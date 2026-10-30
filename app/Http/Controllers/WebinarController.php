<?php
namespace App\Http\Controllers;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\RazorpayService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class WebinarController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly CouponService $coupons
    ) {}

    public function index() { $webinars = Webinar::registrationOpen()->orderBy('starts_at')->paginate(12); return view('webinars.index', compact('webinars')); }
    public function show(Webinar $webinar) {
        abort_unless($webinar->status === 'published', 404);
        $registration = auth()->user()?->webinarRegistrations()->where('webinar_id',$webinar->id)->first();
        return view('webinars.show', compact('webinar','registration'));
    }
    public function myWebinars(Request $request) {
        $registrations = $request->user()->webinarRegistrations()->with('webinar')->latest()->paginate(12);
        return view('webinars.my', compact('registrations'));
    }
    public function register(Request $request, Webinar $webinar) {
        $data = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);
        abort_unless($webinar->registrationIsOpen(), 404);
        if ($request->user()->webinarRegistrations()->where('webinar_id',$webinar->id)->exists()) return redirect()->route('webinars.my')->with('webinar_error','You are already registered.');
        $paid = $webinar->isPaid();
        $couponCode = trim((string) ($data['coupon_code'] ?? ''));
        if (! $paid && $couponCode !== '') {
            return back()->withInput()->with('webinar_error', 'Coupons are available only for paid webinars.');
        }
        $baseAmount = $paid ? (float) $webinar->price : 0.0;
        $preview = $paid && $couponCode !== ''
            ? $this->coupons->quote($couponCode, (int) $request->user()->id, 'webinar', $baseAmount)
            : [
                'coupon' => null,
                'original_amount' => round($baseAmount, 2),
                'discount_amount' => 0,
                'final_amount' => round($baseAmount, 2),
            ];
        if ($preview['final_amount'] > 0 && !$this->razorpay->isConfigured()) return back()->with('webinar_error','Online payment is not configured yet.');
        $registration = DB::transaction(function () use ($request, $webinar, $paid, $couponCode, $baseAmount): WebinarRegistration {
            $quote = $paid && $couponCode !== ''
                ? $this->coupons->quote($couponCode, (int) $request->user()->id, 'webinar', $baseAmount, true)
                : [
                    'coupon' => null,
                    'original_amount' => round($baseAmount, 2),
                    'discount_amount' => 0,
                    'final_amount' => round($baseAmount, 2),
                ];
            $zeroPayable = $paid && $quote['final_amount'] <= 0;
            $registration = WebinarRegistration::create([
                'webinar_id'=>$webinar->id, 'user_id'=>$request->user()->id,
                'student_name'=>$request->user()->name, 'student_email'=>$request->user()->email,
                'registration_reference'=>'WR-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'amount'=>$quote['final_amount'], 'original_amount'=>$quote['original_amount'],
                'discount_amount'=>$quote['discount_amount'], 'coupon_id'=>$quote['coupon']?->id,
                'coupon_code'=>$quote['coupon']?->code, 'currency'=>$webinar->currency,
                'is_free'=>!$paid,
                'payment_status'=>(!$paid || $zeroPayable) ? WebinarRegistration::PAYMENT_NOT_REQUIRED : WebinarRegistration::PAYMENT_PENDING,
                'registration_status'=>(!$paid || $zeroPayable) ? WebinarRegistration::STATUS_CONFIRMED : WebinarRegistration::STATUS_PENDING,
            ]);
            if ($quote['coupon']) {
                $this->coupons->reserve($quote, (int) $request->user()->id, $registration);
            }
            return $registration;
        });
        if (!$paid || (float) $registration->amount <= 0) {
            $request->user()->notify(new \App\Notifications\WebinarRegistrationUpdated($registration));
            return redirect()->route('webinars.my')->with('success', $paid ? 'Your coupon covered the full webinar amount. Registration confirmed.' : 'You are registered for this webinar.');
        }
        try {
            $order = $this->razorpay->createWebinarOrder($registration);
            $registration->update(['razorpay_order_id'=>$order['id']]);
        } catch (Throwable $e) { $registration->update(['payment_status'=>WebinarRegistration::PAYMENT_FAILED,'payment_error'=>'Unable to create payment order.']); $this->coupons->release($registration); return back()->with('webinar_error','Unable to start payment. Please try again.'); }
        return view('webinars.payment', compact('registration','webinar','order'));
    }
    public function verify(Request $request, WebinarRegistration $registration) {
        abort_unless($registration->user_id === $request->user()->id, 403);
        if ($registration->payment_status === WebinarRegistration::PAYMENT_PAID) {
            return redirect()->route('webinars.my')->with('success', 'This payment was already verified.');
        }
        $data = $request->validate(['razorpay_payment_id'=>'required|string','razorpay_signature'=>'required|string']);
        try {
            $this->razorpay->verifyWebinarSignature($registration,$data['razorpay_payment_id'],$data['razorpay_signature']);
            $payment = $this->razorpay->fetchPayment($data['razorpay_payment_id']);
            $this->razorpay->assertPaymentMatchesWebinar($registration,$payment);
            abort_unless($payment['status'] === 'captured', 422);
            DB::transaction(function () use ($registration, $data): void {
                $locked = WebinarRegistration::query()->lockForUpdate()->findOrFail($registration->id);
                if ($locked->payment_status === WebinarRegistration::PAYMENT_PAID) {
                    return;
                }
                $locked->update(['razorpay_payment_id'=>$data['razorpay_payment_id'],'razorpay_signature'=>$data['razorpay_signature'],'payment_status'=>WebinarRegistration::PAYMENT_PAID,'registration_status'=>WebinarRegistration::STATUS_CONFIRMED,'paid_at'=>now(),'payment_error'=>null]);
                $this->coupons->redeem($locked);
            });
            $registration->refresh();
            $request->user()->notify(new \App\Notifications\WebinarRegistrationUpdated($registration));
            return redirect()->route('webinars.my')->with('success','Payment successful. Your registration is confirmed.');
        } catch (Throwable $e) {
            $registration->update(['payment_error'=>'Payment verification failed.']);
            return back()->with('webinar_error','Payment verification failed. Please contact support.');
        }
    }
    public function join(Request $request, WebinarRegistration $registration) {
        abort_unless($registration->user_id === $request->user()->id, 403);
        abort_unless($registration->registration_status === WebinarRegistration::STATUS_CONFIRMED && (!$registration->isPaid() || $registration->payment_status === WebinarRegistration::PAYMENT_PAID), 403);
        abort_unless(filled($registration->webinar->meeting_link), 404);
        $registration->update(['joined_at'=>now()]);
        return redirect()->away($registration->webinar->meeting_link);
    }
}
