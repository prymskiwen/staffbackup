<?php

/**
 * Class FreelancerController.
 *
 * @category Worketic
 *
 * @package Worketic
 * @author  Amentotech <theamentotech@gmail.com>
 * @license http://www.amentotech.com Amentotech
 * @link    http://www.amentotech.com
 */
namespace App\Http\Controllers;

use App\Freelancer;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Repositories\ProfessionRepository;
use Illuminate\Http\Request;
use App\Helper;
use App\Location;
use App\Skill;
use App\Profession;
use App\Role;
use Session;
use App\Profile;
use Auth;
use File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Proposal;
use App\Job;
use DB;
use App\Package;
use App\CalendarEvent;
use App\EmailTemplate;
use App\Mail\EmployerEmailMailable;
use ValidateRequests;
use App\Item;
use Carbon\Carbon;
use App\Message;
use App\Payout;
use App\SiteManagement;
use App\Service;
use App\Review;


/**
 * Class FreelancerController
 *
 */
class FreelancerController extends Controller
{
    /**
     * Defining scope of the variable
     *
     * @access protected
     * @var    array $freelancer
     */
    protected $freelancer;

    protected $professionRepository;

    /**
     * Create a new controller instance.
     *
     * @param Profile $freelancer instance
     * @param Payout $payout
     * @param ProfessionRepository $professionRepository
     */
    public function __construct(Profile $freelancer, Payout $payout, ProfessionRepository $professionRepository)
    {
        $this->freelancer = $freelancer;
        $this->professionRepository = $professionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Location::pluck('title', 'id');
        $skills = Skill::pluck('title', 'id');
        $profile = $this->freelancer::where('user_id', Auth::user()->id)->get()->first();
        $gender = !empty($profile->gender) ? $profile->gender : '';
        $tagline = !empty($profile->tagline) ? html_entity_decode($profile->tagline, ENT_QUOTES) : '';
        $description = !empty($profile->description) ? $profile->description : '';
        $radius = !empty($profile->radius) ? $profile->radius : '';
        $address = !empty($profile->address) ? $profile->address : '';
        $longitude = !empty($profile->longitude) ? $profile->longitude : '';
        $latitude = !empty($profile->latitude) ? $profile->latitude : '';
        $postcode = Auth::user() && !empty(Auth::user()->postcode) ? Auth::user()->postcode : '';
        $banner = !empty($profile->banner) ? $profile->banner : '';
        $avater = !empty($profile->avater) ? $profile->avater : '';
        $cv = !empty($profile->cvFile) ? $profile->cvFile : '';
        $cv_ext = explode('.', $cv);
        $hours_avail = !empty($profile->hours_avail) ? $profile->hours_avail : '';
        $days_avail = !empty($profile->days_avail) ? $profile->days_avail : '';
        $hourly_rate = !empty($profile->hourly_rate) ? $profile->hourly_rate : '';
        $hourly_rate_negotiable = !empty($profile->hourly_rate_negotiable) ? $profile->hourly_rate_negotiable : '';
        $hourly_rate_desc = !empty($profile->hourly_rate_desc) ? $profile->hourly_rate_desc : '';

        $role_id =  Helper::getRoleByUserID(Auth::user()->id);
        $packages = DB::table('items')->where('subscriber', Auth::user()->id)->count();
        $package_options = Package::select('options')->where('role_id', $role_id)->first();
        $options = !empty($package_options) ? unserialize($package_options['options']) : array();
        if (file_exists(resource_path('views/extend/back-end/freelancer/profile-settings/personal-detail/index.blade.php'))) {
            return view(
                'extend.back-end.freelancer.profile-settings.personal-detail.index',
                compact(
                    'locations',
                    'skills',
                    'profile',
                    'gender',
                    'hourly_rate',
                    'tagline',
                    'description',
                    'banner',
                    'radius',
                    'address',
                    'longitude',
                    'latitude',
                    'avater',
                    'options',
                    'cv',
                    'cv_ext',
                    'days_avail',
                    'hours_avail',
                    'hourly_rate_negotiable',
                    'hourly_rate',
                    'hourly_rate_desc',
                    'options'
                )
            );
        } else {
            return view(
                'back-end.freelancer.profile-settings.personal-detail.index',
                compact(
                    'locations',
                    'skills',
                    'profile',
                    'gender',
                    'hourly_rate',
                    'tagline',
                    'description',
                    'banner',
                    'radius',
                    'address',
                    'longitude',
                    'latitude',
                    'avater',
                    'options',
                    'cv',
                    'cv_ext',
                    'days_avail',
                    'hours_avail',
                    'hourly_rate_negotiable',
                    'hourly_rate',
                    'hourly_rate_desc',
                    'postcode'
                )
            );
        }
    }

