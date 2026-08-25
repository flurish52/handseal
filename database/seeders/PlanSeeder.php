<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Start creating professional certificates at no cost. Get your first 3 certificates free and explore HandSeal before choosing a paid plan.',
                'price' => 0,
                'included_certs' => 3,
                'extra_cert_price' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'A simple starting point for businesses creating certificates occasionally. Get the tools you need to issue professional certificates without committing to a large monthly plan.',
                'price' => 100000,
                'included_certs' => 10,
                'extra_cert_price' => 15000,
                'sort_order' => 2,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'Built for businesses that issue certificates regularly. Get more certificates at a lower cost per certificate and keep your certificate workflow running without frequent plan changes.',
                'price' => 250000,
                'included_certs' => 30,
                'extra_cert_price' => 10000,
                'sort_order' => 3,
            ],
            [
                'name' => 'Unlimited',
                'slug' => 'unlimited',
                'description' => 'For businesses with a high certificate volume. Create as many certificates as your business needs each month without worrying about certificate limits or extra certificate charges.',
                'price' => 500000,
                'included_certs' => null,
                'extra_cert_price' => null,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
