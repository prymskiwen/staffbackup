@php
        $user = Auth::user();
        $extra_professions = $user->professions()->get()->toArray();
@endphp
<div class="wt-tabscontenttitle">
    <h2>{{{ trans('lang.your_details') }}}</h2>
</div>
<div class="wt-formtheme">
    <fieldset>

        <div class="form-group form-group-20">
            {!! Form::select('title', ["Mr"=>"Mr", "Ms"=>"Ms", "Mrs"=>"Mrs", "Dr"=>"Dr"], $user->title , ['class' =>'form-control', 'placeholder' => trans('lang.title')]) !!}
        </div>

        <div class="form-group form-group-40">
            {!! Form::text( 'first_name', $user->first_name, ['class' =>'form-control', 'placeholder' => trans('lang.ph_first_name')] ) !!}
        </div>

        <div class="form-group form-group-40">
            {!! Form::text( 'last_name', $user->last_name, ['class' =>'form-control', 'placeholder' => trans('lang.ph_last_name')] ) !!}
        </div>

        <div class="form-group form-group-half">
            {!! Form::text( 'email', $user->email, ['class' =>'form-control', 'placeholder' => trans('lang.ph_email')] ) !!}
        </div>

        <div class="form-group form-group-half">
            {!! Form::text( 'telno', $user->telno, ['class' =>'form-control', 'placeholder' => trans('lang.phone')] ) !!}
        </div>
        @php
            $user_dob = is_string($user->dob) ? date('d-m-Y',  strtotime($user->dob)) : "";
        @endphp
        <div class="form-group birthday">
            <date-picker
                :config="{format: 'DD-MM-YYYY'}"
                value="{{ $user_dob }}"
                class="form-control"
                name="dob"
                placeholder="Date of birth"
            ></date-picker>
        </div>

        {{--<div class="form-group form-group-half">--}}
            {{--<span class="wt-select">--}}
                {{--{!! Form::select( 'gender', ['male' => 'Male', 'female' => 'Female'], e($gender), ['placeholder' => trans('lang.ph_select_gender')] ) !!}--}}
            {{--</span>--}}
        {{--</div>--}}
        @php
            $professions = \App\User::getProfessionsByRole(App\Role::FREELANCER_ROLE);
        @endphp

        <div class="form-group">
            <span class="wt-select">
            <!-- {!! Form::select('profession_id', \App\User::getProfessionsByRole(App\Role::FREELANCER_ROLE), $user->profession_id, array("class"=>"form-control", 'placeholder' => "Profession", "@change" => 'onProfessionChange')) !!} -->
                <select name="profession_id" class="form-control" @change="onProfessionChange">
                    <option selected disabled>Profession</option>
                    @foreach($professions as $id=>$title)
                    <option value="{{$id}}" @if($id==$user->profession_id) selected @endif>{{strtoupper($title)}}</option>
                    @endforeach
                </select>
            </span>
        </div>
        <div class="form-group ">
            <multiselect 
                v-model="extra_professions" 
                :options="extra_professions_options" 
                :searchable="false" 
                :close-on-select="false" 
                :clear-on-select="false" 
                :preserve-search="false" 
                :show-labels="false" 
                :multiple="true" 
                label="title"
                track-by="id"
                placeholder="Other professions" 
                name="extra_professions" 
                class="multiselect-upd" 
                ref="extra_professions" 
                data-value="{{ json_encode(count($extra_professions) != 0 ? $extra_professions : []) }}"
            >
                <template slot="selection" slot-scope="{ values, search, isOpen }"><span class="multiselect__single" v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} option@{{ values.length != 1 ? 's' : '' }} selected</span></template>
            </multiselect>
            <select name="extra_professions[]" style="display:none;" multiple>
                <option v-for="value in extra_professions" :value="value.id" selected></option>
            </select>
        </div>
        <div class="form-group form-group-half">
            <input  type="text"
                    class="form-control"
                    name="pin"
                    value="{{$user->pin}}"

                    placeholder="Pin">
        </div>
        <div class="form-group form-group-half">
            <date-picker :config="{format: 'DD-MM-YYYY'}"

                         value="{{date('d-m-Y', strtotime($user->pin_date_revalid))}}"

                         class="form-control"
                         name="pin_date_revalid"
                         placeholder="Pin date of revalidation"
            ></date-picker>
        </div>
        <div class="form-group form-group-half">
            <div class="left-inner-addon">
                <span>??</span>
                {!! Form::text( 'hourly_rate', e($hourly_rate), ['class' =>'form-control', 'placeholder' => trans('lang.ph_service_hoyrly_rate')] ) !!}
            </div>
        </div>



        <div class="form-group form-group-half">
        <span class="wt-checkbox"
              style="    margin-left: 15px;    margin-top: 17px;">
            <span class="wt-checkbox">
                    <input id="hourly_rate_negotiable"
                           type="checkbox"
                           name="hourly_rate_negotiable"
                            {{$hourly_rate_negotiable=='on'? 'checked' : ''}}

                    >
                    <label for="hourly_rate_negotiable"><span> Hourly rate negotiable</span></label>
            </span>
        </span>
        </div>
        <div class="form-group">
            {!! Form::text( 'tagline', e(html_entity_decode($tagline, ENT_QUOTES)), ['class' =>'form-control', 'placeholder' => trans('lang.ph_add_tagline')] ) !!}
        </div>
        <div class="form-group">
            {!! Form::textarea( 'description', e(html_entity_decode($description, ENT_QUOTES)), ['class' =>'form-control', 'placeholder' => trans('lang.ph_desc')] ) !!}
        </div>


    </fieldset>