    /**
     * Upload Image to temporary folder.
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadTempImage(Request $request)
    {
        $path = Helper::PublicPath() . '/uploads/users/temp/';
        if (!empty($request['hidden_avater_image'])) {
            $profile_image = $request['hidden_avater_image'];
            return Helper::uploadTempImage($path, $profile_image);
        } elseif (!empty($request['hidden_banner_image'])) {
            $profile_image = $request['hidden_banner_image'];
            return Helper::uploadTempImage($path, $profile_image);
        } elseif (!empty($request['project_img'])) {
            $profile_image = $request['project_img'];
            return Helper::uploadTempImage($path, $profile_image);
        } elseif (!empty($request['award_img'])) {
            $profile_image = $request['award_img'];
            return Helper::uploadTempImage($path, $profile_image);
        } elseif (!empty($request['hidden_cv_image'])) {
            $cv_image = $request['hidden_cv_image'];
            return Helper::uploadTempCv($path, $cv_image);
        }
    }

    /**
     * Store profile settings.
     *
     * @param \Illuminate\Http\Request $request request attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function storeProfileSettings(Request $request)
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
                'first_name'    => 'required',
                'last_name'    => 'required',
                'pin' => 'required',
                'pin_date_revalid' => 'required',
                // 'prof_ind_cert' => 'required',
                'passport_visa' => 'required',
                'expiry_passport_visa' => 'required',
                'mand_training' => 'required',
                'expiry_mand_training' => 'required',
                'cert_of_crbdbs' => 'required',
                'expiry_cert_of_crbdbs' => 'required',
                'radius' => 'nullable|numeric',
                'latitude'    => 'nullable|numeric',
                'longitude'    => 'nullable|numeric',
            ]
        );
        if (Auth::user()) {
            $role_id = Helper::getRoleByUserID(Auth::user()->id);
            $packages = DB::table('items')->where('subscriber', Auth::user()->id)->count();
            $package_options = Package::select('options')->where('role_id', $role_id)->first();
            $options = !empty($package_options) ? unserialize($package_options['options']) : array();
            $skills = !empty($options) ? $options['no_of_skills'] : array();
            $payment_settings = SiteManagement::getMetaValue('commision');
            $package_status = '';
            if (empty($payment_settings)) {
                $package_status = 'true';
            } else {
                $package_status =!empty($payment_settings[0]['enable_packages']) ? $payment_settings[0]['enable_packages'] : 'true';
            }
            if ($package_status === 'true') {
                if ($packages > 0) {
                    if (!empty($request['skills']) && count($request['skills']) > $skills) {
                        $json['type'] = 'error';
                        $json['message'] = trans('lang.cannot_add_morethan') . '' . $options['no_of_skills'] . ' ' . trans('lang.skills');
                        return $json;
                    } else {
                        $profile =  $this->freelancer->storeProfile($request, Auth::user()->id);
                        if ($profile = 'success') {
                            $json['type'] = 'success';
                            $json['message'] = '';
                            return $json;
                        }
                    }
                } else {
                    $json['type'] = 'error';
                    $json['message'] = trans('lang.update_pkg');
                    return $json;
                }
            } else {
                $profile =  $this->freelancer->storeProfile($request, Auth::user()->id);
                if ($profile = 'success') {
                    $json['type'] = 'success';
                    $json['message'] = '';
                    return $json;
                }
            }
            Session::flash('message', trans('lang.update_profile'));
            return Redirect::back();
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.not_authorize');
            return $json;
        }
    }

    /**
     * Get freelancer skills.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFreelancerSkills()
    {
        $json = array();
        if (Auth::user()) {
            $skills = User::find(Auth::user()->id)->skills()
                ->orderBy('title')->get()->toArray();
            if (!empty($skills)) {
                $json['type'] = 'success';
                $json['freelancer_skills'] = $skills;
                return $json;
            } else {
                $json['type'] = 'error';
                return $json;
            }
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    /**
     * Show the form for creating and updating experiance and education settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function experienceEducationSettings()
    {
        if (file_exists(resource_path('views/extend/back-end/freelancer/profile-settings/experience-education/index.blade.php'))) {
            return view('extend.back-end.freelancer.profile-settings.experience-education.index');
        } else {
            return view('back-end.freelancer.profile-settings.experience-education.index');
        }
    }

    public function bookingAndAvailability()
    {
        $skills = Skill::pluck('title', 'id');

        return view('back-end.freelancer.profile-settings.booking_availability',
            compact(
                'skills'
            ));
    }

    /**
     * Show the form for creating and updating projects & awards.
     *
     * @return \Illuminate\Http\Response
     */
    public function projectAwardsSettings()
    {
        if (file_exists(resource_path('views/extend/back-end/freelancer/profile-settings/projects-awards/index.blade.php'))) {
            return view('extend.back-end.freelancer.profile-settings.projects-awards.index');
        } else {
            return view('back-end.freelancer.profile-settings.projects-awards.index');
        }
    }

