<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@mealsonwheels.test'],
            [
                'firstname' => 'Rahul',
                'lastname' => 'Gotrekiya',
                'password' => 'password',
                'role' => UserRole::Admin,
                'status' => UserStatus::Active,
                'phone' => '9876543210',
                'photo' => 'users/admin.jpg',
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@mealsonwheels.test'],
            [
                'firstname' => 'Sachin',
                'lastname' => 'Tholiya',
                'password' => 'password',
                'role' => UserRole::Customer,
                'status' => UserStatus::Active,
                'phone' => '8320959002',
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'street' => '12 Signature Tower, Sarkhej Road',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'zip_code' => '380060',
            ]
        );

        // Three suppliers, so a single basket can span more than one merchant
        // and each merchant's view can be shown to contain only its own lines.
        $merchants = [
            [
                'email' => 'pawsome@mealsonwheels.test',
                'firstname' => 'Pawsome',
                'lastname' => 'Supplies',
                'phone' => '9820011223',
                'status' => UserStatus::Active,
            ],
            [
                'email' => 'whiskers@mealsonwheels.test',
                'firstname' => 'Whiskers',
                'lastname' => 'Wholesale',
                'phone' => '9820044556',
                'status' => UserStatus::Active,
            ],
            [
                'email' => 'featherfur@mealsonwheels.test',
                'firstname' => 'Feather & Fur',
                'lastname' => 'Traders',
                'phone' => '9820077889',
                'status' => UserStatus::Active,
            ],
            // Left pending on purpose, so the approval queue is never empty.
            [
                'email' => 'newsupplier@mealsonwheels.test',
                'firstname' => 'Barkwell',
                'lastname' => 'Foods',
                'phone' => '9820099001',
                'status' => UserStatus::Pending,
            ],
        ];

        foreach ($merchants as $merchant) {
            User::updateOrCreate(
                ['email' => $merchant['email']],
                [
                    'firstname' => $merchant['firstname'],
                    'lastname' => $merchant['lastname'],
                    'password' => 'password',
                    'role' => UserRole::Merchant,
                    'status' => $merchant['status'],
                    'phone' => $merchant['phone'],
                ]
            );
        }

        $this->command->info("  admin     {$admin->email} / password");
        $this->command->info("  customer  {$customer->email} / password");
        $this->command->info('  merchants pawsome@ whiskers@ featherfur@ / password');
    }
}
