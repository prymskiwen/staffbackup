<?php


namespace App\Http\Controllers;

use App;
use App\CalendarEvent;
use App\Http\Requests\CreateJobRequest;
use App\Repositories\ProfessionRepository;
use App\Job;
use App\Message;
use App\Role;
use Illuminate\Http\Request;
use Auth;
use Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use App\Language;
use App\Category;
use App\Skill;
use App\Profession;
use App\Location;
use App\Helper;
use App\Proposal;
use ValidateRequests;
use App\User;
use App\Profile;
use App\Package;
use DB;
//use Spatie\Permission\Models\Role;
use App\SiteManagement;
use App\Mail\AdminEmailMailable;
use App\Mail\EmployerEmailMailable;
use App\Mail\SupportEmailMailable;
use App\Mail\FreelancerEmailMailable;
use App\EmailTemplate;
use App\Item;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Class JobController
 *
 */
class JobController extends Controller
{
    /**
     * Defining scope of the variable
     *
     * @access protected
     * @var    array $job
     */
    protected $job;
    protected $professionRepository;

    /**
     * Defining scope of the variable
     *
     * @access protected
     * @var    array $job
     */
    // public $email_settings;

    /**
     * Create a new controller instance.
     *
     * @param instance $job instance
     *
     * @return void
     */
    public function __construct(Job $job, ProfessionRepository $professionRepository)
    {
        $this->job = $job;
        $this->professionRepository = $professionRepository;
    }

    /**
     * Post Job Form.
     *
     * @return post jobs page
     */
    public function postJob()
    {
        $languages = Language::pluck('title', 'id');
        $locations = Location::pluck('title', 'id');
        $user = User::find(Auth::user()->id);
        $profile = $user->profile;
        $english_levels = Helper::getEnglishLevelList();
        $project_levels = Helper::getProjectLevel();
        $max_distances = array(
            '1 Mile'=>'1 Mile',
            '2 Mile'=>'2 Mile',
            '3 Mile'=>'3 Mile',
            '4 Mile'=>'4 Mile',
            '5 Mile'=>'5 Mile',
            '6 Mile'=>'6 Mile',
            '7 Mile'=>'7 Mile',
            '8 Mile'=>'8 Mile',
            '9 Mile'=>'9 Mile',
            '10 Mile'=>'10 Mile',
        );
       // $job_duration = Helper::getJobDurationList();
        $freelancer_level = Helper::getFreelancerLevelList();
        $skills = Skill::pluck('title', 'id');
        $professions = $this->professionRepository->getProfessionsByRole()->pluck('title', 'id');
        $categories = Category::pluck('title', 'id');
        $role_id =  Helper::getRoleByUserID(Auth::user()->id);
        $package_options = Package::select('options')->where('role_id', $role_id)->first();
        $options = !empty($package_options) ? unserialize($package_options['options']) : array();
        if (file_exists(resource_path('views/extend/back-end/employer/jobs/create.blade.php'))) {
            return view(
                'extend.back-end.employer.jobs.create',
                compact(
                    'english_levels',
                    'languages',
                    'project_levels',
                    'max_distances',
                    //'job_duration',
                    'freelancer_level',
                    'skills',
                    'professions',
                    'user',
                    'profile',
                    'categories',
                    'locations',
                    'options'
                )
            );
        } else {
            return view(
                'back-end.employer.jobs.create',
                compact(
                    'english_levels',
                    'languages',
                    'user',
                    'profile',
                    'project_levels',
                    'max_distances',
                    //'job_duration',
                    'freelancer_level',
                    'skills',
                    'professions',
                    'categories',
                    'locations',
                    'options'
                )
            );
        }
    }

    /**
     * Manage Jobs.
     *
     * @return manage jobs page
     */
    public function index()
    {
        $job_details = $this->job->latest()->where('user_id', Auth::user()->id)->paginate(5);
        $currency   = SiteManagement::getMetaValue('commision');
        $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
        if (file_exists(resource_path('views/extend/back-end/employer/jobs/index.blade.php'))) {
            return view('extend.back-end.employer.jobs.index', compact('job_details', 'symbol'));
        } else {
            return view('back-end.employer.jobs.index', compact('job_details', 'symbol'));
        }
    }