    /**
     * Show the form for creating and updating experiance and education settings.
     *
     * @param mixed $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeExperienceEducationSettings(Request $request)
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
                'experience.*.job_title' => 'required',
                'experience.*.start_date' => 'required',
                'experience.*.end_date' => 'required',
                'experience.*.company_title' => 'required',
                'education.*.degree_title' => 'required',
                'education.*.start_date' => 'required',
                'education.*.end_date' => 'required',
                'education.*.institute_title' => 'required',
            ]
        );
        $user_id = Auth::user()->id;
        $update_experience_education = $this->freelancer->updateExperienceEducation($request, $user_id);
        if ($update_experience_education['type'] == 'success') {
            $json['type'] = 'success';
            $json['message'] = trans('lang.saving_profile');
            $json['complete_message'] = trans('lang.profile_update_success');
        } else {
            $json['type'] = 'error';
            $json['message'] = trans('lang.empty_fields_not_allowed');
        }
        return $json;
    }

    /**
     * Show the form with saved values.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFreelancerExperiences()
    {
        $json = array();
        $user_id = Auth::user()->id;
        if (Auth::user()) {
            $profile = $this->freelancer::select('experience')
                ->where('user_id', $user_id)->get()->first();
            if (!empty($profile)) {
                $json['type'] = 'success';
                $json['experiences'] = unserialize($profile->experience);
                return $json;
            } else {
                $json['type'] = 'error';
                return $json;
            }
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    /**
     * Show the form with saved values.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFreelancerEducations()
    {
        $json = array();
        $user_id = Auth::user()->id;
        if (Auth::user()) {
            $profile = $this->freelancer::select('education')
                ->where('user_id', $user_id)->get()->first();
            if (!empty($profile)) {
                $json['type'] = 'success';
                $json['educations'] = unserialize($profile->education);
                return $json;
            } else {
                $json['type'] = 'error';
                return $json;
            }
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }


    /**
     * Show the form for creating and updating projects and awards settings.
     *
     * @param mixed $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeProjectAwardSettings(Request $request)
    {
        $server = Helper::worketicIsDemoSiteAjax();
        if (!empty($server)) {
            $response['type'] = 'error';
            $response['message'] = $server->getData()->message;
            return $response;
        }
        $json = array();
        if (!empty($request)) {
            $this->validate(
                $request,
                [
                    'award.*.award_title' => 'required',
                    'award.*.award_date'    => 'required',
                    'award.*.award_hidden_image'    => 'required',
                    'project.*.project_title' => 'required',
                    'project.*.project_url'    => 'required',
                ]
            );
            $user_id = Auth::user()->id;
            $store_awards_projects = $this->freelancer->updateAwardProjectSettings($request, $user_id);
            if ($store_awards_projects['type'] == 'success') {
                $json['type'] = 'success';
                $json['message'] = trans('lang.saving_profile');
                $json['complete_message'] = 'Profile Updated Successfully';
            } else {
                $json['type'] = 'error';
                $json['message'] = trans('lang.empty_fields_not_allowed');
            }
            return $json;
        }
    }

    /**
     * Get freelancer's projects
     *
     * @return \Illuminate\Http\Response
     */
    public function getFreelancerProjects()
    {
        $user_id = Auth::user()->id;
        $json = array();
        if (Auth::user()) {
            $profile = $this->freelancer::select('projects')
                ->where('user_id', $user_id)->get()->first();
            if (!empty($profile)) {
                $json['type'] = 'success';
                $json['projects'] = unserialize($profile->projects);
                return $json;
            } else {
                $json['type'] = 'error';
                return $json;
            }
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    /**
     * Get freelancer's awards
     *
     * @return \Illuminate\Http\Response
     */
    public function getFreelancerAwards()
    {
        $user_id = Auth::user()->id;
        $json = array();
        if (Auth::user()) {
            $profile = $this->freelancer::select('awards')
                ->where('user_id', $user_id)->get()->first();
            if (!empty($profile)) {
                $json['type'] = 'success';
                $json['awards'] = unserialize($profile->awards);
                return $json;
            } else {
                $json['type'] = 'error';
                return $json;
            }
        } else {
            $json['type'] = 'error';
            return $json;
        }
    }

    /**
     * Show Freelancer Jobs.
     *
     * @param string $status job status
     *
     * @return \Illuminate\Http\Response
     */
    public function showFreelancerJobs($status)
    {
        $ongoing_jobs = array();
        $freelancer_id = Auth::user()->id;
        $currency  = SiteManagement::getMetaValue('commision');
        $symbol    = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
        if (Auth::user()) {
            $ongoing_jobs = Proposal::select('job_id')->latest()->where('freelancer_id', $freelancer_id)->where('status', 'hired')->paginate(7);
            $completed_jobs = Proposal::select('job_id')->latest()->where('freelancer_id', $freelancer_id)->where('status', 'completed')->paginate(7);
            $cancelled_jobs = Proposal::select('job_id')->latest()->where('freelancer_id', $freelancer_id)->where('status', 'cancelled')->paginate(7);
            if (!empty($status) && $status === 'hired') {
                if (file_exists(resource_path('views/extend/back-end/freelancer/jobs/ongoing.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.jobs.ongoing',
                        compact(
                            'ongoing_jobs',
                            'symbol',
                            'status'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.jobs.ongoing',
                        compact(
                            'ongoing_jobs',
                            'symbol',
                            'status'
                        )
                    );
                }
            } elseif (!empty($status) && $status === 'completed') {
                if (file_exists(resource_path('views/extend/back-end/freelancer/jobs/completed.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.jobs.completed',
                        compact(
                            'completed_jobs',
                            'symbol',
                            'status'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.jobs.completed',
                        compact(
                            'completed_jobs',
                            'symbol',
                            'status'
                        )
                    );
                }
            } elseif (!empty($status) && $status === 'cancelled') {
                if (file_exists(resource_path('views/extend/back-end/freelancer/jobs/cancelled.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.jobs.cancelled',
                        compact(
                            'cancelled_jobs',
                            'symbol',
                            'status'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.jobs.cancelled',
                        compact(
                            'cancelled_jobs',
                            'symbol',
                            'status'
                        )
                    );
                }
            }
        }
    }

    /**
     * Show Freelancer Job Details.
     *
     * @param string $slug job slug
     *
     * @return \Illuminate\Http\Response
     */
    public function showOnGoingJobDetail($slug)
    {
        $job = array();
        if (Auth::user()) {
            $job = Job::where('slug', $slug)->first();

            $proposal = Job::find($job->id)->proposals()->select('id', 'status')->where('status', '!=', 'pending')
                ->first();
            if ($proposal->status == 'cancelled') {
                $proposal_job = Job::find($job->id);
                $cancel_reason = $job->reports->first();
            } else {
                $cancel_reason = '';
            }
            $employer_name = Helper::getUserName($job->user_id);
            $duration = !empty($job->duration) ? Helper::getJobDurationList($job->duration) : '';
            $profile = User::find(Auth::user()->id)->profile;
            $employer_profile = User::find($job->user_id)->profile;
            $employer_avatar = !empty($employer_profile) ? $employer_profile->avater : '';
            $user_image = !empty($profile) ? $profile->avater : '';
            $job->skills = (!empty($job->skills))?unserialize($job->skills):"";
            $profile_image = !empty($user_image) ? '/uploads/users/' . Auth::user()->id . '/' . $user_image : 'images/user-login.png';
            $employer_image = !empty($employer_avatar) ? '/uploads/users/' . $job->user_id . '/' . $employer_avatar : 'images/user-login.png';
            $currency   = SiteManagement::getMetaValue('commision');
            $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
            if (file_exists(resource_path('views/extend/back-end/freelancer/jobs/show.blade.php'))) {
                return view(
                    'extend.back-end.freelancer.jobs.show',
                    compact(
                        'job',
                        'employer_name',
                        'duration',
                        'profile_image',
                        'employer_image',
                        'proposal',
                        'symbol',
                        'cancel_reason'
                    )
                );
            } else {
                return view(
                    'back-end.freelancer.jobs.show',
                    compact(
                        'job',
                        'employer_name',
                        'duration',
                        'profile_image',
                        'employer_image',
                        'proposal',
                        'symbol',
                        'cancel_reason'
                    )
                );
            }
        }
    }

    /**
     * Show freelancer proposals.
     *
     * @return \Illuminate\Http\Response
     */
    public function showFreelancerProposals()
    {
        $proposals = Proposal::select('job_id', 'status', 'id')->where('freelancer_id', Auth::user()->id)->latest()->paginate(7);
        $currency  = SiteManagement::getMetaValue('commision');
        $symbol    = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
        if (file_exists(resource_path('views/extend/back-end/freelancer/proposals/index.blade.php'))) {
            return view(
                'extend.back-end.freelancer.proposals.index',
                compact(
                    'proposals',
                    'symbol'
                )
            );
        } else {
            return view(
                'back-end.freelancer.proposals.index',
                compact(
                    'proposals',
                    'symbol'
                )
            );
        }
    }

    /**
     * Show freelancer dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function freelancerDashboard()
    {
        if (Auth::user()) {
            $freelancer_id = Auth::user()->id;
            $ongoing_projects = Proposal::getProposalsByStatus($freelancer_id, 'hired', 3);
            $cancelled_projects = Proposal::getProposalsByStatus($freelancer_id, 'cancelled');
            $package_item = Item::where('subscriber', $freelancer_id)->first();
            $package = !empty($package_item) ? Package::find($package_item->product_id) : array();
            $option = !empty($package) && !empty($package['options']) ? unserialize($package['options']) : '';
            $expiry = !empty($option) ? $package_item->updated_at->addDays($option['duration']) : '';
            $expiry_date = !empty($expiry) ? Carbon::parse($expiry)->toDateTimeString() : '';
            $message_status = Message::where('status', 0)->where('receiver_id', $freelancer_id)->count();
            $notify_class = $message_status > 0 ? 'wt-insightnoticon' : '';
            $completed_projects = Proposal::getProposalsByStatus($freelancer_id, 'completed');
            $currency = SiteManagement::getMetaValue('commision');
            $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
            $trail = !empty($package) && $package['trial'] == 1 ? 'true' : 'false';
            $icons = SiteManagement::getMetaValue('icons');
            $enable_package = !empty($currency) && !empty($currency[0]['enable_packages']) ? $currency[0]['enable_packages'] : 'true';
            $latest_proposals_icon = !empty($icons['hidden_latest_proposal']) ? $icons['hidden_latest_proposal'] : 'img-20.png';
            $latest_package_expiry_icon = !empty($icons['hidden_package_expiry']) ? $icons['hidden_package_expiry'] : 'img-21.png';
            $latest_new_message_icon = !empty($icons['hidden_new_message']) ? $icons['hidden_new_message'] : 'img-19.png';
            $latest_saved_item_icon = !empty($icons['hidden_saved_item']) ? $icons['hidden_saved_item'] : 'img-22.png';
            $latest_cancel_project_icon = !empty($icons['hidden_cancel_project']) ? $icons['hidden_cancel_project'] : 'img-16.png';
            $latest_ongoing_project_icon = !empty($icons['hidden_ongoing_project']) ? $icons['hidden_ongoing_project'] : 'img-17.png';
            $latest_pending_balance_icon = !empty($icons['hidden_pending_balance']) ? $icons['hidden_pending_balance'] : 'icon-01.png';
            $latest_current_balance_icon = !empty($icons['hidden_current_balance']) ? $icons['hidden_current_balance'] : 'icon-02.png';
            $published_services_icon = !empty($icons['hidden_published_services']) ? $icons['hidden_published_services'] : 'payment-method.png';
            $cancelled_services_icon = !empty($icons['hidden_cancelled_services']) ? $icons['hidden_cancelled_services'] : 'decline.png';
            $completed_services_icon = !empty($icons['hidden_completed_services']) ? $icons['hidden_completed_services'] : 'completed-task.png';
            $ongoing_services_icon = !empty($icons['hidden_ongoing_services']) ? $icons['hidden_ongoing_services'] : 'onservice.png';
            $access_type = Helper::getAccessType();
            $applications = Proposal::where('freelancer_id',$freelancer_id)->count();
            $professions = $this->professionRepository->getProfessionsByRole();
            $lastest_proposals = Proposal::getLastWeekProposals($freelancer_id);

            return view(
                'back-end.freelancer.dashboard',
                compact(
                    'access_type',
                    'ongoing_projects',
                    'cancelled_projects',
                    'expiry_date',
                    'notify_class',
                    'completed_projects',
                    'symbol',
                    'trail',
                    'latest_proposals_icon',
                    'latest_package_expiry_icon',
                    'latest_new_message_icon',
                    'latest_saved_item_icon',
                    'latest_cancel_project_icon',
                    'latest_ongoing_project_icon',
                    'latest_pending_balance_icon',
                    'latest_current_balance_icon',
                    'published_services_icon',
                    'cancelled_services_icon',
                    'completed_services_icon',
                    'ongoing_services_icon',
                    'enable_package',
                    'package',
                    'lastest_proposals',
                    'message_status',
                    'applications',
                    'professions'
                )
            );
        }
    }

    /**
     * Show services.
     *
     * @param string $status job status
     *
     * @return \Illuminate\Http\Response
     */
    public function showServices($status)
    {
        $freelancer_id = Auth::user()->id;
        if (Auth::user()) {
            $freelancer = User::find($freelancer_id);
            $currency   = SiteManagement::getMetaValue('commision');
            $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
            $status_list = array_pluck(Helper::getFreelancerServiceStatus(), 'title', 'value');
            if (!empty($status) && $status === 'posted') {
                $services = $freelancer->services;
                if (file_exists(resource_path('views/extend/back-end/freelancer/services/index.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.services.index',
                        compact(
                            'services',
                            'symbol',
                            'status_list'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.services.index',
                        compact(
                            'services',
                            'symbol',
                            'status_list'
                        )
                    );
                }
            } else if (!empty($status) && $status === 'hired') {
                $services = Helper::getFreelancerServices('hired', Auth::user()->id);
                if (file_exists(resource_path('views/extend/back-end/freelancer/services/ongoing.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.services.ongoing',
                        compact(
                            'services',
                            'symbol'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.services.ongoing',
                        compact(
                            'services',
                            'symbol'
                        )
                    );
                }
            } elseif (!empty($status) && $status === 'completed') {
                $services = Helper::getFreelancerServices('completed', Auth::user()->id);
                if (file_exists(resource_path('views/extend/back-end/freelancer/services/completed.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.services.completed',
                        compact(
                            'services',
                            'symbol'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.services.completed',
                        compact(
                            'services',
                            'symbol'
                        )
                    );
                }
            } elseif (!empty($status) && $status === 'cancelled') {
                $services = Helper::getFreelancerServices('cancelled', Auth::user()->id);
                if (file_exists(resource_path('views/extend/back-end/freelancer/services/cancelled.blade.php'))) {
                    return view(
                        'extend.back-end.freelancer.services.cancelled',
                        compact(
                            'services',
                            'symbol'
                        )
                    );
                } else {
                    return view(
                        'back-end.freelancer.services.cancelled',
                        compact(
                            'services',
                            'symbol'
                        )
                    );
                }
            }
        }
    }

    /**
     * Service Details.
     *
     * @param int    $id     id
     * @param string $status status
     *
     * @return \Illuminate\Http\Response
     */
    public function showServiceDetail($id, $status)
    {
        if (Auth::user()) {
            $pivot_service = Helper::getPivotService($id);
            $pivot_id = $pivot_service->id;
            $service = Service::find($pivot_service->service_id);
            $seller = Helper::getServiceSeller($service->id);
            $purchaser = $service->purchaser->first();
            $freelancer = !empty($seller) ? User::find($seller->user_id) : '';
            $service_status = Helper::getProjectStatus();
            $review_options = DB::table('review_options')->get()->all();
            $avg_rating = !empty($freelancer) ? Review::where('receiver_id', $freelancer->id)->sum('avg_rating') : '';
            $freelancer_rating  = !empty($freelancer) && !empty($freelancer->profile->ratings) ? Helper::getUnserializeData($freelancer->profile->ratings) : 0;
            $rating = !empty($freelancer_rating) ? $freelancer_rating[0] : 0;
            $stars  =  !empty($freelancer_rating) ? $freelancer_rating[0] / 5 * 100 : 0;
            $reviews = !empty($freelancer) ? Review::where('receiver_id', $freelancer->id)->where('job_id', $id)->where('project_type', 'service')->get() : '';
            $feedbacks = !empty($freelancer) ? Review::select('feedback')->where('receiver_id', $freelancer->id)->count() : '';
            $cancel_proposal_text = trans('lang.cancel_proposal_text');
            $cancel_proposal_button = trans('lang.send_request');
            $validation_error_text = trans('lang.field_required');
            $cancel_popup_title = trans('lang.reason');
            $attachment = Helper::getUnserializeData($service->attachments);
            $currency   = SiteManagement::getMetaValue('commision');
            $symbol = !empty($currency) && !empty($currency[0]['currency']) ? Helper::currencyList($currency[0]['currency']) : array();
            if (file_exists(resource_path('views/extend/back-end/employer/services/show.blade.php'))) {
                return view(
                    'extend.back-end.employer.services.show',
                    compact(
                        'pivot_service',
                        'id',
                        'service',
                        'freelancer',
                        'service_status',
                        'attachment',
                        'review_options',
                        'stars',
                        'rating',
                        'feedbacks',
                        'cancel_proposal_text',
                        'cancel_proposal_button',
                        'validation_error_text',
                        'cancel_popup_title',
                        'pivot_id',
                        'purchaser',
                        'employer',
                        'symbol'
                    )
                );
            } else {
                return view(
                    'back-end.employer.services.show',
                    compact(
                        'pivot_service',
                        'id',
                        'service',
                        'freelancer',
                        'service_status',
                        'attachment',
                        'review_options',
                        'stars',
                        'rating',
                        'feedbacks',
                        'cancel_proposal_text',
                        'cancel_proposal_button',
                        'validation_error_text',
                        'cancel_popup_title',
                        'pivot_id',
                        'purchaser',
                        'employer',
                        'symbol'
                    )
                );
            }
        } else {
            abort(404);
        }
    }

    /**
     * Get freelancer payouts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPayouts()
    {
        $payouts =  Payout::where('user_id', Auth::user()->id)->paginate(10);
        if (file_exists(resource_path('views/extend/back-end/freelancer/payouts.blade.php'))) {
            return view(
                'extend.back-end.freelancer.payouts.payouts',
                compact('payouts')
            );
        } else {
            return view(
                'back-end.freelancer.payouts.payouts',
                compact('payouts')
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payoutSettings()
    {
        if (Auth::user()) {
            $payrols = Helper::getPayoutsList();
            $user = User::find(Auth::user()->id);
            $payout_settings = $user->profile->count() > 0 ? Helper::getUnserializeData($user->profile->payout_settings) : '';
            if (file_exists(resource_path('views/extend/back-end/freelancer/payouts/payout_settings.blade.php'))) {
                return view(
                    'extend.back-end.freelancer.payouts.payout_settings', compact('payrols', 'payout_settings')
                );
            } else {
                return view(
                    'back-end.freelancer.payouts.payout_settings', compact('payrols', 'payout_settings')
                );
            }
        } else {
            abort(404);
        }
    }


    public function saveCalendarAvailability(Request $request)
    {
        if (Auth::user()) {

            $this->validate(
                $request,
                [
                    'availability_title' => 'required',
                    'availability_content'    => 'required',
                    'start_date'    => 'required',
                    'booking_start'    => 'required',
                    'booking_end'    => 'required',
                ]
            );
            $arrNewEvent['user_id'] = Auth::user()->id;
            $arrNewEvent['title'] = $request['availability_title'];
            $arrNewEvent['content'] = ($request['availability_content'])?$request['availability_content']:'';
            $arrNewEvent['contentFull'] = ($request['availability_content'])?$request['availability_content']:'';
            $arrNewEvent['recurring_date'] = ($request['recurring_date'])?$request['recurring_date']:null;
            $arrNewEvent['recurring_end_date'] = date('Y-m-d', strtotime($request['recurring_end_date']));
            $arrNewEvent['class'] = $request['class']?$request['class']:"";
            $arrNewEvent['skill_id'] = ($request['profession_id'])?$request['profession_id']:null;
            $arrNewEvent['created_at'] = now();
            $arrNewEvent['updated_at'] = now();
            $booking_start = ($request['booking_start']) ? $request['booking_start'] : '23:59';
            $booking_end = ($request['booking_end']) ? $request['booking_end'] : '00:00';
            array_filter($request['start_date']);
            array_filter($request['end_date']);
          
            for($d=0;$d<count($request['start_date']);$d++) {
                if ($request['start_date']) {
                    if ($request['recurring_date']) {
                        $reqStart = $request['start_date'][$d];
                        $reqEnd = ($request['end_date'][$d]) ? $request['end_date'][$d] : $request['start_date'][$d];
                        $recurringEndDay = Carbon::parse($request['recurring_end_date']);
                        $carbStart = new Carbon($reqStart);
                        $carbEnd = Carbon::parse($reqEnd);
                        if ($request['recurring_date'] == 'day') {
                            $difference = ($carbStart->diff($recurringEndDay)->days < 1) ? 'today' : $carbStart->diffInDays($recurringEndDay);
                            echo $difference;
                            for ($g = 0; $g <= $difference; $g++) {
                                $arrNewEvent['start'] = Carbon::parse($reqStart)->addDay($g)->format('Y-m-d') . ' ' . $booking_start;
                                $arrNewEvent['end'] = Carbon::parse($reqEnd)->addDay($g)->format('Y-m-d') . ' ' . $booking_end;
                                //echo $arrNewEvent['start'] . '=>' . $arrNewEvent['end'] . '<br>';
                                DB::table('calendar_events')->insert($arrNewEvent);
                            }
                        } else {
                            if ($request['recurring_date'] == 'week') {
                                $difference = ($carbStart->diff($recurringEndDay)->days < 1) ? 'today' : $carbStart->diffInWeeks($recurringEndDay);
                                for ($g = 0; $g <= $difference; $g++) {
                                    $arrNewEvent['start'] = Carbon::parse($reqStart)->addDay($g * 7)->format('Y-m-d') . ' ' . $booking_start;
                                    $arrNewEvent['end'] = Carbon::parse($reqEnd)->addDay($g * 7)->format('Y-m-d') . ' ' . $booking_end;
                                    //echo $arrNewEvent['start'] . '=>' . $arrNewEvent['end'] . '<br>';
                                    DB::table('calendar_events')->insert($arrNewEvent);
                                }
                            } else {
                                if ($request['recurring_date'] == 'month') {
                                    $difference = ($carbStart->diff($recurringEndDay)->days < 1) ? 'today' : $carbStart->diffInMonths($recurringEndDay);
                                    for ($g = 0; $g <= $difference; $g++) {
                                        $arrNewEvent['start'] = Carbon::parse($reqStart)->addMonth($g)->format('Y-m-d') . ' ' . $booking_start;
                                        $arrNewEvent['end'] = Carbon::parse($reqEnd)->addMonth($g)->format('Y-m-d') . ' ' . $booking_end;
                                        //echo $arrNewEvent['start'] . '=>' . $arrNewEvent['end'] . '<br>';
                                        DB::table('calendar_events')->insert($arrNewEvent);
                                    }
                                }
                            }
                        }

                    }
                    else {
                        $arrNewEvent['start'] = Carbon::parse($request['start_date'][$d])->format('Y-m-d') . ' ' . $booking_start;
                        $arrNewEvent['end'] = $request['end_date'][$d] ? 
                            Carbon::parse($request['end_date'][$d])->format('Y-m-d') . ' ' . $booking_end :
                            Carbon::parse($request['start_date'][$d])->format('Y-m-d') . ' ' . $booking_end;
                        DB::table('calendar_events')->insert($arrNewEvent);
                    }
                }
            }
            return array('success'=>true);
        } else {
            return array('error'=>true);
        }
    }

    /**
     * @param UpdateAvailabilityRequest $request
     * @return mixed
     */
    public function updateCalendarAvailability(UpdateAvailabilityRequest $request)
    {
        if ($request->booking_start && $request->booking_end) {
            $start = Carbon::createFromFormat(
                'd-m-Y H:i:s',
                $request->start_date[0] . $request->booking_start . ':00'
            )->format('Y-m-d H:i:s');
            $end = Carbon::createFromFormat(
                'd-m-Y H:i:s',
                $request->end_date[0] . $request->booking_end . ':00'
            )->format('Y-m-d H:i:s');
        } else {
            $start = Carbon::createFromFormat('d-m-Y', $request->start_date[0])->format('Y-m-d');
            $end = Carbon::createFromFormat('d-m-Y', $request->end_date[0])->format('Y-m-d');
        }

        $newArrEvent = [
            'title' => $request->availability_title,
            'content' => $request->availability_content,
            'class' => $request->class,
            'start' => $start,
            'end' => $end,
            'skill_id' => $request->profession_id,
            'recurring_date' => $request->recurring_date,
            'recurring_end_date' => date('Y-m-d', strtotime($request->recurring_end_date))
        ];
        $affected = CalendarEvent::where('id', $request->event_id)->update($newArrEvent);;
        
        return ['success' => true];
    }
    
    public function deleteCalendarAvailability(Request $request)
    {        
        CalendarEvent::find($request->event_id)->delete();

        return ['success' => true];
    }

    public function getCalendarEvents()
    {
        $arrEvents = DB::table('calendar_events')
            ->where('user_id','=',Auth::user()->id)
            ->get()->all();

        return $arrEvents ?? [];
    }

    public function getAllAvailableExtraProfessions(){
        $professions = Profession::whereIn('role_id', [
                            Role::FREELANCER_ROLE,
                            Role::SUPPORT_ROLE,
                        ])
                        ->orderBy('title')
                        ->get();
        return json_encode($professions);
    }

    public function handleJobInvitation(Request $request){
        $status = $request->status;
        $job = Job::where('slug', $request->job_slug)->first();
        $myProposals = DB::table('proposals')->where('freelancer_id', Auth::user()->id)->where('job_id', $job->id)->get();
        if(!count($myProposals)){
            if($request->status=="accept"){
                $proposal = new Proposal;
                $proposal->freelancer_id = Auth::user()->id;
                $proposal->job_id = $job->id;
                $proposal->content = $request->status=="accept" ? "I am interested in this job." : "I am not interested in this job.";
                $proposal->amount = $job->price;
                $proposal->completion_time = "";
                $proposal->hired = 1;
                $proposal->status = "hired";
                $proposal->save();
            }
    
            //email to employer
            $employer = DB::table('users')
                            ->join('jobs', 'users.id', '=', 'jobs.user_id')
                            ->where('jobs.slug', $request->job_slug)
                            ->first();
            $user = User::find(Auth::user()->id);
            $answered_invitation_template_employer = DB::table('email_types')->select('id')->where('email_type', 'employer_email_invitation_answered')->get()->first();
            $template_data_employer = EmailTemplate::getEmailTemplateByID($answered_invitation_template_employer->id);
            $email_params['job_title'] = $job->title;
            $email_params['status'] = $request->status;
            $email_params['job_proposals_link'] = url('/employer/dashboard/job/' . $job->slug . '/proposals');
            $email_params['name'] = Helper::getUserName(Auth::user()->id);
            $email_params['link'] = url('profile/' . $user->slug);
    
            $templateMailUser = new EmployerEmailMailable(
                'employer_email_invitation_answered',
                $template_data_employer,
                $email_params
            );
            Mail::to($employer->email)
            ->send(
                $templateMailUser
            );
            $messageBodyUser = $templateMailUser->prepareEmployerEmailInvitationAnswered($email_params);
            $notificationMessageUser = ['receiver_id' => $employer->id, 'author_id' => 1, 'message' => $messageBodyUser];
            $serviceUser = new Message();
            $serviceUser->saveNofiticationMessage($notificationMessageUser);

            if (file_exists(resource_path('views/extend/back-end/freelancer/jobs/invite_feedback.blade.php'))) {
                return view(
                    'extend.back-end.freelancer.jobs.invite_feedback', compact('status', 'job')
                );
            } else {
                return view(
                    'back-end.freelancer.jobs.invite_feedback', compact('status', 'job')
                );
            }
        } else{
            return Redirect::to('/dashboard');
        }
    }
}
