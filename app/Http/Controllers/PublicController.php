<?php

/**
 * Class PublicController
 *
 * @category Worketic
 *
 * @package Worketic
 * @author  Amentotech <theamentotech@gmail.com>
 * @license http://www.amentotech.com Amentotech
 * @link    http://www.amentotech.com
 */

namespace App\Http\Controllers;

use App;
use App\Http\Requests\SearchJobsRequest;
use App\Message;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\User;
use App\Language;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Hash;
use Auth;
use DB;
use App\Helper;
use App\Profile;
use App\Category;
use App\Location;
use App\Skill;
use Session;
use Sage;
use App\Job;
use App\EmailTemplate;
use App\Mail\GeneralEmailMailable;
use App\Mail\AdminEmailMailable;
use App\SiteManagement;
use App\Review;
use Carbon\Carbon;
use App\Payout;
use App\Service;
use App\DeliveryTime;
use App\ResponseTime;
use App\Mail\PublicEmailMailable;

/**
 * Class PublicController
 *
 */
class PublicController extends Controller
{

    /**
     * User Login Function
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @access public
     *
     * @return \Illuminate\Http\Response
     */
    public function loginUser(Request $request)
    {
        $json = array();
        if (Session::has('user_id')) {
            $id = Session::get('user_id');
            $user = User::find($id);
            Auth::login($user);
            $json['type'] = 'success';
            $json['role'] = $user->getRoleNames()->first();
            session()->forget('user_id');
            return $json;
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.something_wrong');
            return $json;
        }
    }

