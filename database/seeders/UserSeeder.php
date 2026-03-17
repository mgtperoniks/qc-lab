<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'QA Lab Admin',
                'email' => 'adminlab@peroniks.com',
                'password' => 'password',
                'role' => 'Operator',
            ],
            [
                'name' => 'QA Manager',
                'email' => 'kabagqc@peroniks.com',
                'password' => 'password',
                'role' => 'Approver',
            ],
            [
                'name' => 'Auditor',
                'email' => 'auditor@peroniks.com',
                'password' => 'password',
                'role' => 'Auditor',
            ],
            [
                'name' => 'Direktur',
                'email' => 'direktur@peroniks.com',
                'password' => 'peronijayajaya123',
                'role' => 'Auditor',
            ],
            [
                'name' => 'Management Representative',
                'email' => 'mr@peroniks.com',
                'password' => 'password123',
                'role' => 'Auditor',
            ],
            [
                'name' => 'Marketing Export',
                'email' => 'marketingexport@peroniks.com',
                'password' => 'password',
                'role' => 'Auditor',
            ],
            [
                'name' => 'Marketing Lokal',
                'email' => 'marketinglokal@peroniks.com',
                'password' => 'password',
                'role' => 'Auditor',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
