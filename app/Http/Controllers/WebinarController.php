<?php
namespace App\Http\Controllers;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class WebinarController extends Controller
{
    public function __construct(private readonly RazorpayService $razorpay) {}

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
        abort_unless($webinar->registrationIsOpen(), 404);
        if ($request->user()->webinarRegistrations()->where('webinar_id',$webinar->id)->exists()) return redirect()->route('webinars.my')->with('webinar_error','You are already registered.');
        $paid = $webinar->isPaid();
        if ($paid && !$this->razorpay->isConfigured()) return back()->with('webinar_error','Online payment is not configured yet.');
        $registration = DB::transaction(fn() => WebinarRegistration::create([
            'webinar_id'=>$webinar->id, 'user_id'=>$request->user()->id,
            'student_name'=>$request->user()->name, 'student_email'=>$request->user()->email,
            'registration_reference'=>'WR-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
            'amount'=>$paid ? $webinar->price : 0, 'currency'=>$webinar->currency,
            'is_free'=>!$paid, 'payment_status'=>$paid ? WebinarRegistration::PAYMENT_PENDING : WebinarRegistration::PAYMENT_NOT_REQUIRED,
            'registration_status'=>$paid ? WebinarRegistration::STATUS_PENDING : WebinarRegistration::STATUS_CONFIRMED,
        ]));
        if (!$paid) { $request->user()->notify(new \App\Notifications\WebinarRegistrationUpdated($registration)); return redirect()->route('webinars.my')->with('success','You are registered for this webinar.'); }
        try {
            $order = $this->razorpay->createWebinarOrder($registration);
            $registration->update(['razorpay_order_id'=>$order['id']]);
        } catch (Throwable $e) { $registration->update(['payment_status'=>WebinarRegistration::PAYMENT_FAILED,'payment_error'=>$e->getMessage()]); return back()->with('webinar_error','Unable to start payment. Please try again.'); }
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
            $registration->update(['razorpay_payment_id'=>$data['razorpay_payment_id'],'razorpay_signature'=>$data['razorpay_signature'],'payment_status'=>WebinarRegistration::PAYMENT_PAID,'registration_status'=>WebinarRegistration::STATUS_CONFIRMED,'paid_at'=>now(),'payment_error'=>null]);
            $request->user()->notify(new \App\Notifications\WebinarRegistrationUpdated($registration));
            return redirect()->route('webinars.my')->with('success','Payment successful. Your registration is confirmed.');
        } catch (Throwable $e) {
            $registration->update(['payment_status'=>WebinarRegistration::PAYMENT_FAILED,'payment_error'=>'Payment verification failed.']);
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