    /**
     * Step1 Registeration Validation
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @access public
     *
     * @return \Illuminate\Http\Response
     */
    public function registerStep1Validation(Request $request)
    {
        $this->validate(
            $request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
            ]
        );
    }

    /**
     * Step2 Registeration Validation
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @access public
     *
     * @return \Illuminate\Http\Response
     */
    public function registerStep2Validation(Request $request)
    {
		$validate_rules = [
			'freelancer' => [
                /* 'pin' => 'required',
                'pin_date_revalid' => 'required',
                'prof_ind_cert' => 'required',
                'passport_visa' => 'required', */
				/*
                '' => 'required',
                '' => 'required',
                '' => 'required',
                '' => 'required',
                '' => 'required',
				'' => 'required',
				 */
                'termsconditions' => 'required',
			],
			'support' => [
				/*
                '' => 'required',
                '' => 'required',
                '' => 'required',
                '' => 'required',
                '' => 'required',
                '' => 'required',
                '' => 'required',
				'' => 'required',
				 */
                'termsconditions' => 'required',
			],
			'employer' => [
        //         'emp_website' => 'required',
        //         'straddress' => 'required',
        //         'city' => 'required',
        //         'postcode' => 'required',
        //         'emp_contact' => 'required',
        //         'emp_telno' => 'required',
        //         'emp_email' => 'required|email',
        //         'emp_cqc_rating_date' => 'required',
        //         'emp_cqc_rating' => 'required',
        //         'org_type' => 'required',
				/* 'practice_code' => [
				// 	'required',
				// 	'regex:/(^([a-zA-Z]{1})([\d]+)?$)/u'
            function ($attribute, $value, $fail) {
                $value = trim($value);

                if ($value !== '') {
                    $response = @file_get_contents('https://directory.spineservices.nhs.uk/ORD/2-0-0/organisations/' . urlencode($value) . '?_format=json');

                    if ($response == null) {
                        $fail('Company not found.');
                    }
                }
            }
				], */
                'termsconditions' => 'required',
			],
		];
        $this->validate(
            $request,
			$validate_rules[$request['role']]
		);
    }

    /**
     * Set slug before saving in DB
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @access public
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyUserCode(Request $request)
    {
        $json = array();
        if (Session::has('user_id')) {
            $id = Session::get('user_id');
            $email = Session::get('email');
            $password = Session::get('password');
            $user = User::find($id);
            if (!empty($request['code'])) {
                if ($request['code'] === $user->verification_code) {
                    $user->user_verified = 1;
                    $user->verification_code = null;
                    $user->save();
                    $json['type'] = 'success';
                    //send mail
                    if (!empty(config('mail.username')) && !empty(config('mail.password'))) {
                        $email_params = array();
                        $template = DB::table('email_types')->select('id')->where('email_type', 'new_user')->get()->first();
                        if (!empty($template->id)) {
                            $template_data = EmailTemplate::getEmailTemplateByID($template->id);
                            $email_params['name'] = Helper::getUserName($id);
                            $email_params['email'] = $email;
                            $email_params['password'] = $password;

                            $templateMail = new GeneralEmailMailable(
                                'new_user',
                                $template_data,
                                $email_params
                            );

                            Mail::to($email)
                                ->send(
                                    $templateMail
                                );

                            $messageBody = $templateMail->prepareEmailNewRegisteredUser($email_params);
                            $notificationMessage = ['receiver_id' => $id,'author_id' => 1,'message' => $messageBody];
                            $service = new Message();
                            $service->saveNofiticationMessage($notificationMessage);

                        }
                        $admin_template = DB::table('email_types')->select('id')->where('email_type', 'admin_email_registration')->get()->first();
                        if (!empty($template->id)) {
                            $template_data = EmailTemplate::getEmailTemplateByID($admin_template->id);
                            $email_params['name'] = Helper::getUserName($id);
                            $email_params['email'] = $email;
                            $email_params['link'] = url('profile/' . $user->slug);
                            Mail::to(config('mail.username'))
                                ->send(
                                    new AdminEmailMailable(
                                        'admin_email_registration',
                                        $template_data,
                                        $email_params
                                    )
                                );
                        }
                    }
                    session()->forget('password');
                    session()->forget('email');
                    return $json;
                } else {
                    $json['type'] = 'error';
                    $json['message'] = trans('lang.invalid_verify_code');
                    return $json;
                }
            } else {
                $json['type'] = 'error';
                $json['message'] = trans('lang.verify_code');
                return $json;
            }
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.session_expire');
            return $json;
        }
    }

    /**
     * Download file.
     *
     * @param type    $type     file type
     * @param string  $filename file typname
     * @param integer $id       id
     *
     * @access public
     *
     * @return \Illuminate\Http\Response
     */
    function getFile($type, $filename, $id)
    {
        if (!empty($type) && !empty($filename) && !empty($id)) {
            if (Storage::disk('local')->exists('uploads/' . $type . '/' . $id . '/' . $filename)) {
                return Storage::download('uploads/' . $type . '/' . $id . '/' . $filename);
            } else {
                Session::flash('error', trans('lang.file_not_found'));
                return Redirect::back();
            }
        } else {
            abort(404);
        }
    }

    /**
     * Show user profile.
     *
     * @param string $slug slug
     *
     * @return \Illuminate\Http\Response
     */
    public function showUserProfile($slug)
    {
        $back_url = route('goToDashboard');
        $urlPrevious = url()->previous();
        $backRouteName = app('router')->getRoutes($urlPrevious)->match(app('request')->create($urlPrevious))->getName();
        if (in_array($backRouteName, ['searchResults', 'employerDashboard', 'freelancerDashboard', 'supportDashboard'])) {
            $back_url = $urlPrevious;
        }

        $user = User::select('id')->where('slug', $slug)->first();

        if (!empty($user)) {
            $user = User::leftJoin('professions', 'users.profession_id', '=', 'professions.id')
                        ->select('users.*', 'professions.*', 'users.id as id')
                        ->find($user->id);
            if ($user->is_disabled == 'true') {
                abort(404);
            }
            $skills = $user->skills()->get();
            $job = Job::where('user_id', $user->id)->get();
            $profile = Profile::all()->where('user_id', $user->id)->first();
            $reasons = Helper::getReportReasons();
            $avatar = !empty($profile->avater) ? '/uploads/users/' . $profile->user_id . '/' . $profile->avater : '/images/user.jpg';
            $banner = !empty($profile->banner) ? '/uploads/users/' . $profile->user_id . '/' . $profile->banner : Helper::getUserProfileBanner($user->id);
            $auth_user = Auth::user() ? true : false;
            $user_name = Helper::getUserName($profile->user_id);
            $user_first_name = Helper::getUserFirstName($profile->user_id);
            $user_last_name = Helper::getUserLastName($profile->user_id);
            $current_date = Carbon::now()->format('M d, Y');
            $tagline = !empty($profile) ? html_entity_decode($profile->tagline, ENT_QUOTES) : '';
            $desc = !empty($profile) ? $profile->description : '';
            if ($user->getRoleNames()->first() === 'freelancer') {
                $services = array();
                if (Schema::hasTable('services') && Schema::hasTable('service_user')) {
                    $services = $user->services;
                }
                $reviews = Review::where('receiver_id', $user->id)->get();
                $awards = !empty($profile->awards) ? unserialize($profile->awards) : array();
                $projects = !empty($profile->projects) ? unserialize($profile->projects) : array();
                $experiences = !empty($profile->experience) ? unserialize($profile->experience) : array();
                $education = !empty($profile->education) ? unserialize($profile->education) : array();
                $freelancer_rating  = !empty($user->profile->ratings) ? Helper::getUnserializeData($user->profile->ratings) : 0;
                $rating = !empty($freelancer_rating) ? $freelancer_rating[0] : 0;
                $stars  =  !empty($freelancer_rating) ? $freelancer_rating[0] / 5 * 100 : 0;
                $joining_date = !empty($profile->created_at) ? Carbon::parse($profile->created_at)->format('M d, Y') : '';
                $jobs = Job::select('title', 'id')->get()->pluck('title', 'id');
                $save_freelancer = !empty(auth()->user()->profile->saved_freelancer) ? unserialize(auth()->user()->profile->saved_freelancer) : array();
                $badge = Helper::getUserBadge($user->id);
                $feature_class = !empty($badge) ? 'wt-featured' : '';
                $badge_color = !empty($badge) ? $badge->color : '';
                $badge_img  = !empty($badge) ? $badge->image : '';
                $amount = Payout::where('user_id', $user->id)->select('amount')->pluck('amount')->first();
                $employer_projects = Auth::user() ? Helper::getEmployerJobs(Auth::user()->id) : array();
                $currency_symbol  = !empty($payment_settings) && !empty($payment_settings[0]['currency']) ? Helper::currencyList($payment_settings[0]['currency']) : array();
                $symbol = !empty($currency_symbol['symbol']) ? $currency_symbol['symbol'] : '??';
                $settings = !empty(SiteManagement::getMetaValue('settings')) ? SiteManagement::getMetaValue('settings') : array();
                $display_chat = !empty($settings[0]['chat_display']) ? $settings[0]['chat_display'] : false;
                $payment_settings = SiteManagement::getMetaValue('commision');
                $enable_package = !empty($payment_settings) && !empty($payment_settings[0]['enable_packages']) ? $payment_settings[0]['enable_packages'] : 'true';

                $hiredFreelancers = Job::join('proposals', 'jobs.id', '=', 'proposals.job_id')
                    ->where('jobs.user_id', '=', Auth::user()->id)
                    ->where('proposals.freelancer_id', '=', $user->id)
                    ->where('proposals.hired', '=', 1)
                    ->get();
                $doc_visible = $hiredFreelancers->count();
                if (file_exists(resource_path('views/extend/front-end/users/freelancer-show.blade.php'))) {
                    return View(
                        'extend.front-end.users.freelancer-show',
                        compact(
                            'services',
                            'profile',
                            'amount',
                            'skills',
                            'user',
                            'job',
                            'reasons',
                            'reviews',
                            'avatar',
                            'banner',
                            'user_name',
                            'user_first_name',
                            'user_last_name',
                            'jobs',
                            'rating',
                            'education',
                            'experiences',
                            'projects',
                            'awards',
                            'joining_date',
                            'save_freelancer',
                            'auth_user',
                            'badge',
                            'feature_class',
                            'badge_color',
                            'badge_img',
                            'employer_projects',
                            'currency_symbol',
                            'current_date',
                            'symbol',
                            'tagline',
                            'desc',
                            'display_chat',
                            'enable_package',
                            'doc_visible'
                        )
                    );
                } else {
                    return View(
                        'front-end.users.public-profile.freelancer-show', //'front-end.users.freelancer-show',
                        compact(
                            'services',
                            'profile',
                            'amount',
                            'skills',
                            'user',
                            'job',
                            'reasons',
                            'reviews',
                            'avatar',
                            'banner',
                            'user_name',
                            'user_first_name',
                            'user_last_name',
                            'jobs',
                            'rating',
                            'stars',
                            'education',
                            'experiences',
                            'projects',
                            'awards',
                            'joining_date',
                            'save_freelancer',
                            'auth_user',
                            'badge',
                            'feature_class',
                            'badge_color',
                            'badge_img',
                            'employer_projects',
                            'currency_symbol',
                            'current_date',
                            'symbol',
                            'tagline',
                            'desc',
                            'display_chat',
                            'enable_package',
                            'back_url',
                            'doc_visible'
                        )
                    );
                }
			} else if ($user->getRoleNames()->first() === 'support') {
                $services = array();
                if (Schema::hasTable('services') && Schema::hasTable('service_user')) {
                    $services = $user->services;
                }
                $reviews = Review::where('receiver_id', $user->id)->get();
                $awards = !empty($profile->awards) ? unserialize($profile->awards) : array();
                $projects = !empty($profile->projects) ? unserialize($profile->projects) : array();
                $experiences = !empty($profile->experience) ? unserialize($profile->experience) : array();
                $education = !empty($profile->education) ? unserialize($profile->education) : array();
                $freelancer_rating  = !empty($user->profile->ratings) ? Helper::getUnserializeData($user->profile->ratings) : 0;
                $rating = !empty($freelancer_rating) ? $freelancer_rating[0] : 0;
                $stars  =  !empty($freelancer_rating) ? $freelancer_rating[0] / 5 * 100 : 0;
                $joining_date = !empty($profile->created_at) ? Carbon::parse($profile->created_at)->format('M d, Y') : '';
                $jobs = Job::select('title', 'id')->get()->pluck('title', 'id');
                $save_freelancer = !empty(auth()->user()->profile->saved_freelancer) ? unserialize(auth()->user()->profile->saved_freelancer) : array();
                $badge = Helper::getUserBadge($user->id);
                $feature_class = !empty($badge) ? 'wt-featured' : '';
                $badge_color = !empty($badge) ? $badge->color : '';
                $badge_img  = !empty($badge) ? $badge->image : '';
                $amount = Payout::where('user_id', $user->id)->select('amount')->pluck('amount')->first();
                $employer_projects = Auth::user() ? Helper::getEmployerJobs(Auth::user()->id) : array();
                $currency_symbol  = !empty($payment_settings) && !empty($payment_settings[0]['currency']) ? Helper::currencyList($payment_settings[0]['currency']) : array();
                $symbol = !empty($currency_symbol['symbol']) ? $currency_symbol['symbol'] : '??';
                $settings = !empty(SiteManagement::getMetaValue('settings')) ? SiteManagement::getMetaValue('settings') : array();
                $display_chat = !empty($settings[0]['chat_display']) ? $settings[0]['chat_display'] : false;
                $payment_settings = SiteManagement::getMetaValue('commision');
                $enable_package = !empty($payment_settings) && !empty($payment_settings[0]['enable_packages']) ? $payment_settings[0]['enable_packages'] : 'true';
                $hiredFreelancers = Job::join('proposals', 'jobs.id', '=', 'proposals.job_id')
                    ->where('jobs.user_id', '=', Auth::user()->id)
                    ->where('proposals.freelancer_id', '=', $user->id)
                    ->where('proposals.hired', '=', 1)
                    ->get();
                $doc_visible = $hiredFreelancers->count();
                if (file_exists(resource_path('views/extend/front-end/users/support-show.blade.php'))) {
                    return View(
                        'extend.front-end.users.support-show',
                        compact(
                            'services',
                            'profile',
                            'amount',
                            'skills',
                            'user',
                            'job',
                            'reasons',
                            'reviews',
                            'avatar',
                            'banner',
                            'user_name',
                            'user_first_name',
                            'user_last_name',
                            'jobs',
                            'rating',
                            'education',
                            'experiences',
                            'projects',
                            'awards',
                            'joining_date',
                            'save_freelancer',
                            'auth_user',
                            'badge',
                            'feature_class',
                            'badge_color',
                            'badge_img',
                            'employer_projects',
                            'currency_symbol',
                            'current_date',
                            'symbol',
                            'tagline',
                            'desc',
                            'display_chat',
                            'enable_package',
                            'doc_visible'
                        )
                    );
                } else {
                    return View(
                        'front-end.users.public-profile.support-show',//'front-end.users.support-show',
                        compact(
                            'services',
                            'profile',
                            'amount',
                            'skills',
                            'user',
                            'job',
                            'reasons',
                            'reviews',
                            'avatar',
                            'banner',
                            'user_name',
                            'user_first_name',
                            'user_last_name',
                            'jobs',
                            'rating',
                            'stars',
                            'education',
                            'experiences',
                            'projects',
                            'awards',
                            'joining_date',
                            'save_freelancer',
                            'auth_user',
                            'badge',
                            'feature_class',
                            'badge_color',
                            'badge_img',
                            'employer_projects',
                            'currency_symbol',
                            'current_date',
                            'symbol',
                            'tagline',
                            'desc',
                            'display_chat',
                            'enable_package',
                            'back_url',
                            'doc_visible'
                        )
                    );
                }
            } elseif ($user->getRoleNames()->first() === 'employer') {
                $jobs = Job::where('user_id', $profile->user_id)->latest()->paginate(5);
                $followers = DB::table('followers')->where('following', $profile->user_id)->get();
                $save_employer = !empty(auth()->user()->profile->saved_employers) ? unserialize(auth()->user()->profile->saved_employers) : array();
                $save_jobs = !empty(auth()->user()->profile->saved_jobs) ? unserialize(auth()->user()->profile->saved_jobs) : array();
                $currency = SiteManagement::getMetaValue('commision');
                $symbol   = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
                $breadcrumbs_settings = SiteManagement::getMetaValue('show_breadcrumb');
                $show_breadcrumbs = !empty($breadcrumbs_settings) ? $breadcrumbs_settings : 'true';
                $user = $profile->user;
                $skills = Skill::all();
                if (file_exists(resource_path('views/extend/front-end/users/employer-show.blade.php'))) {
                    return View(
                        'extend.front-end.users.employer-show',
                        compact(
                            'profile',
                            'skills',
                            'user',
                            'job',
                            'reasons',
                            'avatar',
                            'banner',
                            'user_name',
                            'user_first_name',
                            'user_last_name',
                            'jobs',
                            'followers',
                            'save_employer',
                            'save_jobs',
                            'auth_user',
                            'current_date',
                            'symbol',
                            'tagline',
                            'desc',
                            'show_breadcrumbs',
                            'user'
                        )
                    );
                } else {
                    return View(
                        'front-end.users.public-profile.employer-show', //'front-end.users.employer-show',
                        compact(
                            'profile',
                            'skills',
                            'user',
                            'job',
                            'reasons',
                            'avatar',
                            'banner',
                            'user_name',
                            'user_first_name',
                            'user_last_name',
                            'jobs',
                            'followers',
                            'save_employer',
                            'save_jobs',
                            'auth_user',
                            'current_date',
                            'symbol',
                            'tagline',
                            'desc',
                            'show_breadcrumbs',
                            'user',
                            'back_url'
                        )
                    );
                }
            }
        } else {
            abort(404);
        }
    }

    /**
     * Get filtered list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFilterlist()
    {
        $json = array();
        $filters = Helper::getSearchFilterList();
        if (!empty($filters)) {
            $json['type'] = 'success';
            $json['result'] = $filters;
            return $json;
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.something_wrong');
            return $json;
        }
    }

    /**
     * Get searchable data.
     *
     * @param mixed $request request->attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function getSearchableData(Request $request)
    {
        $json = array();
        if (!empty($request['type'])) {
            $searchables = Helper::getSearchableList($request['type']);
            if (!empty($searchables)) {
                $json['type'] = 'success';
                $json['searchables'] = $searchables;
                return $json;
            } else {
                $json['type'] = 'error';
                $json['message'] = trans('lang.something_wrong');
                return $json;
            }
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.something_wrong');
            return $json;
        }
    }


    /**
     * Get search results
     *
     * @param SearchJobsRequest $request
     * @param string $search_type
     * @return mixed
     */
    public function getSearchResult(SearchJobsRequest $request, $search_type = "")
    {
        $user = auth()->user();
        $categories = Category::all();
        $locations = Location::all();
        $languages = Language::all();
        $skills = Skill::all();
        $freelancer_skills = Helper::getFreelancerLevelList();
        $project_length = Helper::getJobDurationList();
        $keyword = !empty($_GET['s']) ? $_GET['s'] : '';
        $type = !empty($_GET['type']) ? $_GET['type'] : $search_type;
        $has_access = true;

        switch ($type) {
            case 'freelancer':
                if (!$user->hasRole('employer')){
                    $has_access = false;
                }
                break;
            case 'job':
                if (!$user->hasRole(['freelancer', 'support'])){
                    $has_access = false;
                }
                break;
            default:
                $has_access = false;
        }

        if (!$has_access) {
          App::abort(403, 'Access Denied');
        }

        $search_categories = !empty($_GET['category']) ? $_GET['category'] : array();
        $search_locations = !empty($_GET['locations']) ? $_GET['locations'] : array();
        $search_skills = !empty($_GET['skills']) ? $_GET['skills'] : array();
        $search_project_lengths = !empty($_GET['project_lengths']) ? $_GET['project_lengths'] : array();
        $search_languages = !empty($_GET['languages']) ? $_GET['languages'] : array();
        $search_employees = !empty($_GET['employees']) ? $_GET['employees'] : array();
        $search_hourly_rates = !empty($_GET['hourly_rate']) ? $_GET['hourly_rate'] : array();
        $search_freelaner_types = !empty($_GET['freelaner_type']) ? $_GET['freelaner_type'] : array();
        $search_english_levels = !empty($_GET['english_level']) ? $_GET['english_level'] : array();
        $search_delivery_time = !empty($_GET['delivery_time']) ? $_GET['delivery_time'] : array();
        $search_response_time = !empty($_GET['response_time']) ? $_GET['response_time'] : array();
        $current_date = Carbon::now()->toDateTimeString();
        $currency = SiteManagement::getMetaValue('commision');
        $symbol   = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
        $inner_page  = SiteManagement::getMetaValue('inner_page_data');
        $payment_settings = SiteManagement::getMetaValue('commision');
        $enable_package = !empty($payment_settings) && !empty($payment_settings[0]['enable_packages']) ? $payment_settings[0]['enable_packages'] : 'true';
        $breadcrumbs_settings = SiteManagement::getMetaValue('show_breadcrumb');
        $show_breadcrumbs = !empty($breadcrumbs_settings) ? $breadcrumbs_settings : 'true';
        $days_avail = !empty($_GET['days_avail']) ? $_GET['days_avail'] : array();
        $hours_avail = !empty($_GET['hours_avail']) ? $_GET['hours_avail'] : array();
        $avail_date_from = null;
        $avail_date_to = null;

        $location = $request->input('location');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radius = $request->input('radius');
        $profession_id = $request->input('profession_id');
        $start_date = $request->input('avail_date_from') ?? $request->input('start_date') ?? null;
        $end_date = $request->input('avail_date_to') ?? $request->input('end_date') ?? null;
        $time = [
            'hours' => $request->input('hours'),
            'minutes' => $request->input('minutes')
        ];
        $rate = $request->input('rate');

        $only_date = true;
        
        if($request->avail_date_from && $request->hours) {
            $only_date = false;
            $avail_date_from = $request->avail_date_from . ' ' . $request->hours . ':' . $request->minutes . ':00';
            $avail_date_from = Carbon::createFromFormat('d/m/Y H:i:s', $avail_date_from)->format('Y-m-d H:i:s');
        } else if($request->avail_date_from) {
            $avail_date_from = $request->avail_date_from;
            $avail_date_from = Carbon::createFromFormat('d/m/Y', $avail_date_from)->format('Y-m-d');
        }
        if($request->avail_date_to && $request->hours) {
            $only_date = false;
            $avail_date_to = $request->avail_date_to . ' ' . $request->hours . ':' . $request->minutes . ':00';
            $avail_date_to = Carbon::createFromFormat('d/m/Y H:i:s', $avail_date_to)->format('Y-m-d H:i:s');
        } else if($request->avail_date_to) {
            $avail_date_to = $request->avail_date_to;
            $avail_date_to = Carbon::createFromFormat('d/m/Y', $avail_date_to)->format('Y-m-d');
        }

        if (!empty($_GET['type'])) {
            if ($type == 'employer' || $type == 'freelancer' || $type == 'avail_date' || $type == 'location' || $type == 'skill') {
                $users_total_records = User::count();
                $search =  User::getSearchResult(
                    $user,
                    $type,
                    $keyword,
                    $search_locations,
                    $search_employees,
                    $search_skills,
                    $search_hourly_rates,
                    $search_freelaner_types,
                    $search_english_levels,
                    $search_languages,
                    $days_avail,
                    $hours_avail,
                    $avail_date_from,
                    $avail_date_to,
                    $location,
                    $latitude,
                    $longitude,
                    $radius,
                    $profession_id,
                    $only_date,
                    $rate
                );
                if(!($location || $profession_id || $avail_date_from || $avail_date_to ))
                    $users = [];
                else $users = count($search['users']) > 0 ? $search['users'] : [];
                /*
                $users = User::select('users.*')
                                ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                ->where('model_has_roles.model_type', '=', 'App\User')
                                ->where('roles.role_type', '=', "freelancer")
                                ->get();
                */
                $save_freelancer = !empty(auth()->user()->profile->saved_freelancer) ?
                    unserialize(auth()->user()->profile->saved_freelancer) : array();
                $save_employer = !empty(auth()->user()->profile->saved_employers) ?
                    unserialize(auth()->user()->profile->saved_employers) : array();
                if ($type === 'employer') {
                    $emp_list_meta_title = !empty($inner_page) && !empty($inner_page[0]['emp_list_meta_title']) ? $inner_page[0]['emp_list_meta_title'] : trans('lang.emp_listing');
                    $emp_list_meta_desc = !empty($inner_page) && !empty($inner_page[0]['emp_list_meta_desc']) ? $inner_page[0]['emp_list_meta_desc'] : trans('lang.emp_meta_desc');
                    $show_emp_banner = !empty($inner_page) && !empty($inner_page[0]['show_emp_banner']) ? $inner_page[0]['show_emp_banner'] : 'true';
                    $e_inner_banner = !empty($inner_page) && !empty($inner_page[0]['e_inner_banner']) ? $inner_page[0]['e_inner_banner'] : null;
                    if (file_exists(resource_path('views/extend/front-end/employers/index.blade.php'))) {
                        return view(
                            'extend.front-end.employers.index',
                            compact(
                                'users',
                                'locations',
                                'languages',
                                'freelancer_skills',
                                'project_length',
                                'keyword',
                                'type',
                                'users_total_records',
                                'save_employer',
                                'current_date',
                                'emp_list_meta_title',
                                'emp_list_meta_desc',
                                'show_emp_banner',
                                'e_inner_banner',
                                'enable_package',
                                'show_breadcrumbs'
                            )
                        );
                    } else {
                        return view(
                            'front-end.employers.index',
                            compact(
                                'users',
                                'locations',
                                'languages',
                                'freelancer_skills',
                                'project_length',
                                'keyword',
                                'type',
                                'users_total_records',
                                'save_employer',
                                'current_date',
                                'emp_list_meta_title',
                                'emp_list_meta_desc',
                                'show_emp_banner',
                                'e_inner_banner',
                                'enable_package',
                                'show_breadcrumbs'
                            )
                        );
                    }
                } elseif ($type === 'freelancer') {
                    $f_list_meta_title = !empty($inner_page) && !empty($inner_page[0]['f_list_meta_title']) ? $inner_page[0]['f_list_meta_title'] : trans('lang.freelancer_listing');
                    $f_list_meta_desc = !empty($inner_page) && !empty($inner_page[0]['f_list_meta_desc']) ? $inner_page[0]['f_list_meta_desc'] : trans('lang.freelancer_meta_desc');
                    $show_f_banner = !empty($inner_page) && !empty($inner_page[0]['show_f_banner']) ? $inner_page[0]['show_f_banner'] : 'true';
                    $f_inner_banner = !empty($inner_page) && !empty($inner_page[0]['f_inner_banner']) ? $inner_page[0]['f_inner_banner'] : null;
                    if (file_exists(resource_path('views/extend/front-end/freelancers/index.blade.php'))) {
                        return view(
                            'extend.front-end.freelancers.index',
                            compact(
                                'type',
                                'users',
                                'categories',
                                'locations',
                                'languages',
                                'skills',
                                'project_length',
                                'keyword',
                                'users_total_records',
                                'save_freelancer',
                                'symbol',
                                'current_date',
                                'f_list_meta_title',
                                'f_list_meta_desc',
                                'show_f_banner',
                                'f_inner_banner',
                                'enable_package',
                                'show_breadcrumbs',
                                'location',
                                'latitude',
                                'longitude',
                                'radius',
                                'profession_id',
                                'avail_date_from',
                                'avail_date_to',
                                'time',
                                'rate'
                            )
                        );
                    } else {
                        return view(
                            'front-end.freelancers.index',
                            compact(
                                'type',
                                'users',
                                'categories',
                                'locations',
                                'languages',
                                'skills',
                                'project_length',
                                'keyword',
                                'users_total_records',
                                'save_freelancer',
                                'symbol',
                                'current_date',
                                'f_list_meta_title',
                                'f_list_meta_desc',
                                'show_f_banner',
                                'f_inner_banner',
                                'enable_package',
                                'show_breadcrumbs',
                                'location',
                                'latitude',
                                'longitude',
                                'radius',
                                'profession_id',
                                'avail_date_from',
                                'avail_date_to',
                                'time',
                                'rate'
                            )
                        );
                    }
                } else {
                    abort(404);
                }
            } elseif ($type == 'service') {
                $service_list_meta_title = !empty($inner_page) && !empty($inner_page[0]['service_list_meta_title']) ? $inner_page[0]['service_list_meta_title'] : trans('lang.service_listing');
                $service_list_meta_desc = !empty($inner_page) && !empty($inner_page[0]['service_list_meta_desc']) ? $inner_page[0]['service_list_meta_desc'] : trans('lang.service_meta_desc');
                $show_service_banner = !empty($inner_page) && !empty($inner_page[0]['show_service_banner']) ? $inner_page[0]['show_service_banner'] : 'true';
                $service_inner_banner = !empty($inner_page) && !empty($inner_page[0]['service_inner_banner']) ? $inner_page[0]['service_inner_banner'] : null;
                // $services= Service::all();
                $delivery_time = DeliveryTime::all();
                $response_time = ResponseTime::all();
                $services_total_records = Service::count();
                $results = Service::getSearchResult(
                    $keyword,
                    $search_categories,
                    $search_locations,
                    $search_languages,
                    $search_delivery_time,
                    $search_response_time
                );
                $services = $results['services'];
                if (file_exists(resource_path('views/extend/front-end/services/index.blade.php'))) {
                    return view(
                        'extend.front-end.services.index',
                        compact(
                            'services_total_records',
                            'type',
                            'services',
                            'symbol',
                            'keyword',
                            'categories',
                            'locations',
                            'languages',
                            'delivery_time',
                            'response_time',
                            'service_list_meta_title',
                            'service_list_meta_desc',
                            'show_service_banner',
                            'service_inner_banner',
                            'show_breadcrumbs'
                        )
                    );
                } else {
                    return view(
                        'front-end.services.index',
                        compact(
                            'services_total_records',
                            'type',
                            'services',
                            'symbol',
                            'keyword',
                            'categories',
                            'locations',
                            'languages',
                            'delivery_time',
                            'response_time',
                            'service_list_meta_title',
                            'service_list_meta_desc',
                            'show_service_banner',
                            'service_inner_banner',
                            'show_breadcrumbs'
                        )
                    );
                }
            } else {
                $Jobs_total_records = Job::count();
                $job_list_meta_title = !empty($inner_page) && !empty($inner_page[0]['job_list_meta_title']) ? $inner_page[0]['job_list_meta_title'] : trans('lang.job_listing');
                $job_list_meta_desc = !empty($inner_page) && !empty($inner_page[0]['job_list_meta_desc']) ? $inner_page[0]['job_list_meta_desc'] : trans('lang.job_meta_desc');
                $show_job_banner = !empty($inner_page) && !empty($inner_page[0]['show_job_banner']) ? $inner_page[0]['show_job_banner'] : 'true';
                $job_inner_banner = !empty($inner_page) && !empty($inner_page[0]['job_inner_banner']) ? $inner_page[0]['job_inner_banner'] : null;
                $job_date = '';

                if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    $job_date = $_GET['start_date'];
                }
                
                $results = Job::getSearchResult($request);

                if(!($request->location || $request->profession_id || $request->avail_date_from || $request->avail_date_to))
                    $jobs = [];
                else 
                    $jobs = $results['jobs'];

                //$jobs = Job::get();

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
                            'Jobs_total_records',
                            'keyword',
                            'skills',
                            'type',
                            'current_date',
                            'symbol',
                            'job_list_meta_title',
                            'job_list_meta_desc',
                            'show_job_banner',
                            'job_inner_banner',
                            'show_breadcrumbs',
                            'location',
                            'latitude',
                            'longitude',
                            'radius',
                            'profession_id',
                            'time',
                            'rate',
                            'start_date',
                            'end_date'
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
                            'Jobs_total_records',
                            'keyword',
                            'skills',
                            'type',
                            'current_date',
                            'symbol',
                            'job_list_meta_title',
                            'job_list_meta_desc',
                            'show_job_banner',
                            'job_inner_banner',
                            'show_breadcrumbs',
                            'location',
                            'latitude',
                            'longitude',
                            'radius',
                            'profession_id',
                            'time',
                            'rate',
                            'start_date',
                            'end_date'
                        )
                    );
                }
            }
        } else {
            abort(404);
        }
    }

    /**
     * Get Pass Reset Form
     *
     * @param mixed $verification_code verification_code
     *
     * @access public
     *
     * @return View
     */
    public function resetPasswordView($verification_code)
    {
        if (!empty($verification_code)) {
            session()->put(['verification_code' => $verification_code]);
            if (file_exists(resource_path('views/extend/front-end/reset-password.blade.php'))) {
                return View('extend.front-end.reset-password');
            } else {
                return View('front-end.reset-password');
            }
        } else {
            abort(404);
        }
    }

    /**
     * Reset user password.
     *
     * @param mixed $request req->attr
     *
     * @access public
     *
     * @return View
     */
    public function resetUserPassword(Request $request)
    {
        if (Session::has('verification_code')) {
            $verification_code = Session::get('verification_code');
            if (!empty($request)) {
                $this->validate(
                    $request,
                    [
                        'new_password' => 'required',
                        'confirm_password' => 'required',
                    ]
                );
                $user_id = User::select('verification_code', 'id')
                    ->where('verification_code', $verification_code)
                    ->pluck('id')->first();
                $user = User::find($user_id);
                if ($request->new_password === $request->confirm_password) {
                    if ($verification_code === $user->verification_code) {
                        $user->password = Hash::make($request->confirm_password);
                        $user->verification_code = null;
                        $user->save();
                        Auth::logout();
                        session()->forget('verification_code');
                        return Redirect::to('/');
                    } else {
                        Session::flash('error', trans('lang.invalid_verify_code'));
                        return Redirect::back();
                    }
                } else {
                    Session::flash('error', trans('lang.pass_mismatched'));
                    return Redirect::back();
                }
            } else {
                Session::flash('error', trans('lang.something_wrong'));
                return Redirect::back();
            }
        } else {
            Session::flash('error', trans('lang.invalid_verify_code'));
            return Redirect::back();
        }
    }

    /**
     * Check user authorization.
     *
     * @access public
     *
     * @return View
     */
    public function checkProposalAuth(Request $request)
    {
        $json = array();
        $job = Job::where('slug', $request->job)->first();

        if (!Auth::user()) {
            $json['auth'] = false;
            $json['message'] = trans('lang.not_authorize');
            return $json;
        }

        if (!ProposalController::checkDistance($job)) {
            $json['auth'] = false;
            $json['message'] = trans('lang.distance_error');
            return $json;
        }

        $json['auth'] = true;
        return $json;
    }

    /**
     * Check user authorization.
     *
     * @access public
     *
     * @return View
     */
    public function checkServiceAuth()
    {
        $json = array();
        if (Auth::user() && Auth::user()->getRoleNames()->first() === 'employer') {
            $json['auth'] = true;
            return $json;
        } else {
            $json['auth'] = false;
            $json['message'] = trans('lang.not_authorize');
            return $json;
        }
    }

    /**
     * Check user authorization.
     *
     * @access public
     *
     * @return unserialize array
     */
    public function getFreelancerExperience(Request $request)
    {
        $json = array();
        $id = $request['id'];
        $freelancer = User::find($id);
        if (!empty($freelancer)) {
            $json['type'] = 'success';
            $json['experience'] = unserialize($freelancer->profile->experience);
            return $json;
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    /**
     * Check user authorization.
     *
     * @access public
     *
     * @return View
     */
    public function getFreelancerEducation(Request $request)
    {
        $json = array();
        $id = $request['id'];
        $freelancer = User::find($id);
        if (!empty($freelancer)) {
            $json['type'] = 'success';
            $json['education'] = unserialize($freelancer->profile->education);
            return $json;
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    /**
     * Check user authorization.
     *
     * @access public
     *
     * @return View
     */
    public function getFreelancerService(Request $request)
    {
        $json = array();
        $id = $request['id'];
        $freelancer = User::find($id);
        if (!empty($freelancer)) {
            $json['type'] = 'success';
            $json['user'] = $freelancer;
            $json['services'] = Helper::getUnserializeData($freelancer->services);
            return $json;
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    public function RegisterCheckoutComplete($stripe_token)
    {
        $user = User::where('stripe_token',  $stripe_token)->first();
        if (!empty($user)) {
            $user->stripe_token = "";
            $user->save();
            Auth::login($user);
            $role_id = Helper::getRoleByUserID(Auth::user()->id);

            if($role_id==2)
            {
                $role = 'employer';
            }
            else{
                $role = 'freelancer';
            }
            Session::flash('message', "Payment completed successfully");
            return Redirect::to('/'.$role.'/dashboard');
        }


    }

    public function goToDashboard(){
        if(Auth::user())
        {
            $role_id = Helper::getRoleByUserID(Auth::user()->id);

            if($role_id==2)
            {
                $role = 'employer';
            } elseif ($role_id == 3) {
                $role = 'freelancer';
            } else {
                $role = 'support';
            }
            return Redirect::to('/'.$role.'/dashboard');
        }
        else{
            return Redirect::to('/');

        }
    }

    public function contactUs(Request $request){
        if (!empty($_POST)) {
            $this->validate($request, [
                'name' => 'required|string',
                'subject' => 'required|string',
                'email' => 'required|email',
                'message' => 'required|string'
            ]);

            Mail::to('westwardforster@gmail.com')->send(
                new PublicEmailMailable('contact_us', [], [
                    'name' => $request->input('name'),
                    'subject' => $request->input('subject'),
                    'email' => $request->input('email'),
                    'message' => $request->input('message')
                ])
            );

            return redirect('contact-us')->with('success', ['Thanks for contacting us! We will be in touch with you shortly.']);
        }

        return view('front-end.contact-us');
    }
}