    public function close($job_slug)
    {
        if (!empty($job_slug)) {
            $job = Job::where('slug', $job_slug)->first();
            $job->is_active = false;
            $job->save();
        }
        return redirect('employer/dashboard/manage-jobs');

    }

    public function reactivate($job_slug)
    {
        if (!empty($job_slug)) {
            $job = Job::where('slug', $job_slug)->first();
            $job->is_active = true;
            $job->save();
        }
        return redirect('employer/dashboard/manage-jobs');

    }

    /**
     * Job Edit Form.
     *
     * @param integer $job_slug Job Slug
     *
     * @return show job edit page
     */
    public function edit($job_slug)
    {
        if (!empty($job_slug)) {
            $job = Job::leftJoin('job_profession', 'job_profession.job_id', '=', 'jobs.id')
                        ->where('slug', $job_slug)
                        ->select('jobs.*', 'job_profession.*', 'jobs.id as id')
                        ->first();
            $json = array();
            $languages = Language::pluck('title', 'id');
            $locations = Location::pluck('title', 'id');
            $skills = Skill::pluck('title', 'id');
            $professions =  $this->professionRepository->getProfessionsByRole()->pluck('title', 'id');

            $categories = Category::pluck('title', 'id');
            $project_levels = Helper::getProjectLevel();
            $english_levels = Helper::getEnglishLevelList();
            //$job_duration = Helper::getJobDurationList();
            $freelancer_level_list = Helper::getFreelancerLevelList();

            $jobEvents = CalendarEvent::where('job_id',$job->id)->get()->toArray();
            $attachments = !empty($job->attachments) ? unserialize($job->attachments) : '';
            $firstJob = CalendarEvent::where('job_id',$job->id)->first();
            $firstJobStart = ($firstJob)?[Carbon::parse($firstJob->start)->format('d-m-Y'),Carbon::parse($firstJob->start)->format('H:i')]:false;
            $firstJobEnd = ($firstJob)?[Carbon::parse($firstJob->end)->format('d-m-Y'),Carbon::parse($firstJob->end)->format('H:i')]:false;
            if (!empty($job)) {
                if (file_exists(resource_path('views/extend/back-end/employer/jobs/edit.blade.php'))) {
                    return View(
                        'extend.back-end.employer.jobs.edit',
                        compact(
                            'job',
                            'project_levels',
                            'english_levels',
                            //'job_duration',
                            'freelancer_level_list',
                            'languages',
                            'categories',
                            'skills',
                            'professions',
                            'locations',
                            'attachments',
                            'jobEvents',
                            'firstJob',
                            'firstJobStart',
                            'firstJobEnd'
                        )
                    );
                } else {
                    return View(
                        'back-end.employer.jobs.edit',
                        compact(
                            'job',
                            'project_levels',
                            'english_levels',
                            //'job_duration',
                            'freelancer_level_list',
                            'languages',
                            'categories',
                            'skills',
                            'professions',
                            'locations',
                            'attachments',
                            'jobEvents',
                            'firstJob',
                            'firstJobStart',
                            'firstJobEnd'
                        )
                    );
                }
            }
        }
    }

    /**
     * Get job attachment settings.
     *
     * @param integer $request $request->attributes
     *
     * @return show job single page
     */
    public function getAttachmentSettings(Request $request)
    {
        $json = array();
        if ($request['slug']) {
            $settings = Job::where('slug', $request['slug'])
                ->select('is_featured', 'show_attachments')->first();
            if (!empty($settings)) {
                $json['type'] = 'success';
                if ($settings->is_featured == 'true') {
                    $json['is_featured'] = 'true';
                }
                if ($settings->show_attachments == 'true') {
                    $json['show_attachments'] = 'true';
                }
            } else {
                $json['type'] = 'error';
            }
            return $json;
        }
    }

