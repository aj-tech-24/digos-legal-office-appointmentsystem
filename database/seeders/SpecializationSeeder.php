<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specializations = [
            [
                'name' => 'Civil Law',
                'slug' => 'civil-law',
                'description' => 'Deals with disputes between individuals or organizations, including contracts, property, and torts.',
            ],
            [
                'name' => 'Criminal Law',
                'slug' => 'criminal-law',
                'description' => 'Handles cases involving crimes and offenses against the state or public.',
            ],
            [
                'name' => 'Family Law',
                'slug' => 'family-law',
                'description' => 'Covers matters related to family relationships such as marriage, divorce, child custody, and adoption.',
            ],
            [
                'name' => 'Labor Law',
                'slug' => 'labor-law',
                'description' => 'Addresses employment-related issues including worker rights, benefits, and disputes.',
            ],
            [
                'name' => 'Property Law',
                'slug' => 'property-law',
                'description' => 'Deals with real estate matters, land titles, property disputes, and ownership transfers.',
            ],
            [
                'name' => 'Administrative Law',
                'slug' => 'administrative-law',
                'description' => 'Governs the activities of government agencies and administrative bodies.',
            ],
            [
                'name' => 'Tax Law',
                'slug' => 'tax-law',
                'description' => 'Covers taxation matters including compliance, disputes, and tax planning.',
            ],
            [
                'name' => 'Environmental Law',
                'slug' => 'environmental-law',
                'description' => 'Addresses environmental protection, natural resources, and environmental compliance.',
            ],
            [
                'name' => 'Notarial Services',
                'slug' => 'notarial-services',
                'description' => 'Document notarization and authentication services.',
            ],
            [
                'name' => 'General Consultation',
                'slug' => 'general-consultation',
                'description' => 'General legal advice and consultation on various matters.',
            ],
        ];

        foreach ($specializations as $spec) {
            Specialization::updateOrCreate(
                ['slug' => $spec['slug']],
                $spec
            );
        }
    }
}
