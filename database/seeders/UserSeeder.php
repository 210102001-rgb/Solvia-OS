<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Teams ────────────────────────────────────────────────────────
        $techTeam = Team::create([
            'name' => 'Tech & Engineering',
            'code' => 'TECH',
            'description' => 'Software development, IoT engineering, and backend systems',
        ]);

        $creativeTeam = Team::create([
            'name' => 'Creative & Design',
            'code' => 'CREATIVE',
            'description' => 'UI/UX design, content creation, and brand communication',
        ]);

        // ── Super Admin / Owner ───────────────────────────────────────────
        $superAdmin = User::create([
            'name' => 'Rian Pratama',
            'email' => 'rian@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08111234567',
            'role' => 'super_admin',
            'department' => 'Management',
            'team_id' => null,
            'join_date' => '2022-01-10',
            'status' => 'active',
            'profile_bio' => 'Founder & CEO of Solvia.Nova. Drives company vision, client strategy, and product direction.',
        ]);

        // ── Backend Developer ─────────────────────────────────────────────
        $backend = User::create([
            'name' => 'Dimas Ardiansyah',
            'email' => 'dimas@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08122345678',
            'role' => 'backend_developer',
            'department' => 'Engineering',
            'team_id' => $techTeam->id,
            'join_date' => '2022-03-01',
            'status' => 'active',
            'profile_bio' => 'Senior Backend Developer. PHP/Laravel, Node.js, REST APIs, database architecture.',
        ]);

        // ── Frontend Developer ────────────────────────────────────────────
        $frontend = User::create([
            'name' => 'Nadia Kusuma',
            'email' => 'nadia@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08133456789',
            'role' => 'frontend_developer',
            'department' => 'Engineering',
            'team_id' => $techTeam->id,
            'join_date' => '2022-05-15',
            'status' => 'active',
            'profile_bio' => 'Frontend Developer. React, Vue.js, Tailwind CSS, and responsive UI implementation.',
        ]);

        // ── IoT Engineer ──────────────────────────────────────────────────
        $iot = User::create([
            'name' => 'Bagas Wicaksono',
            'email' => 'bagas@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08144567890',
            'role' => 'iot_engineer',
            'department' => 'Engineering',
            'team_id' => $techTeam->id,
            'join_date' => '2023-01-10',
            'status' => 'active',
            'profile_bio' => 'IoT Engineer. ESP32, Raspberry Pi, MQTT, sensor integration, and embedded systems.',
        ]);

        // ── Designer ──────────────────────────────────────────────────────
        $designer = User::create([
            'name' => 'Sinta Rahayu',
            'email' => 'sinta@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08155678901',
            'role' => 'designer',
            'department' => 'Creative',
            'team_id' => $creativeTeam->id,
            'join_date' => '2022-08-01',
            'status' => 'active',
            'profile_bio' => 'UI/UX Designer. Figma, user research, wireframing, and design systems.',
        ]);

        // ── Content Creator ───────────────────────────────────────────────
        $content = User::create([
            'name' => 'Firda Amalia',
            'email' => 'firda@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08166789012',
            'role' => 'content_creator',
            'department' => 'Creative',
            'team_id' => $creativeTeam->id,
            'join_date' => '2023-03-20',
            'status' => 'active',
            'profile_bio' => 'Content Creator & Copywriter. Social media strategy, blog content, and brand storytelling.',
        ]);

        // ── Viewer (client stakeholder) ───────────────────────────────────
        User::create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendra@solvia.id',
            'password' => Hash::make('password'),
            'phone' => '08177890123',
            'role' => 'viewer',
            'department' => 'Operations',
            'team_id' => null,
            'join_date' => '2024-01-05',
            'status' => 'active',
            'profile_bio' => 'Operations Coordinator. Read-only access for project status monitoring.',
        ]);

        // Assign team leads
        $techTeam->update(['lead_id' => $backend->id]);
        $creativeTeam->update(['lead_id' => $designer->id]);
    }
}
