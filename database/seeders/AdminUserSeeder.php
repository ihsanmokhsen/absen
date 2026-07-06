<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'BADAN PENDAPATAN DAN ASET DAERAH',
                'username' => 'bpad',
                'email' => 'admin@bpadntt.local',
                'password' => 'bpad1',
                'is_admin' => true,
                'bidang' => null,
            ],
            [
                'name' => 'SEKRETARIAT',
                'username' => 'sekretariat',
                'email' => 'sekretariat@bpadntt.local',
                'password' => 'sekretariat1',
                'is_admin' => false,
                'bidang' => 'SEKRETARIAT',
            ],
            [
                'name' => 'PENDAPATAN 1',
                'username' => 'pendapatan1',
                'email' => 'pendapatan1@bpadntt.local',
                'password' => 'pendapatan1',
                'is_admin' => false,
                'bidang' => 'PENDAPATAN 1',
            ],
            [
                'name' => 'PENDAPATAN 2',
                'username' => 'pendapatan2',
                'email' => 'pendapatan2@bpadntt.local',
                'password' => 'pendapatan2',
                'is_admin' => false,
                'bidang' => 'PENDAPATAN 2',
            ],
            [
                'name' => 'ASET 1',
                'username' => 'aset1',
                'email' => 'aset1@bpadntt.local',
                'password' => 'aset1',
                'is_admin' => false,
                'bidang' => 'ASET 1',
            ],
            [
                'name' => 'ASET 2',
                'username' => 'aset2',
                'email' => 'aset2@bpadntt.local',
                'password' => 'aset2',
                'is_admin' => false,
                'bidang' => 'ASET 2',
            ],
        ];

        foreach ($users as $user) {
            $model = User::query()
                ->where('username', $user['username'])
                ->orWhere('email', $user['email'])
                ->first() ?? new User();

            $model->fill([
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
                'is_admin' => $user['is_admin'],
                'bidang' => $user['bidang'],
            ])->save();
        }
    }
}
