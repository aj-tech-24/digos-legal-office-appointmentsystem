<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json, textarea
            $table->string('label');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $settings = [
            // General / Office Info
            ['group' => 'general', 'key' => 'office_name', 'value' => 'Digos City Legal Office', 'type' => 'text', 'label' => 'Office Name', 'description' => 'Name of the legal office displayed throughout the system'],
            ['group' => 'general', 'key' => 'office_address', 'value' => 'City Hall, Digos City, Davao del Sur', 'type' => 'text', 'label' => 'Office Address', 'description' => 'Physical address of the office'],
            ['group' => 'general', 'key' => 'office_phone', 'value' => '', 'type' => 'text', 'label' => 'Office Phone', 'description' => 'Contact phone number'],
            ['group' => 'general', 'key' => 'office_email', 'value' => '', 'type' => 'text', 'label' => 'Office Email', 'description' => 'Contact email address'],

            // Appointment Settings
            ['group' => 'appointments', 'key' => 'default_duration', 'value' => '60', 'type' => 'number', 'label' => 'Default Duration (minutes)', 'description' => 'Default appointment duration in minutes'],
            ['group' => 'appointments', 'key' => 'max_appointments_per_day', 'value' => '20', 'type' => 'number', 'label' => 'Max Appointments Per Day', 'description' => 'Maximum number of appointments allowed per day'],
            ['group' => 'appointments', 'key' => 'advance_booking_days', 'value' => '30', 'type' => 'number', 'label' => 'Advance Booking Days', 'description' => 'How many days in advance clients can book appointments'],
            ['group' => 'appointments', 'key' => 'min_booking_notice_hours', 'value' => '24', 'type' => 'number', 'label' => 'Minimum Booking Notice (hours)', 'description' => 'Minimum hours before an appointment can be booked'],
            ['group' => 'appointments', 'key' => 'allow_same_day_booking', 'value' => '0', 'type' => 'boolean', 'label' => 'Allow Same-Day Booking', 'description' => 'Allow clients to book appointments for the same day'],
            ['group' => 'appointments', 'key' => 'auto_confirm_appointments', 'value' => '0', 'type' => 'boolean', 'label' => 'Auto-Confirm Appointments', 'description' => 'Automatically confirm new appointments without staff review'],

            // Operating Hours
            ['group' => 'hours', 'key' => 'operating_days', 'value' => 'Monday,Tuesday,Wednesday,Thursday,Friday', 'type' => 'text', 'label' => 'Operating Days', 'description' => 'Comma-separated list of operating days'],
            ['group' => 'hours', 'key' => 'opening_time', 'value' => '08:00', 'type' => 'text', 'label' => 'Opening Time', 'description' => 'Office opening time (24-hour format)'],
            ['group' => 'hours', 'key' => 'closing_time', 'value' => '17:00', 'type' => 'text', 'label' => 'Closing Time', 'description' => 'Office closing time (24-hour format)'],
            ['group' => 'hours', 'key' => 'lunch_start', 'value' => '12:00', 'type' => 'text', 'label' => 'Lunch Break Start', 'description' => 'Lunch break start time'],
            ['group' => 'hours', 'key' => 'lunch_end', 'value' => '13:00', 'type' => 'text', 'label' => 'Lunch Break End', 'description' => 'Lunch break end time'],

            // Notification Settings
            ['group' => 'notifications', 'key' => 'email_notifications_enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'Enable Email Notifications', 'description' => 'Send email notifications to clients for appointment updates'],
            ['group' => 'notifications', 'key' => 'send_booking_confirmation', 'value' => '1', 'type' => 'boolean', 'label' => 'Booking Confirmation Email', 'description' => 'Send confirmation email when appointment is booked'],
            ['group' => 'notifications', 'key' => 'send_appointment_reminder', 'value' => '1', 'type' => 'boolean', 'label' => 'Appointment Reminder Email', 'description' => 'Send reminder email before the appointment'],
            ['group' => 'notifications', 'key' => 'reminder_hours_before', 'value' => '24', 'type' => 'number', 'label' => 'Reminder Hours Before', 'description' => 'Hours before appointment to send reminder'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
