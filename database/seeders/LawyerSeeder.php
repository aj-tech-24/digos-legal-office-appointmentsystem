<?php

namespace Database\Seeders;

use App\Models\Lawyer;
use App\Models\LawyerSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LawyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lawyers = [
            [
                'user' => [
                    'name' => 'Atty. Maria Santos',
                    'email' => 'maria.santos@digoscity.gov.ph',
                    'password' => Hash::make('password'),
                ],
                'lawyer' => [
                    'license_number' => 'IBP-2010-12345',
                    'bio' => 'Experienced civil and family law practitioner with over 12 years of experience.',
                    'years_of_experience' => 12,
                    'languages' => ['Filipino', 'English', 'Bisaya'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'max_daily_appointments' => 8,
                    'default_consultation_duration' => 60,
                ],
                'specializations' => ['Civil Law', 'Family Law'],
            ],
            [
                'user' => [
                    'name' => 'Atty. Juan dela Cruz',
                    'email' => 'juan.delacruz@digoscity.gov.ph',
                    'password' => Hash::make('password'),
                ],
                'lawyer' => [
                    'license_number' => 'IBP-2008-67890',
                    'bio' => 'Criminal law specialist with extensive trial experience.',
                    'years_of_experience' => 15,
                    'languages' => ['Filipino', 'English'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'max_daily_appointments' => 6,
                    'default_consultation_duration' => 60,
                ],
                'specializations' => ['Criminal Law', 'Civil Law'],
            ],
            [
                'user' => [
                    'name' => 'Atty. Rosa Reyes',
                    'email' => 'rosa.reyes@digoscity.gov.ph',
                    'password' => Hash::make('password'),
                ],
                'lawyer' => [
                    'license_number' => 'IBP-2015-11111',
                    'bio' => 'Labor law expert focused on workers rights and employment disputes.',
                    'years_of_experience' => 8,
                    'languages' => ['Filipino', 'English', 'Bisaya'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'max_daily_appointments' => 8,
                    'default_consultation_duration' => 45,
                ],
                'specializations' => ['Labor Law', 'Administrative Law'],
            ],
            [
                'user' => [
                    'name' => 'Atty. Pedro Garcia',
                    'email' => 'pedro.garcia@digoscity.gov.ph',
                    'password' => Hash::make('password'),
                ],
                'lawyer' => [
                    'license_number' => 'IBP-2012-22222',
                    'bio' => 'Property law specialist with expertise in land disputes and titles.',
                    'years_of_experience' => 10,
                    'languages' => ['Filipino', 'English'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'max_daily_appointments' => 7,
                    'default_consultation_duration' => 60,
                ],
                'specializations' => ['Property Law', 'Civil Law'],
            ],
        ];

        foreach ($lawyers as $data) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $data['user']['email']],
                $data['user']
            );

            // Create lawyer profile
            $lawyer = Lawyer::updateOrCreate(
                ['user_id' => $user->id],
                $data['lawyer']
            );

            // Attach specializations
            $specializationIds = \App\Models\Specialization::whereIn('name', $data['specializations'])
                ->pluck('id')
                ->toArray();
            
            $syncData = [];
            foreach ($specializationIds as $index => $id) {
                $syncData[$id] = ['is_primary' => $index === 0];
            }
            $lawyer->specializations()->sync($syncData);

            // Create default schedule (Monday to Friday, 8 AM to 5 PM)
            for ($day = 1; $day <= 5; $day++) {
                LawyerSchedule::updateOrCreate(
                    ['lawyer_id' => $lawyer->id, 'day_of_week' => $day],
                    [
                        'start_time' => '08:00:00',
                        'end_time' => '17:00:00',
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