    /**
     * Upload image to temporary folder.
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadTempImage(Request $request)
    {
        if (!empty($request['file'])) {
            $attachments = $request['file'];
            $path = 'uploads/jobs/temp/';
            return $this->job->uploadTempattachments($attachments, $path);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateJobRequest $request)
    {
        $json = [];
        $server = Helper::worketicIsDemoSiteAjax();

        if (!empty($server)) {
            $response['message'] = $server->getData()->message;
            return $response;
        }

        if (Helper::getAccessType() == 'services') {
            $json['type'] = 'job_warning';
            return $json;
        }

        $package_item = Item::where('subscriber', Auth::user()->id)->first();

        $package = !empty($package_item) ? Package::find($package_item->product_id) : '';
        $option = !empty($package) ? unserialize($package->options) : '';
        $posted_featured_jobs = Job::where('user_id', Auth::user()->id)->where('is_featured', 'true')->count();
        $payment_settings = \App\SiteManagement::getMetaValue('commision');

        if (empty($payment_settings)) {
            $package_status = 'true';
        } else {
            $package_status = !empty($payment_settings[0]['employer_package']) ? $payment_settings[0]['employer_package'] : 'true';
        }

        if ($package_status === 'true') {
            if ($request['is_featured'] == 'true') {
                if ($posted_featured_jobs >= intval($option['featured_jobs'])) {
                    $json['type'] = 'error';
                    $json['message'] = trans('lang.sorry_can_only_feature')  .' '. $option['featured_jobs'] .' ' . trans('lang.jobs_acc_to_pkg');
                    return $json;
                }
            }

            if($request['project_rates_type']=='Per hour')
            {
                $request['project_type'] = "hourly";
            }
            else{
                $request['project_type'] = "fixed";
            }
            $request['english_level'] = "basic";
            $request['freelancer_type'] = "pro_independent";
            $request['project_levels'] = "basic";
            $job_post = $this->job->storeJobs($request);

            if ($job_post['type'] === 'success') {

                $json['type'] = 'success';
                $json['message'] = trans('lang.job_post_success');
                // Send Email
                $user = User::find(Auth::user()->id);
                //send email to admin
                if (!empty(config('mail.username')) && !empty(config('mail.password'))) {
                    $job = $this->job::where('user_id', Auth::user()->id)->latest()->first();
                    $email_params = array();
                    $new_posted_job_template = DB::table('email_types')->select('id')->where('email_type', 'admin_email_new_job_posted')->get()->first();
                    $new_posted_job_template_employer = DB::table('email_types')->select('id')->where('email_type', 'employer_email_new_job_posted')->get()->first();
                    if (!empty($new_posted_job_template->id) || !empty($new_posted_job_template_employer)) {
                        $template_data = EmailTemplate::getEmailTemplateByID($new_posted_job_template->id);
                        $template_data_employer = EmailTemplate::getEmailTemplateByID($new_posted_job_template_employer->id);
                        $email_params['job_title'] = $job->title;
                        $email_params['posted_job_link'] = url('/job/' . $job->slug);
                        $email_params['name'] = Helper::getUserName(Auth::user()->id);
                        $email_params['link'] = url('profile/' . $user->slug);
                        //TODO
                        Mail::to(config('mail.username'))
                        ->send(
                            new AdminEmailMailable(
                                'admin_email_new_job_posted',
                                $template_data,
                                $email_params
                            )
                        );
                        if (!empty($user->email)) {
                            $templateMailUser = new EmployerEmailMailable(
                                'employer_email_new_job_posted',
                                $template_data_employer,
                                $email_params
                            );
                            Mail::to($user->email)
                            ->send(
                                $templateMailUser
                            );
                            $messageBodyUser = $templateMailUser->prepareEmployerEmailJobPosted($email_params);
                            $notificationMessageUser = ['receiver_id' => $user->id,'author_id' => 1,'message' => $messageBodyUser];
                            $serviceUser = new Message();
                            $serviceUser->saveNofiticationMessage($notificationMessageUser);
                        }

                        
                        $team_members = DB::table('teams')
                                            ->join('team_user', 'teams.id', '=', 'team_user.team_id')
                                            ->join('users', 'users.id', '=', 'team_user.user_id')
                                            ->where('teams.employer_id', $user->id)
                                            ->get();
                        $candidates = array();
                        foreach($team_members as $member){
                            $professions = array();
                            array_push($professions, $member->profession_id);
                            $extra_professions = DB::table('professions')
                                            ->join('profession_user', 'professions.id', '=', 'profession_user.profession_id')
                                            ->where('profession_user.user_id', $member->id)
                                            ->get();
                            if(count($extra_professions)){
                                foreach($extra_professions as $prof)
                                    array_push($professions, $prof->profession_id);
                            }
                            if(in_array($request->profession_id, $professions)){
                                array_push($candidates, $member);
                            }
                        }
                        foreach($candidates as $candidate){
                            $new_posted_job_template_freelancer = DB::table('email_types')->select('id')->where('email_type', 'freelancer_email_new_job_posted')->get()->first();
                            $template_data_freelancer = EmailTemplate::getEmailTemplateByID($new_posted_job_template_freelancer->id);
                            $email_params['job_title'] = $job->title;
                            $email_params['posted_job_link'] = url('/job/' . $job->slug);
                            $email_params['name'] = Helper::getUserName(Auth::user()->id);
                            $email_params['link'] = url('profile/' . $user->slug);

                            $templateMailUser = new FreelancerEmailMailable(
                                'freelancer_email_new_job_posted',
                                $template_data_freelancer,
                                $email_params
                            );
                            Mail::to($candidate->email)
                            ->send(
                                $templateMailUser
                            );
                            $messageBodyUser = $templateMailUser->prepareFreelancerEmailNewJobPosted($email_params);
                            $notificationMessageUser = ['receiver_id' => $candidate->id, 'author_id' => $user->id, 'message' => $messageBodyUser];
                            $serviceUser = new Message();
                            $serviceUser->saveNofiticationMessage($notificationMessageUser);
                        }

                    }

                    if (!empty($job->latitude) && !empty($job->longitude) && !empty($job->radius)) {
                        $template_data = [];
                        $email_params = [];

                        // notify professonals
                        $professonals = User::findByLocation($job->latitude, $job->longitude, $job->radius, 'freelancer');

                        if ($professonals->count()) {
                            foreach ($professonals as $professonal){
                                $templateMailUser = new FreelancerEmailMailable(
                                    'freelancer_email_new_job_posted',
                                    $template_data,
                                    $email_params
                                );
                                Mail::to($professonal->email)
                                ->send(
                                    $templateMailUser
                                );
                                $messageBodyUser = $templateMailUser->prepareFreelancerEmailNewJobPosted($email_params);
                                $notificationMessageUser = ['receiver_id' => $professonal->id,'author_id' => 1,'message' => $messageBodyUser];
                                $serviceUser = new Message();
                                $serviceUser->saveNofiticationMessage($notificationMessageUser);
                            }
                        }

                        // notify support workers
                        $supports = User::findByLocation($job->latitude, $job->longitude, $job->radius, 'support');

                        if ($supports->count()) {
                            foreach ($supports as $support){
                                $templateMailUser = new SupportEmailMailable(
                                    'support_email_new_job_posted',
                                    $template_data,
                                    $email_params
                                );
                                Mail::to($support->email)
                                ->send(
                                    $templateMailUser
                                );

                                $messageBodyUser = $templateMailUser->prepareSupportEmailNewJobPosted($email_params);
                                $notificationMessageUser = ['receiver_id' => $support->id,'author_id' => 1,'message' => $messageBodyUser];
                                $serviceUser = new Message();
                                $serviceUser->saveNofiticationMessage($notificationMessageUser);

                            }
                        }
                    }

                }
                return $json;
            }
        } else {
            $job_post = $this->job->storeJobs($request);
            if ($job_post = 'success') {
                $json['type'] = 'success';
                $json['message'] = trans('lang.job_post_success');
                // Send Email
                $user = User::find(Auth::user()->id);
                //send email to admin
                if (!empty(config('mail.username')) && !empty(config('mail.password'))) {
                    $job = $this->job::where('user_id', Auth::user()->id)->latest()->first();
                    $email_params = array();
                    $new_posted_job_template = DB::table('email_types')->select('id')->where('email_type', 'admin_email_new_job_posted')->get()->first();
                    $new_posted_job_template_employer = DB::table('email_types')->select('id')->where('email_type', 'employer_email_new_job_posted')->get()->first();
                    if (!empty($new_posted_job_template->id) || !empty(new_posted_job_template_employer)) {
                        $template_data = EmailTemplate::getEmailTemplateByID($new_posted_job_template->id);
                        $template_data_employer = EmailTemplate::getEmailTemplateByID($new_posted_job_template_employer->id);
                        $email_params['job_title'] = $job->title;
                        $email_params['posted_job_link'] = url('/job/' . $job->slug);
                        $email_params['name'] = Helper::getUserName(Auth::user()->id);
                        $email_params['link'] = url('profile/' . $user->slug);
                        Mail::to(config('mail.username'))
                        ->send(
                            new AdminEmailMailable(
                                'admin_email_new_job_posted',
                                $template_data,
                                $email_params
                            )
                        );
                        if (!empty($user->email)) {
                            $templateMailUser = new EmployerEmailMailable(
                                'employer_email_new_job_posted',
                                $template_data_employer,
                                $email_params
                            );
                            Mail::to($user->email)
                            ->send(
                                $templateMailUser
                            );
                            $messageBodyUser = $templateMailUser->prepareEmployerEmailJobPosted($email_params);
                            $notificationMessageUser = ['receiver_id' => $user->id,'author_id' => 1,'message' => $messageBodyUser];
                            $serviceUser = new Message();
                            $serviceUser->saveNofiticationMessage($notificationMessageUser);
                        }
                    }
                }

                return $json;
            }
        }
    }

    /**
     * Updated resource in DB.
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $server = Helper::worketicIsDemoSiteAjax();
        if (!empty($server)) {
            $response['type'] = 'error';
            $response['message'] = $server->getData()->message;
            return $response;
        }
        $json = array();
        $this->validate(
            $request,
            [
                'title' => 'required',
                'project_levels'    => 'required',
                'english_level'    => 'required',
                //'project_cost'    => 'required',
            ]
        );
        $id = $request['id'];
        $job_update = $this->job->updateJobs($request, $id);
        if ($job_update['type'] = 'success') {
            $json['redirect'] = $job_update['redirect'];
            $json['type'] = 'success';
            $json['role'] = Auth::user()->getRoleNames()->first();
            $json['message'] = trans('lang.job_update_success');
            return $json;
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.something_wrong');
            return $json;
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        
        $back_url = route('goToDashboard');
        $urlPrevious = url()->previous();
        $backRouteName = app('router')->getRoutes($urlPrevious)->match(app('request')->create($urlPrevious))->getName();
        if (in_array($backRouteName, ['searchResults', 'employerDashboard', 'freelancerDashboard', 'supportDashboard'])) {
            $back_url = $urlPrevious;
        }
        $job = Job::where('slug', $request->slug)->firstOrFail();

        if ($job) {
            $submitted_proposals = $job->proposals->where('status', '!=', 'cancelled')->pluck('freelancer_id')->toArray();
            $employer_id = $job->employer->id;
            $profile = User::find($employer_id)->profile;
            $user_image = !empty($profile) ? $profile->avater : '';
            $profile_image = !empty($user_image) ? '/uploads/users/' . $job->employer->id . '/' . $user_image : 'images/user-login.png';
            $reasons = Helper::getReportReasons();
            $auth_profile = Auth::user() ? auth()->user()->profile : '';
            $save_jobs = !empty($auth_profile->saved_jobs) ? unserialize($auth_profile->saved_jobs) : array();
            $save_employers = !empty($auth_profile->saved_employers) ? unserialize($auth_profile->saved_employers) : array();
            $attachments  = unserialize($job->attachments);
            $currency   = SiteManagement::getMetaValue('commision');
            $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
            $project_type  = Helper::getProjectTypeList($job->project_type);
            $breadcrumbs_settings = SiteManagement::getMetaValue('show_breadcrumb');
            $show_breadcrumbs = !empty($breadcrumbs_settings) ? $breadcrumbs_settings : 'true';
            $user = $profile->user;

            if (file_exists(resource_path('views/extend/front-end/jobs/show.blade.php'))) {
                return view(
                    'extend.front-end.jobs.show',
                    compact(
                        'job',
                        'reasons',
                        'profile_image',
                        'submitted_proposals',
                        'save_jobs',
                        'save_employers',
                        'attachments',
                        'symbol',
                        'project_type',
                        'show_breadcrumbs',
                        'user',
                        'back_url'
                    )
                );
            } else {
                return view(
                    'front-end.jobs.show',
                    compact(
                        'job',
                        'reasons',
                        'profile_image',
                        'submitted_proposals',
                        'save_jobs',
                        'save_employers',
                        'attachments',
                        'symbol',
                        'project_type',
                        'show_breadcrumbs',
                        'user',
                        'back_url'
                    )
                );
            }
        } else {
            abort(404);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function show1(Request $request)
    {
        $back_url = route('goToDashboard');
        $urlPrevious = url()->previous();
        $backRouteName = app('router')->getRoutes($urlPrevious)->match(app('request')->create($urlPrevious))->getName();
        if (in_array($backRouteName, ['searchResults', 'employerDashboard', 'freelancerDashboard', 'supportDashboard'])) {
            $back_url = $urlPrevious;
        }
        $job = Job::where('slug', $request->slug)->firstOrFail();

        if ($job) {
            $submitted_proposals = $job->proposals->where('status', '!=', 'cancelled')->pluck('freelancer_id')->toArray();
            $employer_id = $job->employer->id;
            $profile = User::find($employer_id)->profile;
            $user_image = !empty($profile) ? $profile->avater : '';
            $profile_image = !empty($user_image) ? '/uploads/users/' . $job->employer->id . '/' . $user_image : 'images/user-login.png';
            $reasons = Helper::getReportReasons();
            $auth_profile = Auth::user() ? auth()->user()->profile : '';
            $save_jobs = !empty($auth_profile->saved_jobs) ? unserialize($auth_profile->saved_jobs) : array();
            $save_employers = !empty($auth_profile->saved_employers) ? unserialize($auth_profile->saved_employers) : array();
            $attachments  = unserialize($job->attachments);
            $currency   = SiteManagement::getMetaValue('commision');
            $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
            $project_type  = Helper::getProjectTypeList($job->project_type);
            $breadcrumbs_settings = SiteManagement::getMetaValue('show_breadcrumb');
            $show_breadcrumbs = !empty($breadcrumbs_settings) ? $breadcrumbs_settings : 'true';
            $user = $profile->user;

            if (file_exists(resource_path('views/extend/front-end/jobs/show1.blade.php'))) {
                return view(
                    'extend.front-end.jobs.show1',
                    compact(
                        'job',
                        'reasons',
                        'profile_image',
                        'submitted_proposals',
                        'save_jobs',
                        'save_employers',
                        'attachments',
                        'symbol',
                        'project_type',
                        'show_breadcrumbs',
                        'user',
                        'back_url'
                    )
                );
            } else {
                return view(
                    'front-end.jobs.show1',
                    compact(
                        'job',
                        'reasons',
                        'profile_image',
                        'submitted_proposals',
                        'save_jobs',
                        'save_employers',
                        'attachments',
                        'symbol',
                        'project_type',
                        'show_breadcrumbs',
                        'user',
                        'back_url'
                    )
                );
            }
        } else {
            abort(404);
        }
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function getJobAppointment(Request $request)
    {
        $job = Job::find($request->id);
        $json['job_appo_slot_times'] = $job->job_appo_slot_times;
        $json['job_adm_catch_time'] = $job->job_adm_catch_time;
        $json['job_adm_catch_time_interval'] = $job->job_adm_catch_time_interval;
        $json['home_visits'] = $job->home_visits;
        $json['breaks'] = unserialize($job->breaks);
        return json_encode($json);
    }

    /**
     * Get job Skills.
     *
     * @param mixed $request $req->attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function getJobSkills(Request $request)
    {
        $json = array();
        if (!empty($request['slug'])) {
            $job = $this->job::where('slug', $request['slug'])->select('id')->first();
            //dd($this->job::where('id', $job['id'])->first());
            if (!empty($job)) {
                $skills = Job::join('job_profession', 'job_profession.job_id', '=', 'jobs.id')
                                ->join('professions', 'professions.id', '=', 'job_profession.profession_id')
                                ->select('professions.id', 'professions.title')
                                ->where('jobs.id', $job['id'])
                                ->get();
                if (!empty($skills)) {
                    $json['type'] = 'success';
                    $json['skills'] = $skills;
                    return $json;
                } else {
                    $json['error'] = 'error';
                    return $json;
                }
            } else {
                $json['error'] = 'error';
                return $json;
            }
        }
    }

    /**
     * Display admin jobs.
     *
     * @return \Illuminate\Http\Response
     */
    public function jobsAdmin()
    {
        if (!empty($_GET['keyword'])) {
            $keyword = $_GET['keyword'];
            $jobs = $this->job::where('title', 'like', '%' . $keyword . '%')->paginate(6)->setPath('');
            $pagination = $jobs->appends(
                array(
                    'keyword' => Input::get('keyword')
                )
            );
        } else {
            $jobs = $this->job->latest()->paginate(6);
        }
        $payment   = SiteManagement::getMetaValue('commision');
        $symbol = !empty($payment) && !empty($payment[0]['currency']) ? Helper::currencyList($payment[0]['currency']) : array();
        $payment_methods = Arr::pluck(Helper::getPaymentMethodList(), 'title', 'value');
        if (file_exists(resource_path('views/extend/back-end/admin/jobs/index.blade.php'))) {
            return view(
                'extend.back-end.admin.jobs.index',
                compact('jobs', 'symbol', 'payment', 'payment_methods')
            );
        } else {
            return view(
                'back-end.admin.jobs.index',
                compact('jobs', 'symbol', 'payment', 'payment_methods')
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listjobs()
    {
        $user = auth()->user();

        if (!$user->hasRole(['freelancer', 'support'])){
            App::abort(403, 'Access Denied');
        }

        $jobs = array();
        $categories = array();
        $locations = array();
        $languages = array();
        $jobs = $this->job->latest()->paginate(6);
        $categories = Category::all();
        $locations = Location::all();
        $languages = Language::all();
        $freelancer_skills = Helper::getFreelancerLevelList();
        $project_length = Helper::getJobDurationList();
        $skills = Skill::all();
        $keyword = '';
        $Jobs_total_records = '';
        $type = 'job';
        $currency = SiteManagement::getMetaValue('commision');
        $symbol   = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
        $job_list_meta_title = !empty($inner_page) && !empty($inner_page[0]['job_list_meta_title']) ? $inner_page[0]['job_list_meta_title'] : trans('lang.job_listing');
        $job_list_meta_desc = !empty($inner_page) && !empty($inner_page[0]['job_list_meta_desc']) ? $inner_page[0]['job_list_meta_desc'] : trans('lang.job_meta_desc');
        $show_job_banner = !empty($inner_page) && !empty($inner_page[0]['show_job_banner']) ? $inner_page[0]['show_job_banner'] : 'true';
        $job_inner_banner = !empty($inner_page) && !empty($inner_page[0]['job_inner_banner']) ? $inner_page[0]['job_inner_banner'] : null;
        if (file_exists(resource_path('views/extend/front-end/jobs/index.blade.php'))) {
            return view(
                'extend.front-end.jobs.index',
                compact(
                    'jobs',
                    'categories',
                    'locations',
                    'languages',
                    'freelancer_skills',
                    'project_length',
                    'keyword',
                    'Jobs_total_records',
                    'type',
                    'skills',
                    'symbol',
                    'job_list_meta_title',
                    'job_list_meta_desc',
                    'show_job_banner',
                    'job_inner_banner'
                )
            );
        } else {
            return view(
                'front-end.jobs.index',
                compact(
                    'jobs',
                    'categories',
                    'locations',
                    'languages',
                    'freelancer_skills',
                    'project_length',
                    'keyword',
                    'Jobs_total_records',
                    'type',
                    'skills',
                    'symbol',
                    'job_list_meta_title',
                    'job_list_meta_desc',
                    'show_job_banner',
                    'job_inner_banner'
                )
            );
        }
    }

    /**
     * Add job to whishlist.
     *
     * @param mixed $request request->attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function addWishlist(Request $request)
    {
        $json = array();
        if (Auth::user()) {
            if (!empty($request['id'])) {
                $user_id = Auth::user()->id;
                $id = $request['id'];
                $profile = new Profile();
                $add_wishlist = $profile->addWishlist($request['column'], $id, $user_id);
                if ($add_wishlist == "success") {
                    $json['type'] = 'success';
                    $json['message'] = trans('lang.added_to_wishlist');
                    return $json;
                } else {
                    $json['type'] = 'error';
                    $json['message'] = trans('lang.something_wrong');
                    return $json;
                }
            }
        } else {
            $json['type'] = 'authentication';
            $json['message'] = trans('lang.need_to_reg');
            return $json;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param mixed $request request attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $server = Helper::worketicIsDemoSiteAjax();
        if (!empty($server)) {
            $json['type'] = 'error';
            $json['message'] = $server->getData()->message;
            return $json;
        }
        $json = array();
        $id = $request['job_id'];
        if (!empty($id)) {
            $this->job->deleteRecord($id);
            $json['type'] = 'success';
            return $json;
        }
    }
}
