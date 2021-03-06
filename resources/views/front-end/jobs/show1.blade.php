@extends(file_exists(resource_path('views/extend/front-end/master.blade.php')) ?
'extend.front-end.master':
 'front-end.master', ['body_class' => 'wt-innerbgcolor'] )
@section('title'){{ $job->title }} @stop
@section('description', "$job->description")
@section('content')
<div class="content-public-profile" id="user_profile">
    <div class="content-public-profile__wrapper">
        <section class="block-circles">
            <div class="block-circles__container">
                <div class="block-circles__wrapper">
                    <div class="block-circles__item block-circles__item-cyan"></div>
                    <div class="block-circles__item block-circles__item-blue"></div>
                    <div class="block-circles__item block-circles__item-yellow"></div>
                </div>
            </div>
        </section><!-- .circles -->

        <section class="content-public-profile__main-content">
            <div class="content-public-profile__main-content-wrapper">
                <a class="content-public-profile__main-content-back-btn"
                    href="{{ $back_url }}"><button>Back</button></a>

                <!-- Left content -->
                <div class="content-public-profile__main-content-left">

                    <h4 class="content-public-profile__main-content-slag fs-14">
                    {{ trans('lang.project_id') . ": " . $job->code }}
                    </h4>
                    <p class="fs-14">{{ trans('lang.created_at') }}&nbsp;{{ date('d-m-Y H:i', strtotime($job->created_at)) }} </p>

                    <!-- <h2 class="content-public-profile__main-content-name mbottom35 fs-14"> -->
                    <div class="wt-widgettitle wt-companysinfo">
                        <div class="wt-title">
                            <h2>
                                @if(!empty($job->title))
                                {{ $job->title }}
                                @else
                                Undefined
                                @endif
                            </h2>
                        </div>
                    </div>

                    @if($job->description != "")
                    <div class="content-public-profile__main-content-text-block mbottom35">
                        <span class="wt-category-title">Description:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Description:</span> -->
                        <div class="fs-14">{{ $job->description }}</div>
                    </div>
                    @endif

                    @if (!empty($job->professions))
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35">
                        <span class="wt-category-title">Profession:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Profession:</span> -->
                        <div class="wt-tag wt-widgettag d-flex justify-content-center">
                        @foreach ($job->professions as $profession)
                            <a href="#">{{{ $profession->title }}}</a>
                        @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if ($job->employer->itsoftware != "")
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35">
                        <span class="wt-category-title">Computer System in use:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Computer System in use:</span> -->
                        <div class="fs-14">{{ implode(', ', $job->employer->getItsoftware()) }}</div>
                    </div>
                    @endif

                    @if(!empty($job->calendars))
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35">
                        <span class="wt-category-title">Start and End time:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Start and End time:</span> -->
                        @foreach($job->calendars as $calendar_event)
                            @if($calendar_event->class=="booking_calendar" || $calendar_event->class=="booking_hired")
                            <div class="fs-14">Start: {{$calendar_event->start->format('d-m-Y H:i')}}</div>
                            <div class="fs-14">End: {{$calendar_event->end->format('d-m-Y H:i')}}</div>
                            @endif
                        @endforeach
                    </div>
                    @endif
                    @php
                    $breaks = @unserialize($job->breaks);
                    @endphp
                    @if($breaks)
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35">
                        <span class="wt-category-title">Breaks:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Breaks:</span> -->
                        <div class="fs-14">
                        @foreach($breaks as $break)
                        {{ $break->when . ": "}} {{ $break->for }}
                        @endforeach 
                        </div>
                    </div>
                    @endif
                    @if($job->job_adm_catch_time_interval)
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35 fs-14">
                        <span class="wt-category-title">Admin Catch Up Provided (interval):</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Admin Catch Up Provided (interval):</span> -->
                        {{ $job->job_adm_catch_time_interval }}
                    </div>
                    @endif
                    @if($job->job_appo_slot_times)
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35 fs-14">
                        <span class="wt-category-title">Appointment Slot Times:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Appointment Slot Times:</span> -->
                        {{ $job->job_appo_slot_times }}
                    </div>
                    @endif
                    @if($job->home_visits)
                    <div class="content-public-profile__main-content-separator"></div>
                    <div class="content-public-profile__main-content-text-block mtop35 mbottom35 fs-14">
                        <span class="wt-category-title">Home Visits:</span>
                        <!-- <span class="content-public-profile__main-content-title fs-14">Home Visits:</span> -->
                        {{ $job->home_visits }}
                    </div>
                    @endif
                    @if (!empty($attachments) && $job->show_attachments === 'true')
                        <div class="wt-attachments">
                            <span class="wt-category-title">{{ trans('lang.attachments') }}</span>
                            <ul class="wt-attachfile">
                                @foreach ($attachments as $attachment)
                                    <li>
                                        <span>{{{Helper::formateFileName($attachment)}}}</span>
                                        <em>
                                            @if (Storage::disk('local')->exists('uploads/jobs/'.$job->employer->id.'/'.$attachment))
                                                {{ trans('lang.file_size') }} {{{Helper::bytesToHuman(Storage::size('uploads/jobs/'.$job->employer->id.'/'.$attachment))}}}
                                            @endif
                                            <a href="{{{route('getfile', ['type'=>'jobs','attachment'=>$attachment,'id'=>$job->user_id])}}}"><i class="lnr lnr-download"></i></a>
                                        </em>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Right content -->
                <div class="content-public-profile__main-content-right">
                @if (file_exists(resource_path('views/extend/front-end/jobs/sidebar1/index.blade.php')))
                    @include('extend.front-end.jobs.sidebar1.index')
                @else
                    @include('front-end.jobs.sidebar1.index')
                @endif
                </div>
            </div>
        </section>
        <!-- .content-public-profile__main-content -->

        <section class="block-circles">
            <div class="block-circles__container block-circles__container-last">
                <div class="block-circles__wrapper">
                    <div class="block-circles__item block-circles__item-blue"></div>
                    <div class="block-circles__item block-circles__item-blue"></div>
                    <div class="block-circles__item block-circles__item-blue"></div>
                </div>
            </div>
        </section><!-- .circles -->
    </div>
</div>
@endsection
@push('scripts')
    <script>
        var popupMeta = {
            width: 400,
            height: 400
        }
        $(document).on('click', '.social-share', function(event){
            event.preventDefault();

            var vPosition = Math.floor(($(window).width() - popupMeta.width) / 2),
                hPosition = Math.floor(($(window).height() - popupMeta.height) / 2);

            var url = $(this).attr('href');
            var popup = window.open(url, 'Social Share',
                'width='+popupMeta.width+',height='+popupMeta.height+
                ',left='+vPosition+',top='+hPosition+
                ',location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1');

            if (popup) {
                popup.focus();
                return false;
            }
        })
    </script>
@endpush
