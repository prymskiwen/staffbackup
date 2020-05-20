<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailTypesNewJobNotificationEmplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE email_types MODIFY COLUMN email_type ENUM('new_user',
          'verification_code','lost_password','account_verification',
          'invitation','contact_form_received','admin_email_registration',
          'admin_email_delete_account','admin_email_report_employer',
          'admin_email_report_project','admin_email_report_freelancer',
          'admin_email_new_job_posted','admin_email_job_completed',
          'employer_email_proposal_received','employer_email_new_job_posted',
          'employer_email_proposal_message','employer_email_package_subscribed',
          'freelancer_email_new_proposal_submitted','freelancer_email_hire_freelancer',
          'freelancer_email_send_offer','freelancer_email_cancel_job',
          'freelancer_email_proposal_message','freelancer_email_package_subscribed',
          'freelancer_email_job_completed','freelancer_email_new_job_posted',
          'support_email_new_job_posted','admin_email_dispute_raised',
          'reset_password_email','admin_email_cancel_job')");
    }
}