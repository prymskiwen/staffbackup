@if (Schema::hasTable('pages') || Schema::hasTable('site_managements'))
    @php
        $settings = array();
        $pages = App\Page::all();
        $setting = \App\SiteManagement::getMetaValue('settings');
        $logo =  '/images/logo2.png';
        $inner_header = !empty(Route::getCurrentRoute()) && Route::getCurrentRoute()->uri() != '/' ? 'wt-headervtwo' : '';
        $type = Helper::getAccessType();
    @endphp
@endif
<header id="wt-header" class="wt-header wt-haslayout {{$inner_header}}">
    <div class="wt-navigationarea">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    @auth
                        {{ Helper::displayEmailWarning() }}
                    @endauth
                    @if (!empty($logo) || Schema::hasTable('site_managements'))
                        <strong class="wt-logo"><a href="{{{ url('/') }}}"><img src="{{{ asset($logo) }}}" alt="{{{ trans('Logo') }}}"></a></strong>
                    @endif

                    <div class="wt-rightarea" style="height: 90px;">
                        <nav id="wt-nav" class="wt-nav navbar-expand-lg">
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                <i class="lnr lnr-menu"></i>
                            </button>
                            <div class="collapse navbar-collapse wt-navigation" id="navbarNav">
                                <div class="row " >
                                    <div class="mainhomeMenu">
                                        <ul id="newmenu"  class="list-unstyled" style="list-style: none;">
                                            <li><a href="{{url('/')}}">START BROWSING ADHOC STAFF</a></li>
                                            <li><a href="{{url('page/how-it-works')}}">FIND TEMPORARY SHORT TERM WORK</a></li>
                                            <li><a href="">FAQs</a></li>
                                            <li style="border-right:none"><a href="" style="color:#2a3b65">CONTACT US<br> FOR INFORMATION</a></li>

                                        </ul>

                                    </div>
                                    {{--<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 telno" >--}}
                                    {{--<span>Email:info@staffbackup.co.uk</span>--}}
                                    {{--</div>--}}
                                </div>

                            </div>
                            <div class="collapse navbar-collapse wt-navigation" id="navbarNav2" style="margin-top: 118px; align-items:normal">

                                @if(!\Request::is('search-results') && !\Request::is('register'))
                                <ul class="navbar-nav" style="margin-top: 5px;">
                                    @if (!empty($pages) || Schema::hasTable('pages'))
                                        @foreach ($pages as $key => $page)
                                            @php
                                                $page_has_child = App\Page::pageHasChild($page->id); $pageID = Request::segment(2);
                                                $show_page = \App\SiteManagement::where('meta_key', 'show-page-'.$page->id)->select('meta_value')->pluck('meta_value')->first();
                                            @endphp
                                            @if ($page->relation_type == 0 && $show_page == 'true')
                                                <li class="{{!empty($page_has_child) ? 'menu-item-has-children page_item_has_children' : '' }} @if ($pageID == $page->slug ) current-menu-item @endif">
                                                    <a href="{{url('page/'.$page->slug)}}">{{{$page->title}}}</a>
                                                    @if (!empty($page_has_child))
                                                        <ul class="sub-menu">
                                                            @foreach($page_has_child as $parent)
                                                                @php $child = App\Page::getChildPages($parent->child_id);@endphp
                                                                <li class="@if ($pageID == $child->slug ) current-menu-item @endif">
                                                                    <a href="{{url('page/'.$child->slug.'/')}}">
                                                                        {{{$child->title}}}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                    <li>
                                        <a href="{{url('search-results?type=freelancer')}}">
                                            {{{ trans('lang.view_freelancers') }}}
                                        </a>
                                    </li>

                                    @if ($type =='jobs' || $type == 'both')
                                        <li>
                                            <a href="{{url('search-results?type=job')}}">
                                                {{{ trans('lang.browse_jobs') }}}
                                            </a>
                                        </li>
                                    @endif
                                    @if ($type =='services' || $type == 'both')
                                        <li>
                                            <a href="{{url('search-results?type=service')}}">
                                                {{{ trans('lang.browse_services') }}}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                @endif
                                @auth
                                    @php
                                        $user = !empty(Auth::user()) ? Auth::user() : '';
                                        $role = !empty($user) ? $user->getRoleNames()->first() : array();
                                        $profile = \App\User::find($user->id)->profile;
                                        $user_image = !empty($profile) ? $profile->avater : '';
                                        $employer_job = \App\Job::select('status')->where('user_id', Auth::user()->id)->first();
                                        $profile_image = !empty($user_image) ? '/uploads/users/'.$user->id.'/'.$user_image : 'images/user-login.png';
                                        $payment_settings = \App\SiteManagement::getMetaValue('commision');
                                        $payment_module = !empty($payment_settings) && !empty($payment_settings[0]['enable_packages']) ? $payment_settings[0]['enable_packages'] : 'true';
                                        $employer_payment_module = !empty($payment_settings) && !empty($payment_settings[0]['employer_package']) ? $payment_settings[0]['employer_package'] : 'true';
                                    @endphp
                                    <div class="wt-userlogedin">
                                        <figure class="wt-userimg" style="float:none">
                                            <img src="{{{ asset($profile_image) }}}"
                                                 alt="{{{ trans('lang.user_avatar') }}}">
                                        </figure>
                                        <div class="wt-username" style="margin-top: 10px; text-align: center">
                                            <h3 style="font-size: 13px;">{{{ Helper::getUserName(Auth::user()->id) }}}</h3>
                                            <div style="font-size:10px;color:darkgrey">{{{ !empty(Auth::user()->profile->tagline) ? str_limit(Auth::user()->profile->tagline, 26, '') : Helper::getAuthRoleName() }}}</div>
                                        </div>
                                        @if (file_exists(resource_path('views/extend/back-end/includes/profile-menu.blade.php')))
                                            @include('extend.back-end.includes.profile-menu')
                                        @else
                                            @include('back-end.includes.profile-menu')
                                        @endif
                                    </div>
                                @endauth
                            </div>
                        </nav>
                        @guest
                            <div class="wt-loginarea">
                                <div class="wt-loginoption">
                                    <a href="javascript:void(0);" id="wt-loginbtn" class="wt-loginbtn">{{{trans('lang.login') }}}</a>
                                    <div class="wt-loginformhold" @if ($errors->has('email') || $errors->has('password')) style="display: block;" @endif>
                                        <div class="wt-loginheader">
                                            <span>{{{ trans('lang.login') }}}</span>
                                            <a href="javascript:;"><i class="fa fa-times"></i></a>
                                        </div>
                                        <form method="POST" action="{{ route('login') }}" class="wt-formtheme wt-loginform do-login-form">
                                            @csrf
                                            <fieldset>
                                                <div class="form-group">
                                                    <input id="email" type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                                        placeholder="Email" required autofocus>
                                                    @if ($errors->has('email'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group">
                                                    <input id="password" type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                                        placeholder="Password" required>
                                                    @if ($errors->has('password'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('password') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="wt-logininfo">
                                                        <button type="submit" class="wt-btn do-login-button">{{{ trans('lang.login') }}}</button>
                                                    <span class="wt-checkbox">
                                                        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                        <label for="remember">{{{ trans('lang.remember') }}}</label>
                                                    </span>
                                                </div>
                                            </fieldset>
                                            <div class="wt-loginfooterinfo">
                                                @if (Route::has('password.request'))
                                                    <a href="{{ route('password.request') }}" class="wt-forgot-password">{{{ trans('lang.forget_pass') }}}</a>
                                                @endif
                                                <a href="{{{ route('register') }}}">{{{ trans('lang.create_account') }}}</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <a href="{{{ route('register') }}}" class="wt-btn">{{{ trans('lang.join_now') }}}</a>
                            </div>
                        @endguest

                        {{--@if (!empty(Route::getCurrentRoute()) && Route::getCurrentRoute()->uri() != '/' && Route::getCurrentRoute()->uri() != 'home')--}}
                            {{--<div class="wt-respsonsive-search"><a href="javascript:;" class="wt-searchbtn"><i class="fa fa-search"></i></a></div>--}}
                        {{--@endif--}}


                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
