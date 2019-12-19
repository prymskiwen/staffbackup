@extends(file_exists(resource_path('views/extend/front-end/master.blade.php')) ? 'extend.front-end.master' : 'front-end.master')
@section('content')
    @php
        $employees      = Helper::getEmployeesList();
        $departments    = App\Department::all();
        $locations      = App\Location::select('title', 'id')->get()->pluck('title', 'id')->toArray();
        $roles          = Spatie\Permission\Models\Role::all()->toArray();
        $register_form = App\SiteManagement::getMetaValue('reg_form_settings');
        $reg_one_title = !empty($register_form) && !empty($register_form[0]['step1-title']) ? $register_form[0]['step1-title'] : trans('lang.join_for_good');
        $reg_one_subtitle = !empty($register_form) && !empty($register_form[0]['step1-subtitle']) ? $register_form[0]['step1-subtitle'] : trans('lang.join_for_good_reason');
        $reg_two_title = !empty($register_form) && !empty($register_form[0]['step2-title']) ? $register_form[0]['step2-title'] : trans('lang.pro_info');
        $reg_two_subtitle = !empty($register_form) && !empty($register_form[0]['step2-subtitle']) ? $register_form[0]['step2-subtitle'] : '';
        $term_note = !empty($register_form) && !empty($register_form[0]['step2-term-note']) ? $register_form[0]['step2-term-note'] : trans('lang.agree_terms');
        $reg_three_title = !empty($register_form) && !empty($register_form[0]['step3-title']) ? $register_form[0]['step3-title'] : trans('lang.almost_there');
        $reg_three_subtitle = !empty($register_form) && !empty($register_form[0]['step3-subtitle']) ? $register_form[0]['step3-subtitle'] : trans('lang.acc_almost_created_note');
        $register_image = !empty($register_form) && !empty($register_form[0]['register_image']) ? '/uploads/settings/home/'.$register_form[0]['register_image'] : 'images/work.jpg';
        $reg_page = !empty($register_form) && !empty($register_form[0]['step3-page']) ? $register_form[0]['step3-page'] : '';
        $reg_four_title = !empty($register_form) && !empty($register_form[0]['step4-title']) ? $register_form[0]['step4-title'] : trans('lang.congrats');
        $reg_four_subtitle = !empty($register_form) && !empty($register_form[0]['step4-subtitle']) ? $register_form[0]['step4-subtitle'] : trans('lang.acc_creation_note');
        $show_emplyr_inn_sec = !empty($register_form) && !empty($register_form[0]['show_emplyr_inn_sec']) ? $register_form[0]['show_emplyr_inn_sec'] : 'true';
        $show_reg_form_banner = !empty($register_form) && !empty($register_form[0]['show_reg_form_banner']) ? $register_form[0]['show_reg_form_banner'] : 'true';
        $reg_form_banner = !empty($register_form) && !empty($register_form[0]['reg_form_banner']) ? $register_form[0]['reg_form_banner'] : null;
        $breadcrumbs_settings = \App\SiteManagement::getMetaValue('show_breadcrumb');
        $show_breadcrumbs = !empty($breadcrumbs_settings) ? $breadcrumbs_settings : 'true';
        $cqc_ratings = array(
                            'Outstanding'=>'Outstanding',
                            'Good'=>'Good',
                            'Requires Improvement'=>'Requires Improvement',
                            'Inadequate'=> 'Inadequate'
                            );
    $cqc_ratings_date = array(
    '2015'=>'2015',
    '2016'=>'2016',
    '2017'=>'2017',
    '2018'=>'2018',
    '2019'=>'2019',

    );
    $payment_options = array(
    'Paypal'=>'Paypal',
    'BACS'=>'BACS',
    'Cheque'=>'Cheque'
    );
    $subscribe_options  = array(
    'plan_G6DvQf9zdEGczW'=>'6 Months',
    'plan_G6DvMJGDvP6wGz'=>'3 Months',
    'plan_G6DuLUGgkizyrs'=>'Monthly'
    );

    $arrSettings = array(
    'GP Surgery'=>'GP Surgery',
    'Walk-In centre'=>'Walk-In centre',
    'Urgent Care Centre'=>'Urgent Care Centre',
    'GP Out Of Hours'=>'GP Out Of Hours',
    'Community Service'=>'Community Service',
    'Other'=>'Other',
    );
    $arrProfReq = array(
    'Practice Manager'=>'Practice Manager',
    'Practice Nurse'=>'Practice Nurse',
    'Advanced Nurse Practitioner'=>'Advanced Nurse Practitioner',
    'GP'=>'GP',
    'Receptionist'=>'Receptionist',
    'Admin & Clerical'=>'Admin & Clerical',
    'Cleaner in clinical settings'=>'Cleaner in clinical settings',
    'Pharmacist'=>'Pharmacist',
    'Community Nurse'=>'Community Nurse',
    'District Nurse'=>'District Nurse',
    'Healthcare Assistant (HCA)'=>'Healthcare Assistant (HCA)',
    );

    $arrSpecialInterests = array(
    'Diabetes'=>'Diabetes',
    'Rhematology'=>'Rhematology',
    'Neurology'=>'Neurology',
    'Dermatology'=>'Dermatology',
    'Asthma'=>'Asthma',
    'Mental Health'=>'Mental Health',
    'Substance Misuse and Addictions'=>'Substance Misuse and Addictions',
    'MSK'=>'MSK',
    'Paediatrics'=>'Paediatrics',
    'Cardiology'=>'Cardiology',
    'Gastrointestinal'=>'Gastrointestinal',
    'Other'=>'Other',
    );

    $arrAppo_slot_times = array(
    '10 minutes'=>'10 minutes',
    '15 minutes'=>'15 minutes',
    '20 minutes'=>'20 minutes',
    '30 minutes'=>'30 minutes',
    'Other'=>'Other',
    );

    $arrBreaks = array(
    'Morning Break'=>'Morning Break',
    'Lunch Break'=>'Lunch Break',
    'Afternoon Break'=>'Afternoon Break',
    'Evening Break'=>'Evening Break',
    'Not Applicable' => 'Not Applicable',
    );

    $arrPaymentTerms = array(
    'Invoices usually paid within 7 days of receipt'=>'Invoices usually paid within 7 days of receipt',
    'Invoices usually paid within 14 days of receipt'=>'Invoices usually paid within 14 days of receipt',
    'Other'=>'Other',
    );
    $nationals = array(
        'Afghan',
        'Albanian',
        'Algerian',
        'American',
        'Andorran',
        'Angolan',
        'Antiguans',
        'Argentinean',
        'Armenian',
        'Australian',
        'Austrian',
        'Azerbaijani',
        'Bahamian',
        'Bahraini',
        'Bangladeshi',
        'Barbadian',
        'Barbudans',
        'Batswana',
        'Belarusian',
        'Belgian',
        'Belizean',
        'Beninese',
        'Bhutanese',
        'Bolivian',
        'Bosnian',
        'Brazilian',
        'British',
        'Bruneian',
        'Bulgarian',
        'Burkinabe',
        'Burmese',
        'Burundian',
        'Cambodian',
        'Cameroonian',
        'Canadian',
        'Cape Verdean',
        'Central African',
        'Chadian',
        'Chilean',
        'Chinese',
        'Colombian',
        'Comoran',
        'Congolese',
        'Costa Rican',
        'Croatian',
        'Cuban',
        'Cypriot',
        'Czech',
        'Danish',
        'Djibouti',
        'Dominican',
        'Dutch',
        'East Timorese',
        'Ecuadorean',
        'Egyptian',
        'Emirian',
        'Equatorial Guinean',
        'Eritrean',
        'Estonian',
        'Ethiopian',
        'Fijian',
        'Filipino',
        'Finnish',
        'French',
        'Gabonese',
        'Gambian',
        'Georgian',
        'German',
        'Ghanaian',
        'Greek',
        'Grenadian',
        'Guatemalan',
        'Guinea-Bissauan',
        'Guinean',
        'Guyanese',
        'Haitian',
        'Herzegovinian',
        'Honduran',
        'Hungarian',
        'I-Kiribati',
        'Icelander',
        'Indian',
        'Indonesian',
        'Iranian',
        'Iraqi',
        'Irish',
        'Israeli',
        'Italian',
        'Ivorian',
        'Jamaican',
        'Japanese',
        'Jordanian',
        'Kazakhstani',
        'Kenyan',
        'Kittian and Nevisian',
        'Kuwaiti',
        'Kyrgyz',
        'Laotian',
        'Latvian',
        'Lebanese',
        'Liberian',
        'Libyan',
        'Liechtensteiner',
        'Lithuanian',
        'Luxembourger',
        'Macedonian',
        'Malagasy',
        'Malawian',
        'Malaysian',
        'Maldivan',
        'Malian',
        'Maltese',
        'Marshallese',
        'Mauritanian',
        'Mauritian',
        'Mexican',
        'Micronesian',
        'Moldovan',
        'Monacan',
        'Mongolian',
        'Moroccan',
        'Mosotho',
        'Motswana',
        'Mozambican',
        'Namibian',
        'Nauruan',
        'Nepalese',
        'New Zealander',
        'Nicaraguan',
        'Nigerian',
        'Nigerien',
        'North Korean',
        'Northern Irish',
        'Norwegian',
        'Omani',
        'Pakistani',
        'Palauan',
        'Panamanian',
        'Papua New Guinean',
        'Paraguayan',
        'Peruvian',
        'Polish',
        'Portuguese',
        'Qatari',
        'Romanian',
        'Russian',
        'Rwandan',
        'Saint Lucian',
        'Salvadoran',
        'Samoan',
        'San Marinese',
        'Sao Tomean',
        'Saudi',
        'Scottish',
        'Senegalese',
        'Serbian',
        'Seychellois',
        'Sierra Leonean',
        'Singaporean',
        'Slovakian',
        'Slovenian',
        'Solomon Islander',
        'Somali',
        'South African',
        'South Korean',
        'Spanish',
        'Sri Lankan',
        'Sudanese',
        'Surinamer',
        'Swazi',
        'Swedish',
        'Swiss',
        'Syrian',
        'Taiwanese',
        'Tajik',
        'Tanzanian',
        'Thai',
        'Togolese',
        'Tongan',
        'Trinidadian/Tobagonian',
        'Tunisian',
        'Turkish',
        'Tuvaluan',
        'Ugandan',
        'Ukrainian',
        'Uruguayan',
        'Uzbekistani',
        'Venezuelan',
        'Vietnamese',
        'Welsh',
        'Yemenite',
        'Zambian',
        'Zimbabwean'
);
    $arrNationals = array();
    foreach($nationals as $national)
    {
        $arrNationals[$national] = $national;
    }

    $arrPaymentMethods = array(
    'Limited Company'=>'Limited Company',
    'Self Employed'=>'Self Employed',
    );

    $arrITSoftware = array(
       'Adastra'=>'Adastra',
        'Cerna'=>'Cerna',
        'Cerna Millenium'=>'Cerna Millenium',
        'Cleo'=>'Cleo',
        'DGL'=>'DGL',
        'Docman'=>'Docman',
        'Edis & A&E System'=>'Edis & A&E System',
        'Emis Community'=>'Emis Community',
        'Emis LV'=>'Emis LV',
        'Emis PCS'=>'Emis PCS',
        'Emis Web'=>'Emis Web',
        'Frontdesk'=>'Frontdesk',
        'Heydoc'=>'Heydoc',
        'Infoslex'=>'Infoslex',
        'Microtest'=>'Microtest',
        'Premiere'=>'Premiere',
        'Symphony'=>'Symphony',
        'Synergy'=>'Synergy',
        'SystmOne'=>'SystmOne',
        'Torex'=>'Torex',
        'Vision'=>'Vision',
        'Vision Anywhere'=>'Vision Anywhere',
    );

    $arrOrgTypes = array(
        'NHS'=>'NHS',
        'Private organisation providing NHS care'=>'Private organisation providing NHS care',
        'Private organisation proving private healthcare'=>'Private organisation proving private healthcare',
    );
    @endphp
    <script src="https://js.stripe.com/v3"></script>
    <style type="text/css">
        select {
            width: 100% !important;
        }
    </style>
    @if (!empty($show_reg_form_banner) && $show_reg_form_banner === 'true')
        <div class="wt-haslayout wt-innerbannerholder">
            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-xs-12 col-sm-12 col-md-8 push-md-2 col-lg-6 push-lg-3">
                        <div class="wt-innerbannercontent">
                            <div class="wt-title">
                                <h2>{{ trans('lang.join_for_free') }}</h2>
                            </div>
                            @if (!empty($show_breadcrumbs) && $show_breadcrumbs === 'true')
                                <ol class="wt-breadcrumb">
                                    <li><a href="{{ url('/') }}">{{ trans('lang.home') }}</a></li>
                                    <li class="wt-active">{{ trans('lang.join_now') }}</li>
                                </ol>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="wt-haslayout wt-main-section">
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col-xs-12 col-sm-12 col-md-10 push-md-1 col-lg-8 push-lg-2" id="registration">
                    <div class="preloader-section" v-if="loading" v-cloak>
                        <div class="preloader-holder">
                            <div class="loader"></div>
                        </div>
                    </div>
                    <form method="POST" action="{{{ url('register/form-step1-custom-errors') }}}"
                          class="wt-formtheme wt-formregister" @submit.prevent="checkStep1"
                          id="register_form">
                        <div class="wt-registerformhold">
                            <div class="wt-registerformmain">
                                <div class="wt-joinforms">

                                    @csrf
                                    <input type="hidden" name="stripe_token" v-model="stripe_token"/>

                                    <fieldset class="wt-registerformgroup">
                                        <div class="wt-haslayout" v-show="step === 1" v-cloak>
                                            <div class="wt-registerhead">
                                                <div class="wt-title">
                                                    <h3>{{{ $reg_one_title }}}</h3>
                                                </div>
                                                <div class="wt-description">
                                                    <p>{{{ $reg_one_subtitle }}}</p>
                                                </div>
                                            </div>
                                            <ul class="wt-joinsteps">
                                                <li class="wt-active"><a
                                                            href="javascrip:void(0);">{{{ trans('lang.01') }}}</a></li>
                                                <li><a href="javascrip:void(0);">{{{ trans('lang.02') }}}</a></li>
                                                <li><a href="javascrip:void(0);">{{{ trans('lang.03') }}}</a></li>
                                                <li><a href="javascrip:void(0);">{{{ trans('lang.04') }}}</a></li>
                                            </ul>
                                            <div class="form-group form-group-half">
                                                <input type="text" name="first_name" class="form-control"
                                                       placeholder="{{{ trans('lang.ph_first_name') }}}"
                                                       v-bind:class="{ 'is-invalid': form_step1.is_first_name_error }"
                                                       v-model="first_name">
                                                <span class="help-block" v-if="form_step1.first_name_error">
                                                <strong v-cloak>@{{form_step1.first_name_error}}</strong>
                                            </span>
                                            </div>
                                            <div class="form-group form-group-half">
                                                <input type="text" name="last_name" class="form-control"
                                                       placeholder="{{{ trans('lang.ph_last_name') }}}"
                                                       v-bind:class="{ 'is-invalid': form_step1.is_last_name_error }"
                                                       v-model="last_name">
                                                <span class="help-block" v-if="form_step1.last_name_error">
                                                <strong v-cloak>@{{form_step1.last_name_error}}</strong>
                                            </span>
                                            </div>
                                            <div class="form-group">
                                                <input id="user_email" type="email" class="form-control" name="email"
                                                       placeholder="{{{ trans('lang.ph_email') }}}"
                                                       value="{{ old('email') }}"
                                                       v-bind:class="{ 'is-invalid': form_step1.is_email_error }"
                                                       v-model="user_email">
                                                <span class="help-block" v-if="form_step1.email_error">
                                                <strong v-cloak>@{{form_step1.email_error}}</strong>
                                            </span>
                                            </div>
                                            <div class="form-group">
                                                <button type="submit"
                                                        class="wt-btn">{{{  trans('lang.btn_startnow') }}}</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <div class="wt-haslayout" v-show="step === 2" v-cloak>
                                        <fieldset class="wt-registerformgroup">
                                            <div class="wt-registerhead">
                                                <div class="wt-title">
                                                    <h3>{{{ $reg_two_title }}}</h3>
                                                </div>
                                                @if (!empty($reg_two_subtitle))
                                                    <div class="wt-description">
                                                        <p>{{{ $reg_two_subtitle }}}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <ul class="wt-joinsteps">
                                                <li class="wt-done-next"><a href="javascrip:void(0);"><i
                                                                class="fa fa-check"></i></a></li>
                                                <li class="wt-active"><a
                                                            href="javascrip:void(0);">{{{ trans('lang.02') }}}</a></li>
                                                <li><a href="javascrip:void(0);">{{{ trans('lang.03') }}}</a></li>
                                                <li><a href="javascrip:void(0);">{{{ trans('lang.04') }}}</a></li>
                                            </ul>
                                            <div class="form-group  form-group-half">
                                                <input id="number" type="text"
                                                       class="form-control"
                                                       name="number"
                                                       placeholder="{{{ trans('lang.number') }}}"
                                                >
                                            </div>
                                            <div class="form-group  form-group-half">
                                                <input id="emp_website" type="url"
                                                       class="form-control"
                                                       name="emp_website"
                                                       placeholder="{{{ trans('lang.emp_website') }}}"
                                                       v-bind:class="{ 'is-invalid': form_step2.emp_website_error }">
                                                <span class="help-block"
                                                      v-if="form_step2.emp_website_error">
                                                                                <strong v-cloak>@{{form_step2.emp_website_error}}</strong>
                                                                            </span>
                                            </div>
                                            <div class="form-group  form-group-half">
                                                <input id="straddress" type="text"
                                                       class="form-control"
                                                       name="straddress"
                                                       placeholder="Organisation Address"
                                                >
                                            </div>
                                            <div class="form-group  form-group-half">
                                                <input id="city" type="text"
                                                       class="form-control"
                                                       name="city"
                                                       placeholder="{{{ trans('lang.city') }}}"
                                                >
                                            </div>
                                            <div class="form-group  form-group-half">
                                                <input id="postcode" type="text"
                                                       class="form-control"
                                                       name="postcode"
                                                       placeholder="{{{ trans('lang.postcode') }}}"
                                                >
                                            </div>
                                            <div class="form-group form-group-half">
                                                <input id="password" type="password" class="form-control"
                                                       name="password" placeholder="{{{ trans('lang.ph_pass') }}}"
                                                       v-bind:class="{ 'is-invalid': form_step2.is_password_error }">
                                                <span class="help-block" v-if="form_step2.password_error">
                                                <strong v-cloak>@{{form_step2.password_error}}</strong>
                                            </span>
                                            </div>
                                            <div class="form-group form-group-half">
                                                <input id="password-confirm" type="password" class="form-control"
                                                       name="password_confirmation"
                                                       placeholder="{{{ trans('lang.ph_retry_pass') }}}"
                                                       v-bind:class="{ 'is-invalid': form_step2.is_password_confirm_error }">
                                                <span class="help-block" v-if="form_step2.password_confirm_error">
                                                <strong v-cloak>@{{form_step2.password_confirm_error}}</strong>
                                            </span>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::select('itsoftware', $arrITSoftware, null, array('placeholder' => "IT software")) !!}
                                            </div>

                                        </fieldset>
                                        <fieldset class="wt-formregisterstart">
                                            <div class="wt-title wt-formtitle">
                                                <h4>{{{ trans('lang.start_as') }}}</h4>
                                            </div>
                                            @if(!empty($roles))
                                                <ul class="wt-accordionhold wt-formaccordionhold accordion">
                                                    @foreach ($roles as $key => $role)
                                                        @if (!in_array($role['id'] == 1, $roles))
                                                            <li style="width:50%">
                                                                <div class="wt-accordiontitle" id="headingOne"
                                                                     style="height: 69px; border: 1px solid #ddd;"
                                                                     data-toggle="collapse" data-target="#collapseOne">
                                                                <span class="wt-radio">
                                                                <input id="wt-company-{{$key}}" type="radio" name="role"
                                                                       value="{{{ $role['role_type'] }}}" checked=""
                                                                       v-model="user_role"
                                                                       v-on:change="selectedRole(user_role)">
                                                                <label for="wt-company-{{$key}}">
                                                                    {{ $role['name'] === 'freelancer' ? trans('lang.freelancer') : trans('lang.employer')}}
                                                                    <span> 
                                                                        ({{ $role['name'] === 'freelancer' ? trans('lang.signup_as_freelancer') : trans('lang.signup_as_country')}}
                                                                        )
                                                                    </span>
                                                                </label>
                                                                </span>
                                                                </div>

                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                                @foreach ($roles as $key => $role)
                                                    @if (!in_array($role['id'] == 1, $roles))
                                                        @if ($role['role_type'] === 'employer')
                                                            @if ($show_emplyr_inn_sec === 'true')
                                                                <div class="wt-accordiondetails collapse show"
                                                                     id="collapseOne" aria-labelledby="headingOne"
                                                                     v-if="is_show">
                                                                    <div>
                                                                        <h4>Company Contact</h4>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="emp_contact" type="text"
                                                                                   class="form-control"
                                                                                   name="emp_contact"
                                                                                   placeholder="{{{ trans('lang.emp_contact') }}}"
                                                                                   v-bind:class="{ 'is-invalid': form_step2.emp_contact_error }">
                                                                            <span class="help-block"
                                                                                  v-if="form_step2.emp_contact_error">
                                                                            <strong v-cloak>@{{form_step2.emp_contact_error}}</strong>
                                                                            </span>
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="emp_telno" type="tel"
                                                                                   class="form-control"
                                                                                   name="emp_telno"
                                                                                   placeholder="{{{ trans('lang.emp_telno') }}}"
                                                                                   v-bind:class="{ 'is-invalid': form_step2.emp_telno_error }">
                                                                            <span class="help-block"
                                                                                  v-if="form_step2.emp_telno_error">
                                                                        <strong v-cloak>@{{form_step2.emp_telno_error}}</strong>
                                                                            </span>
                                                                        </div>



                                                                        <div class="form-group form-group-half">
                                                                            <input id="emp_pos" type="url"
                                                                                   class="form-control"
                                                                                   name="emp_pos"
                                                                                   placeholder="Position"
                                                                            >
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="emp_email" type="url"
                                                                                   class="form-control"
                                                                                   name="emp_email"
                                                                                   placeholder="Email"
                                                                            >
                                                                        </div>

                                                                        <h4>Backup Company contact</h4>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="backup_emp_contact" type="url"
                                                                                   class="form-control"
                                                                                   name="backup_emp_contact"
                                                                                   placeholder="Backup Company Contact"
                                                                            >
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="backup_emp_email" type="url"
                                                                                   class="form-control"
                                                                                   name="backup_emp_email"
                                                                                   placeholder="Backup Company Email"
                                                                            >
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="backup_emp_pos" type="url"
                                                                                   class="form-control"
                                                                                   name="backup_emp_pos"
                                                                                   placeholder="Backup Company Position"
                                                                            >
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="backup_emp_tel" type="url"
                                                                                   class="form-control"
                                                                                   name="backup_emp_tel"
                                                                                   placeholder="Backup Company Tel"
                                                                            >
                                                                        </div>


                                                                        <div class="form-group form-group-half">
                                                                            {!! Form::select('emp_cqc_rating_date', $cqc_ratings_date, null, array('placeholder' => trans('lang.emp_cqc_rating_date'), 'class' => 'form-group', 'v-bind:class' => '{ "is-invalid": form_step2.emp_cqc_rating_date_error }')) !!}

                                                                        </div>
                                                                        <div class="form-group form-group-half">

                                                                            {!! Form::select('emp_cqc_rating', $cqc_ratings, null, array('placeholder' => trans('lang.emp_cqc_rating'), 'class' => 'form-group', 'v-bind:class' => '{ "is-invalid": form_step2.emp_cqc_rating_error }')) !!}

                                                                            <span class="help-block"
                                                                                  v-if="form_step2.emp_cqc_rating_error">
                                                                                    <strong v-cloak>@{{form_step2.emp_cqc_rating_error}}</strong>
                                                                                </span>
                                                                        </div>

                                                                        <!-- New columns for sheet-->

                                                                        <div class="form-group ">
                                                                            <label for="org_type">Please indicate the organisation which best describes your service</label>
                                                                            {!! Form::select('org_type', $arrOrgTypes, null, array('placeholder' => "Organisation type")) !!}
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <input id="org_desc" type="text"
                                                                                   class="form-control"
                                                                                   name="org_desc"
                                                                                   placeholder="Organisation description">
                                                                        </div>
                                                                        <div class="form-group ">
                                                                            {!! Form::select('setting[]', $arrSettings, null, array('v-model'=>'appoSlotTime', 'placeholder' => "Setting")) !!}
                                                                        </div>
                                                                        <div class="form-group "
                                                                             v-if="appoSlotTime=='Other'">
                                                                            <input id="other_setting" type="text"
                                                                                   class="form-control"
                                                                                   name="setting[]"
                                                                                   placeholder="Other Setting">
                                                                        </div>

                                                                        <div class="form-group form-group-half">
                                                                            <input  type="text"
                                                                                   class="form-control"
                                                                                   name="pin"
                                                                                   placeholder="Pin">
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <date-picker
                                                                                    :config="{format: 'YYYY-MM-DD'}"
                                                                                    value=""
                                                                                    class="form-control"
                                                                                    name="pin_date_revalid"
                                                                                    placeholder="Pin date of revalidation"
                                                                            ></date-picker>
                                                                        </div>
                                                                        <br>

                                                                        <div class="form-group form-group">
                                                                            <label for="insurance"
                                                                                   style="display: inline-block">Insurance
                                                                                Details</label> <input
                                                                                                       type="checkbox"
                                                                                                       name="insurance"
                                                                                                       placeholder="Insurance" v-model="insurancecheckbox">
                                                                        </div>
                                                                        <div v-if="insurancecheckbox">
                                                                            <div class="form-group " >
                                                                                <input  type="text"
                                                                                       class="form-control"
                                                                                       name="org_name"
                                                                                       placeholder="Organisation name">
                                                                            </div>
                                                                            <div class="form-group ">
                                                                                <input  type="text"
                                                                                       class="form-control"
                                                                                       name="policy_number"
                                                                                       placeholder="Policy Number">
                                                                            </div>


                                                                            <div class="form-group">
                                                                                <label>Organisation Contact</label>
                                                                            </div>
                                                                            <div class="form-group form-group-half">
                                                                                <input id="org_name" type="text"
                                                                                       class="form-control"
                                                                                       name="org_name"
                                                                                       placeholder="Name">
                                                                            </div>
                                                                            <div class="form-group form-group-half">
                                                                                <input id="organisation_position" type="text"
                                                                                       class="form-control"
                                                                                       name="organisation_position"
                                                                                       placeholder="Position">
                                                                            </div>
                                                                            <div class="form-group form-group-half">
                                                                                <input id="organisation_email" type="email"
                                                                                       class="form-control"
                                                                                       name="organisation_email"
                                                                                       placeholder="Email">
                                                                            </div>
                                                                            <div class="form-group form-group-half">
                                                                                <input id="organisation_contact" type="text"
                                                                                       class="form-control"
                                                                                       name="organisation_contact"
                                                                                       placeholder="Direct Contact No">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group ">
                                                                            <strong>Professional Indemnity
                                                                                Certificate:</strong>
                                                                            <input type="file" name="prof_ind_cert"
                                                                                   class="form-control"
                                                                                   accept=".pdf, image/*,.doc,.docx">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::select('prof_required', $arrProfReq, null, array('placeholder' => "Professional Required")) !!}
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::select('special_interests[]', $arrSpecialInterests, null, array('v-model'=>'specialInterest','placeholder' => "Special Interests")) !!}
                                                                        </div>
                                                                        <div class="form-group"
                                                                             v-if="specialInterest=='Other'">
                                                                            <input  type="text"
                                                                                   class="form-control"
                                                                                   name="special_interests[]"
                                                                                   placeholder="Other Special Interest">
                                                                        </div>
                                                                        <div class="form-group ">
                                                                            <strong>Certificates –Vaccinations &
                                                                                immunisation
                                                                                (Measles/Mumps/Rubella/Hepatitis
                                                                                B/Varicella):</strong>
                                                                            <input type="file" name="certs"
                                                                                   class="form-control"
                                                                                   accept=".pdf, image/*,.doc,.docx">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::select('appo_slot_times[]', $arrAppo_slot_times, null, array('v-model'=>'appoSlotTime', 'placeholder' => "Appointment Slot Times")) !!}
                                                                        </div>
                                                                        <div class="form-group"
                                                                             v-if="appoSlotTime=='Other'">
                                                                            <input id="other_appo" type="text"
                                                                                   class="form-control"
                                                                                   name="appo_slot_times[]"
                                                                                   placeholder="Other Appointment Slot Times">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::select('adm_catch_time', array('Yes'=>'Yes', 'No'=>'No'), null, array('placeholder' => "Admin Catch Up Time Provided", 'v-model'=>'adm_catch_time')) !!}
                                                                        </div>
                                                                        <div class="form-group" v-if="adm_catch_time=='Yes'">
                                                                            {!! Form::select('time_allowed[]', $arrAppo_slot_times, null, array('placeholder' => "Time Allocated", 'v-model'=>'timeAllocated')) !!}
                                                                        </div>
                                                                        <div class="form-group"
                                                                             v-if="timeAllocated=='Other' && adm_catch_time=='Yes'">
                                                                            <input id="other_time_allo" type="text"
                                                                                   class="form-control"
                                                                                   name="time_allowed[]"
                                                                                   placeholder="Other Time Allocated">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::select('breaks', $arrBreaks, null, array('placeholder' => "Breaks")) !!}
                                                                        </div>
                                                                        <div class="form-group">
                                                                            {!! Form::select('payment_terms[]', $arrPaymentTerms, null, array('v-model'=>'paymentTerm', 'placeholder' => "Payment Terms")) !!}
                                                                        </div>
                                                                        <div class="form-group"
                                                                             v-if="paymentTerm=='Other'">
                                                                            <input id="other_payment_terms" type="text"
                                                                                   class="form-control"
                                                                                   name="payment_terms[]"
                                                                                   placeholder="Other Payment terms">
                                                                        </div>
                                                                        <div class="form-group" >
                                                                            <input id="hourly_rate" type="number"
                                                                                   class="form-control"
                                                                                   name="hourly_rate"
                                                                                   placeholder="Hourly Rate">
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <span class="wt-checkbox"
                                                                                  style="    margin-left: 15px;    margin-top: 17px;">
                                                                                <span class="wt-checkbox">
                                                                                        <input id="hourly_rate_negotiable"
                                                                                               type="checkbox"
                                                                                               name="hourly_rate_negotiable"
                                                                                               checked="">
                                                                                        <label for="hourly_rate_negotiable"><span> Hour rate negotiable?</span></label>
                                                                                </span>
                                                                            </span>
                                                                        </div>
                                                                        <div class="form-group" >
                                                                            <label for="hourly_rate_desc">Please enter additional information in the communication box if required</label>
                                                                            <input id="hourly_rate_desc" type="text"
                                                                                   class="form-control"
                                                                                   name="hourly_rate_desc"
                                                                                   placeholder="Additional info">
                                                                        </div>

                                                                        <div class="form-group">
                                                                            {!! Form::select('direct_booking', array('Direct Bookings accepted'=>'Direct Bookings accepted', 'Direct Bookings not accepted'=>'Direct Bookings not accepted'), null, array('placeholder' => "Direct Bookings")) !!}
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Session Advertised By</label>
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="session_ad_by" type="text"
                                                                                   class="form-control"
                                                                                   name="session_ad_by"
                                                                                   placeholder="Name">
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="session_ad_by_position" type="text"
                                                                                   class="form-control"
                                                                                   name="session_ad_by_position"
                                                                                   placeholder="Position">
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="session_ad_by_email" type="email"
                                                                                   class="form-control"
                                                                                   name="session_ad_by_email"
                                                                                   placeholder="Email">
                                                                        </div>
                                                                        <div class="form-group form-group-half">
                                                                            <input id="session_ad_by_contact" type="text"
                                                                                   class="form-control"
                                                                                   name="session_ad_by_contact"
                                                                                   placeholder="Direct Contact No">
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            @endif
                                                        @endif
                                                        @if(in_array($role['id']==3, $roles))
                                                            <div class="wt-accordiondetails collapse hide"
                                                                 id="collapseOne" aria-labelledby="headingOne"
                                                                 v-if="is_show_freelancer">

                                                                <div class="form-group">
                                                                            {!! Form::select('title', array("Mr"=>"Mr", "Ms"=>"Ms", "Mrs"=>"Mrs", "Dr"=>"Dr"), null, array('placeholder' => trans('lang.title'), 'v-bind:class' => '{ "is-invalid": form_step2.title_error }')) !!}
                                                                            <span class="help-block"
                                                                                  v-if="form_step2.title_error">
                                                                                <strong v-cloak>@{{form_step2.title_error}}</strong>
                                                                            </span>
                                                                </div>

                                                                <div class="form-group form-group-half">
                                                                    <input id="telno" class="form-control"
                                                                           name="telno" type="tel"
                                                                           placeholder="{{{ trans('lang.telno') }}}"
                                                                           v-bind:class="{ 'is-invalid': form_step2.telno_error }">
                                                                    <span class="help-block"
                                                                          v-if="form_step2.telno_error">
                                                                        <strong v-cloak>@{{form_step2.telno_error}}</strong>
                                                                    </span>
                                                                </div>
                                                                <div class="form-group form-group-half">
                                                                    <date-picker :config="{format: 'DD/MM/YYYY'}"
                                                                                 value=""
                                                                                 id="dob" class="form-control"
                                                                                 name="dob"
                                                                                 placeholder="{{{ trans('lang.dob') }}}"
                                                                                 v-bind:class="{ 'is-invalid': form_step2.dob_error }"></date-picker>
                                                                    <span class="help-block"
                                                                          v-if="form_step2.dob_error">
                                                                        <strong v-cloak>@{{form_step2.dob_error}}</strong>
                                                                    </span>
                                                                </div>
                                                                <div class="form-group form-group-half">
                                                                    <date-picker :config="{format: 'DD/MM/YYYY'}"
                                                                                 id="date_available"
                                                                                 value=""

                                                                                 class="form-control"
                                                                                 name="date_available"
                                                                                 placeholder="{{{ trans('lang.date_available') }}}"
                                                                                 v-bind:class="{ 'is-invalid': form_step2.date_available_error }"></date-picker>
                                                                    <span class="help-block"
                                                                          v-if="form_step2.date_available_error">
                                                                        <strong v-cloak>@{{form_step2.date_available_error}}</strong>
                                                                    </span>
                                                                </div>
                                                                <div class="form-group">
                                                                    <strong>CV Upload:</strong>
                                                                    <input type="file" name="cvfile"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>

                                                                <div class="form-group form-group-half">
                                                                    {!! Form::select('rate', array("p/h"=>"p/h", "p/m"=>"p/m", "p/a"=>"p/a"), null, array('placeholder' => 'Rate', 'v-bind:class' => '{ "is-invalid": form_step2.rate_error }')) !!}
                                                                    <span class="help-block"
                                                                          v-if="form_step2.rate_error">
                                                                                    <strong v-cloak>@{{form_step2.rate_error}}</strong>
                                                                                </span>
                                                                </div>
                                                                <div class="form-group" >
                                                                    <input id="hourly_rate" type="number"
                                                                           class="form-control"
                                                                           name="hourly_rate"
                                                                           placeholder="Hourly Rate">
                                                                </div>
                                                                <div class="form-group form-group-half">
                                                                            <span class="wt-checkbox"
                                                                                  style="    margin-left: 15px;    margin-top: 17px;">
                                                                                <span class="wt-checkbox">
                                                                                        <input id="hourly_rate_negotiable"
                                                                                               type="checkbox"
                                                                                               name="hourly_rate_negotiable"
                                                                                               checked="">
                                                                                        <label for="hourly_rate_negotiable"><span> Hour rate negotiable?</span></label>
                                                                                </span>
                                                                            </span>
                                                                </div>
                                                                <div class="form-group">
                                                                    <input id="exp_years" type="tel"
                                                                           class="form-control"
                                                                           name="exp_years"
                                                                           placeholder="Experience Years"
                                                                           v-bind:class="{ 'is-invalid': form_step2.exp_years_error }">
                                                                    <span class="help-block"
                                                                          v-if="form_step2.exp_years_error">
                                                                                <strong v-cloak>@{{form_step2.exp_years_error}}</strong>
                                                                            </span>
                                                                </div>
                                                                <div class="form-group">
                                                                    {!! Form::select('gender', array("Male"=>"Male", "Female"=>"Female"), null, array('placeholder' => 'Gender', 'v-bind:class' => '{ "is-invalid": form_step2.gender_error }')) !!}

                                                                </div>
                                                                <div class="form-group">
                                                                    {!! Form::select('nationality', $arrNationals, null, array('placeholder' => "Nationality")) !!}
                                                                </div>
                                                                <div class="form-group form-group-half">
                                                                    <input  type="text"
                                                                           class="form-control"
                                                                           name="pin"
                                                                           placeholder="Pin">
                                                                </div>
                                                                <div class="form-group form-group-half">
                                                                    <date-picker :config="{format: 'YYYY-MM-DD'}"

                                                                                 value=""

                                                                                 class="form-control"
                                                                                 name="pin_date_revalid"
                                                                                 placeholder="Pin date of revalidation"
                                                                    ></date-picker>
                                                                </div>
                                                                <div class="form-group">
                                                                    {!! Form::select('profession', $arrProfReq, null, array('placeholder' => "Profession")) !!}
                                                                </div>
                                                                <div class="form-group">
                                                                    {!! Form::select('right_of_work',  array('Yes'=>'Yes', 'No'=>'No'), null, array('placeholder' => "Right to work")) !!}
                                                                </div>
                                                                <div class="form-group ">
                                                                    <strong>Passport or Visa:</strong>
                                                                    <input type="file" name="passport_visa"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>
                                                                <div class="form-group form-group">
                                                                    <strong>Professional Qualifications</strong>
                                                                    <span class="text-right" id="plusQual" style="cursor:pointer;font-size: 16px; background-color: #fccf17;color:white;padding:7px;border-radius:5px">+</span>
                                                                </div>

                                                                <div class="profQualif_block">
                                                                    <table border="1">
                                                                        <tr>
                                                                            <td><input type="text"
                                                                                       class="form-control"
                                                                                       name="profQualLevel[]"
                                                                                       placeholder="Level"></td>
                                                                            <td> <input type="text"
                                                                                        class="form-control"
                                                                                        name="profQualName[]"
                                                                                        placeholder="Name"></td>
                                                                            <td><input type="text"
                                                                                       class="form-control"
                                                                                       name="profQualPlace[]"
                                                                                       placeholder="Place of Study"></td>
                                                                            <td><input type="number"
                                                                                       class="form-control"
                                                                                       name="profQualYear[]"
                                                                                       placeholder="Year"></td>
                                                                        </tr>
                                                                    </table>

                                                                </div>

                                                                <div class="form-group ">
                                                                    <strong>Professional Qualifications Certificate</strong>
                                                                    <input type="file" name="prof_qual_cert"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>
                                                                <div class="form-group ">
                                                                    <strong>Mandatory Training:</strong>
                                                                    <input type="file" name="mand_training"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="wt-checkboxholder">
                                                                        <span class="wt-checkbox">
                                                                            <input id="dbscheck" type="checkbox"
                                                                                   name="dbscheck"
                                                                                   checked=""
                                                                                   v-model="dbscheck">
                                                                            <label for="dbscheck"><span>DBS checked</span></label>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group " v-if="dbscheck">
                                                                    <strong>Certificate of CRB/DBS:</strong>
                                                                    <input type="file" name="cert_of_crbdbs"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>
                                                                <div class="form-group ">
                                                                    <strong>Occupational Health:</strong>
                                                                    <input type="file" name="occup_health"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>
                                                                <div class="form-group">
                                                                    {!! Form::select('special_interests[]', $arrSpecialInterests, null, array('v-model'=>'specialInterest','placeholder' => "Special Interests")) !!}
                                                                </div>
                                                                <div class="form-group" v-if="specialInterest=='Other'">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="special_interests[]"
                                                                           placeholder="Other Special Interest">
                                                                </div>

                                                                <div class="form-group form-group">
                                                                    <label for="insurance"
                                                                           style="display: inline-block">Professional
                                                                        Indemnity Insurance</label> <input
                                                                            type="checkbox"
                                                                            name="insurance"
                                                                            placeholder="Insurance"
                                                                            v-model="insurancecheckbox">
                                                                </div>

                                                                <div v-if="insurancecheckbox">
                                                                    <div class="form-group ">
                                                                        <input type="text"
                                                                               class="form-control"
                                                                               name="org_name"
                                                                               placeholder="Organisation name">
                                                                    </div>
                                                                    <div class="form-group ">
                                                                        <input  type="text"
                                                                               class="form-control"
                                                                               name="policy_number"
                                                                               placeholder="Insurance Policy Number">
                                                                    </div>
                                                                </div>



                                                                <div class="form-group">
                                                                    <div class="wt-checkboxholder">
                                                                        <span class="wt-checkbox">
                                                                            <input id="dbscheck" type="checkbox"
                                                                                   name="dbscheck"
                                                                                   checked=""
                                                                                   v-model="dbscheck">
                                                                            <label for="dbscheck"><span>DBS checked</span></label>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group " v-if="dbscheck">
                                                                    <strong>Certificate of CRB/DBS:</strong>
                                                                    <input type="file" name="cert_of_crbdbs"
                                                                           class="form-control"
                                                                           accept=".pdf, image/*,.doc,.docx">
                                                                </div>

                                                                <div class="form-group">
                                                                    {!! Form::select('direct_booking', array('Direct Bookings accepted'=>'Direct Bookings accepted', 'Direct Bookings not accepted'=>'Direct Bookings not accepted'), null, array('placeholder' => "Direct Bookings")) !!}
                                                                </div>
                                                                <div class="form-group">
                                                                    {!! Form::select('c_payment_methods',$arrPaymentMethods, null, array('placeholder' => "Payment Method", 'v-model'=>'payment_method')) !!}
                                                                    <span v-if="payment_method=='Self Employed'">Please invoice the employer directly for payment</span>
                                                                </div>


                                                                <input v-if="payment_method=='Limited Company'" type="text"
                                                                       name="limitied_company_number"
                                                                       class="form-control" placeholder="Limited Company Number "><br>
                                                                <input v-if="payment_method=='Limited Company'" type="text"
                                                                       name="limitied_company_name"
                                                                       class="form-control" placeholder="Limited Company Name ">
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            @endif
                                        </fieldset>
                                        <fieldset class="wt-termsconditions">
                                            <div class="wt-checkboxholder">
                                            <span class="wt-checkbox">
                                                <input id="termsconditions" type="checkbox" name="termsconditions"
                                                       checked="">
                                                <label for="termsconditions"><span>Agree to T&Cs and Privacy</span></label>
                                                <span class="help-block" v-if="form_step2.termsconditions_error">
                                                    <strong style="color: red;"
                                                            v-cloak>{{trans('lang.register_termsconditions_error')}}</strong>
                                                </span>
                                            </span>
                                                <a href="#" @click.prevent="prev()"
                                                   class="wt-btn">{{{ trans('lang.previous') }}}</a>
                                                <a href="#"
                                                   @click.prevent="checkStep2('{{ trans('lang.email_not_config') }}')"
                                                   class="wt-btn">{{{ trans('lang.continue') }}}</a>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="wt-joinformc" v-show="step === 3" v-cloak>

                                        <div class="wt-registerhead">
                                            <div class="wt-title">
                                                <h3>{{{ $reg_three_title }}}</h3>
                                            </div>
                                            {{--<div class="wt-description">--}}
                                            {{--<p>{{{ $reg_three_subtitle }}}</p>--}}
                                            {{--</div>--}}
                                        </div>
                                        <ul class="wt-joinsteps">
                                            <li class="wt-done-next"><a href="javascrip:void(0);"><i
                                                            class="fa fa-check"></i></a></li>
                                            <li class="wt-done-next"><a href="javascrip:void(0);"><i
                                                            class="fa fa-check"></i></a></li>
                                            <li class="wt-active"><a
                                                        href="javascrip:void(0);">{{{ trans('lang.03') }}}</a></li>
                                            <li><a href="javascrip:void(0);">{{{ trans('lang.04') }}}</a></li>
                                        </ul>
                                        <figure class="wt-joinformsimg">
                                            {{--<img src="{{ asset($register_image)}}" alt="{{{ trans('lang.verification_code_img') }}}">--}}
                                        </figure>
                                        <fieldset>
                                            <div class="form-group">
                                                {{--<label>--}}
                                                {{--{{{ trans('lang.verify_code_note') }}}--}}
                                                {{--@if (!empty($reg_page))--}}
                                                {{--<a target="_blank" href="{{{url($reg_page)}}}">--}}
                                                {{--{{{ trans('lang.why_need_code') }}}--}}
                                                {{--</a>--}}
                                                {{--@else--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                {{--{{{ trans('lang.why_need_code') }}}--}}
                                                {{--</a>--}}
                                                {{--@endif--}}
                                                {{--</label>--}}
                                                {{--<input type="text" name="code" class="form-control" placeholder="{{{ trans('lang.enter_code') }}}">--}}
                                                <div v-if="user_role=='freelancer'">
                                                    {!! Form::select('payment_option', $payment_options, null, array('placeholder' => "Select Payment Option", 'v-model'=>'choosen_payment' ,'class' => 'form-group', 'v-bind:class' => '{ "is-invalid": form_step2.payment_option_error }', 'v-on:change' => 'selectedPayment(choosen_payment)')) !!}
                                                    <div class="form-group" v-if="P60upload">
                                                        <strong>P60 Upload:</strong>
                                                        <input type="file" name="p60" class="form-control"
                                                               accept=".pdf, image/*,.doc,.docx">
                                                    </div>
                                                    <div class="form-group" v-if="paypal_show">
                                                        <strong>Paypal Account:</strong>
                                                        <input type="email" name="paypal" class="form-control"
                                                               placeholder="Paypal email address"/>
                                                    </div>
                                                    <div class="form-group" v-if="cheque_show">
                                                        <strong>Cheque:</strong>
                                                        <input type="text" name="cheque" class="form-control"
                                                               placeholder="Your current address details will be used"/>
                                                    </div>


                                                </div>
                                                <div v-if="user_role=='employer'">
                                                    {!! Form::select('plan_id', $subscribe_options, null, array('placeholder' => "Select subscription ", 'v-model'=>'subscription' ,'class' => 'form-group', 'v-bind:class' => '{ "is-invalid": form_step2.payment_option_error }', 'v-on:change' => 'selectedSubscription(subscription)')) !!}
                                                </div>

                                            </div>
                                            <div class="form-group wt-btnarea">
                                                <a href="#" @click.prevent="checkStep3()"
                                                   class="wt-btn">{{{ trans('lang.continue') }}}</a>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="wt-gotodashboard" v-show="step === 4" v-cloak>
                                        <ul class="wt-joinsteps">
                                            <li class="wt-done-next"><a href="javascrip:void(0);"><i
                                                            class="fa fa-check"></i></a></li>
                                            <li class="wt-done-next"><a href="javascrip:void(0);"><i
                                                            class="fa fa-check"></i></a></li>
                                            <li class="wt-done-next"><a
                                                        href="javascrip:void(0);">{{{ trans('lang.03') }}}</a></li>
                                            <li class="wt-active"><a
                                                        href="javascrip:void(0);">{{{ trans('lang.04') }}}</a></li>
                                        </ul>
                                        <div class="wt-registerhead">
                                            <div class="wt-title">
                                                <h3>Last step</h3>
                                            </div>
                                            {{--<div class="description">--}}
                                            {{--<p>{{{ $reg_four_subtitle }}}</p>--}}
                                            {{--</div>--}}
                                        </div>

                                        <a href="#" class="wt-btn" @click.prevent="checkoutStripe(subscription)"
                                           v-if="subscription">Go To Checkout</a>
                                    </div>
                                </div>
                            </div>
                            <div class="wt-registerformfooter">
                            <span>{{{ trans('lang.have_account') }}}<a id="wt-lg" href="javascript:void(0);"
                                                                       @click.prevent='scrollTop()'>{{{ trans('lang.btn_login_now') }}}</a></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection
