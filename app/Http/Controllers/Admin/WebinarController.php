<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WebinarController extends Controller
{
    public function index(Request $request) {
        $webinars = Webinar::withCount('registrations')
            ->when($request->search, fn($q,$s)=>$q->where(function ($query) use ($s) {
                $query->where('title','like',"%{$s}%")
                    ->orWhere('subject','like',"%{$s}%")
                    ->orWhere('speaker','like',"%{$s}%")
                    ->orWhere('description','like',"%{$s}%");
            }))
            ->when($request->status, fn($q,$s)=>$q->where('status',$s))
            ->when($request->type === 'free', fn($q)=>$q->where('is_free', true))
            ->when($request->type === 'paid', fn($q)=>$q->where('is_free', false))
            ->when($request->subject, fn($q,$s)=>$q->where('subject',$s))
            ->when($request->academic_year, fn($q,$s)=>$q->where('academic_year',$s))
            ->when($request->date_from, fn($q,$d)=>$q->whereDate('starts_at','>=',$d))
            ->when($request->date_to, fn($q,$d)=>$q->whereDate('starts_at','<=',$d))
            ->latest('starts_at')->paginate(20)->withQueryString();
        $subjects = Webinar::whereNotNull('subject')->where('subject','!=','')->distinct()->orderBy('subject')->pluck('subject');
        $academicYears = Webinar::whereNotNull('academic_year')->where('academic_year','!=','')->distinct()->orderBy('academic_year')->pluck('academic_year');
        return view('admin.webinars.index', compact('webinars','subjects','academicYears'));
    }
    public function create() { return view('admin.webinars.create', ['webinar'=>new Webinar]); }
    public function store(Request $request) { $webinar=$this->save(new Webinar,$request); return redirect()->route('admin.webinars.index')->with('success','Webinar created.'); }
    public function show(Webinar $webinar) { $registrations=$webinar->registrations()->with('user')->latest()->paginate(25); return view('admin.webinars.show',compact('webinar','registrations')); }
    public function edit(Webinar $webinar) { return view('admin.webinars.edit',compact('webinar')); }
    public function update(Request $request, Webinar $webinar) {
        $this->save($webinar,$request);
        $changed = $webinar->wasChanged(['starts_at', 'ends_at', 'meeting_link', 'status']);
        if ($changed) {
            $message = $webinar->status === 'cancelled'
                ? 'This webinar has been cancelled.'
                : 'The webinar schedule or meeting details have been updated.';
            $webinar->registrations()->with('user')->get()->each(function ($registration) use ($message, $webinar): void {
                if ($registration->user) {
                    $registration->user->notify(new \App\Notifications\WebinarUpdated($webinar, $message));
                }
            });
        }
        return redirect()->route('admin.webinars.show',$webinar)->with('success','Webinar updated.');
    }
    public function destroy(Webinar $webinar) { $webinar->delete(); return back()->with('success','Webinar deleted.'); }
    public function updateRegistration(Request $request, WebinarRegistration $registration) {
        $data=$request->validate([
            'registration_status'=>[ 'required',Rule::in([WebinarRegistration::STATUS_CONFIRMED,WebinarRegistration::STATUS_CANCELLED,WebinarRegistration::STATUS_PENDING])],
            'attendance_status'=>['nullable',Rule::in(['attended','absent'])],
        ]);
        $old=$registration->registration_status; $registration->update($data);
        if ($old !== $registration->registration_status && $registration->user) $registration->user->notify(new \App\Notifications\WebinarRegistrationUpdated($registration));
        return back()->with('success','Registration updated.');
    }
    private function save(Webinar $webinar, Request $request): Webinar {
        $data=$request->validate([
            'title'=>'required|string|max:255','subject'=>'nullable|string|max:255',
            'academic_year'=>'nullable|string|max:100','semester'=>'nullable|string|max:100',
            'description'=>'nullable|string','learning_objectives'=>'nullable|string',
            'speaker'=>'nullable|string|max:255','starts_at'=>'required|date',
            'ends_at'=>'required|date|after:starts_at','duration_minutes'=>'required|integer|min:1',
            'timezone'=>'required|string|max:64','meeting_platform'=>'nullable|string|max:100',
            'meeting_link'=>'nullable|url|max:2048','capacity'=>'nullable|integer|min:1',
            'registration_deadline'=>'nullable|date|before:starts_at','price'=>'required|numeric|min:0',
            'currency'=>'required|string|size:3','status'=>['required',Rule::in(['draft','published','registration_closed','completed','cancelled'])],
        ]);
        if ($request->status === 'published' && blank($request->meeting_link)) {
            abort(422, 'A published webinar must have a meeting link.');
        }
        $data['is_free']=(float)$data['price']<=0; $data['published_at']=$data['status']==='published' ? ($webinar->published_at ?: now()) : null;
        if (!$webinar->exists) $data['slug']=Str::slug($data['title']).'-'.Str::lower(Str::random(5));
        if ($webinar->exists) {
            $webinar->update($data);
            return $webinar;
        }
        return Webinar::create($data);
    }
}