</div>


{{--<div class="wt-tabscontenttitle" style="margin-top: 20px;">--}}
    {{--<h2>Avilable days and hours</h2>--}}
{{--</div>--}}
{{--<div class="wt-formtheme">--}}
    {{--<div class="form-group form-group-half">--}}
        {{--<select id="multiselect" class="form-control" name="days_avail[]" data-dbValue="{{$days_avail}}" multiple="multiple">--}}
            {{--<option>Monday</option>--}}
            {{--<option>Tuesday</option>--}}
            {{--<option>Wednesday</option>--}}
            {{--<option>Thursday</option>--}}
            {{--<option>Friday</option>--}}
            {{--<option>Saturday</option>--}}
            {{--<option>Sunday</option>--}}
        {{--</select>--}}
    {{--</div>--}}
    {{--<div class="form-group form-group-half">--}}
        {{--<div id="datetimepickerDate" class="input-group timerange">--}}
                    {{--<input class="form-control" name="hours_avail" type="text" value="{{$hours_avail}}" autocomplete="off">--}}
                    {{--<span class="input-group-addon" style="">--}}
          {{--</span>--}}
        {{--</div>--}}

    {{--</div>--}}


{{--</div>--}}
<div class="wt-tabscontenttitle" style="margin-top: 20px;">
    <h2>Computer System in use</h2>
</div>

<div class="wt-formtheme">
    <div class="form-group ">
        <multiselect v-model="itsoftware" :options="itsoftware_options" :searchable="false" :close-on-select="false" :clear-on-select="false" :preserve-search="false" :show-labels="false" :multiple="true" placeholder="Computer Systems" name="itsoftware" class="multiselect-upd" ref="input" data-value="{{ json_encode($user->itsoftware != null ? unserialize($user->itsoftware) : []) }}">
            <template slot="selection" slot-scope="{ values, search, isOpen }"><span class="multiselect__single" v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} option@{{ values.length != 1 ? 's' : '' }} selected</span></template>
        </multiselect>
        <select name="itsoftware[]" style="display:none;" multiple>
            <option v-for="value in itsoftware" :value="value" selected></option>
        </select>
    </div>
</div>


<div class="wt-tabscontenttitle" style="margin-top: 20px;">
    <h2>Limted Company Number</h2>
</div>
<div class="wt-formtheme">
    <div class="form-group">
        {!! Form::text( 'limitied_company_number', Auth::user()->limitied_company_number, ['class' =>'form-control', 'placeholder' =>"Limited Company Number"] ) !!}
    </div>
</div>
<div class="wt-tabscontenttitle" style="margin-top: 20px;">
    <h2>Limted Company Name</h2>
</div>
<div class="wt-formtheme">
    <div class="form-group">
        {!! Form::text( 'limitied_company_name', Auth::user()->limitied_company_name, ['class' =>'form-control', 'placeholder' => "Limited Company Name" ] ) !!}
    </div>
</div>
