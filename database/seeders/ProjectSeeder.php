<?php

namespace Database\Seeders;

use App\Models\Blocker;
use App\Models\Client;
use App\Models\DailyProgress;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $backend    = User::where('role', 'backend_developer')->first();
        $frontend   = User::where('role', 'frontend_developer')->first();
        $iot        = User::where('role', 'iot_engineer')->first();
        $designer   = User::where('role', 'designer')->first();
        $content    = User::where('role', 'content_creator')->first();

        $clients = Client::all()->keyBy('client_code');

        // ─── PROJECT 1: Smart IoT Monitoring (Active, At Risk) ────────────
        $p1 = Project::create([
            'project_code'   => 'PRJ-2026-001',
            'name'           => 'Smart Farm Monitoring System',
            'client_id'      => $clients['CLT-002']->id,
            'description'    => 'IoT-based real-time monitoring system for soil moisture, temperature, humidity, and pest detection across agricultural farms with automated alert and dashboard.',
            'project_type'   => 'IoT Engineering',
            'start_date'     => '2026-07-01',
            'deadline'       => '2026-10-15',
            'revenue'        => 185_000_000,
            'budget'         => 110_000_000,
            'actual_cost'    => 67_000_000,
            'profit'         => 118_000_000,
            'profit_margin'  => 63.78,
            'status'         => 'active',
            'health'         => 'at_risk',
        ]);

        $this->addMembers($p1, [
            [$iot, 'IoT Engineer', 'Hardware setup, sensor integration, MQTT broker'],
            [$backend, 'Backend Developer', 'API development, data pipeline, database'],
            [$frontend, 'Frontend Developer', 'Dashboard UI, real-time charts'],
            [$designer, 'Designer', 'Dashboard UX and mobile interface design'],
        ]);

        $m1a = Milestone::create([
            'project_id'  => $p1->id,
            'name'        => 'Phase 1 — Hardware & Infrastructure',
            'description' => 'Sensor deployment, ESP32 setup, MQTT configuration, and VPS provisioning',
            'start_date'  => '2026-07-01',
            'deadline'    => '2026-08-01',
            'progress'    => 100,
            'status'      => 'completed',
        ]);

        $m1b = Milestone::create([
            'project_id'  => $p1->id,
            'name'        => 'Phase 2 — Backend API & Data Pipeline',
            'description' => 'REST API, MQTT subscriber, data storage, and alert engine',
            'start_date'  => '2026-08-01',
            'deadline'    => '2026-09-05',
            'progress'    => 65,
            'status'      => 'in_progress',
        ]);

        $m1c = Milestone::create([
            'project_id'  => $p1->id,
            'name'        => 'Phase 3 — Dashboard & Mobile',
            'description' => 'Monitoring dashboard, mobile responsive view, and notification integration',
            'start_date'  => '2026-09-05',
            'deadline'    => '2026-10-10',
            'progress'    => 10,
            'status'      => 'pending',
        ]);

        // Tasks for P1
        $t1 = Task::create([
            'project_id'      => $p1->id,
            'milestone_id'    => $m1b->id,
            'assignee_id'     => $backend->id,
            'title'           => 'MQTT Subscriber Service',
            'description'     => 'Build Node.js service to subscribe to farm sensor topics and persist data to PostgreSQL',
            'priority'        => 'high',
            'status'          => 'in_progress',
            'progress'        => 70,
            'start_date'      => '2026-08-05',
            'deadline'        => Carbon::now()->addDays(3)->toDateString(),
            'estimated_hours' => 32,
            'actual_hours'    => 22,
        ]);

        $t2 = Task::create([
            'project_id'      => $p1->id,
            'milestone_id'    => $m1b->id,
            'assignee_id'     => $backend->id,
            'title'           => 'Sensor Alert Engine',
            'description'     => 'Rule-based alert system that triggers notifications when sensor thresholds are exceeded',
            'priority'        => 'high',
            'status'          => 'blocked',
            'progress'        => 20,
            'start_date'      => '2026-08-20',
            'deadline'        => Carbon::now()->addDays(7)->toDateString(),
            'estimated_hours' => 24,
            'actual_hours'    => 5,
        ]);

        $t3 = Task::create([
            'project_id'      => $p1->id,
            'milestone_id'    => $m1c->id,
            'assignee_id'     => $frontend->id,
            'title'           => 'Real-time Dashboard UI',
            'description'     => 'React dashboard with live sensor data charts, farm map overlay, and alert feed',
            'priority'        => 'urgent',
            'status'          => 'to_do',
            'progress'        => 0,
            'start_date'      => Carbon::now()->addDays(5)->toDateString(),
            'deadline'        => Carbon::now()->addDays(21)->toDateString(),
            'estimated_hours' => 40,
            'actual_hours'    => 0,
        ]);

        Task::create([
            'project_id'      => $p1->id,
            'milestone_id'    => $m1c->id,
            'assignee_id'     => $designer->id,
            'title'           => 'Dashboard UX Wireframes',
            'description'     => 'Figma wireframes for monitoring dashboard and mobile views',
            'priority'        => 'high',
            'status'          => 'done',
            'progress'        => 100,
            'start_date'      => '2026-08-01',
            'deadline'        => '2026-08-15',
            'estimated_hours' => 16,
            'actual_hours'    => 14,
        ]);

        Task::create([
            'project_id'      => $p1->id,
            'milestone_id'    => $m1a->id,
            'assignee_id'     => $iot->id,
            'title'           => 'ESP32 Sensor Firmware',
            'description'     => 'Custom firmware for ESP32 nodes collecting soil, temperature, and humidity data',
            'priority'        => 'urgent',
            'status'          => 'done',
            'progress'        => 100,
            'start_date'      => '2026-07-05',
            'deadline'        => '2026-07-25',
            'estimated_hours' => 36,
            'actual_hours'    => 38,
        ]);

        // Blocker for P1
        Blocker::create([
            'project_id'          => $p1->id,
            'task_id'             => $t2->id,
            'title'               => 'Alert threshold config schema not finalized',
            'description'         => 'Client has not confirmed sensor threshold values. Cannot implement alert rules without agreed parameters for soil moisture, temperature, and humidity ranges.',
            'priority'            => 'high',
            'type'                => 'client',
            'reporter_id'         => $backend->id,
            'responsible_user_id' => $superAdmin->id,
            'status'              => 'open',
        ]);

        Blocker::create([
            'project_id'          => $p1->id,
            'task_id'             => $t1->id,
            'title'               => 'MQTT broker SSL certificate expired',
            'description'         => 'The staging MQTT broker SSL cert expired on 10 Sep. Messages from farm nodes are being rejected. Need to renew or replace the certificate.',
            'priority'            => 'urgent',
            'type'                => 'technical',
            'reporter_id'         => $iot->id,
            'responsible_user_id' => $backend->id,
            'status'              => 'in_progress',
        ]);

        // Daily Progress for P1
        $this->seedDailyProgress($p1, $t1, $backend, [
            ['2026-09-10', 60, 'Completed MQTT subscriber core logic and unit tests', 'Implement retry logic and dead letter queue', null, 8],
            ['2026-09-11', 65, 'Added retry mechanism, improved logging', 'DLQ implementation and Redis caching', 'Waiting for SSL cert renewal on broker', 7.5],
            ['2026-09-12', 70, 'Redis caching layer added for sensor data', 'Final QA and documentation', null, 8],
        ]);

        $this->seedDailyProgress($p1, null, $iot, [
            ['2026-09-11', 85, 'SSL cert issue identified, raised to Dimas for infra fix', 'Monitor broker once cert renewed', 'MQTT broker SSL cert expired — blocked', 6],
            ['2026-09-12', 85, 'Documenting sensor node firmware — no code changes while blocked', 'Resume integration testing after cert renewed', 'MQTT broker still down', 4],
        ]);

        // ─── PROJECT 2: E-Commerce Platform (Active, On Track) ───────────
        $p2 = Project::create([
            'project_code'   => 'PRJ-2026-002',
            'name'           => 'Logistik Cepat Mobile App',
            'client_id'      => $clients['CLT-004']->id,
            'description'    => 'Cross-platform mobile application (React Native) for logistics tracking, driver management, and delivery status with real-time updates.',
            'project_type'   => 'Mobile Application',
            'start_date'     => '2026-08-01',
            'deadline'       => '2026-11-30',
            'revenue'        => 220_000_000,
            'budget'         => 130_000_000,
            'actual_cost'    => 42_000_000,
            'profit'         => 178_000_000,
            'profit_margin'  => 80.91,
            'status'         => 'active',
            'health'         => 'on_track',
        ]);

        $this->addMembers($p2, [
            [$frontend, 'Frontend Developer', 'React Native app — driver and customer screens'],
            [$backend, 'Backend Developer', 'REST API, WebSocket for live tracking, database'],
            [$designer, 'Designer', 'Mobile UX/UI design and component library'],
        ]);

        $m2a = Milestone::create([
            'project_id'  => $p2->id,
            'name'        => 'Design System & Prototype',
            'description' => 'Complete Figma design system, component library, and interactive prototype',
            'start_date'  => '2026-08-01',
            'deadline'    => '2026-08-20',
            'progress'    => 100,
            'status'      => 'completed',
        ]);

        $m2b = Milestone::create([
            'project_id'  => $p2->id,
            'name'        => 'Core App Features',
            'description' => 'Driver app, customer tracking, authentication, and push notifications',
            'start_date'  => '2026-08-20',
            'deadline'    => '2026-10-15',
            'progress'    => 45,
            'status'      => 'in_progress',
        ]);

        $t4 = Task::create([
            'project_id'      => $p2->id,
            'milestone_id'    => $m2b->id,
            'assignee_id'     => $frontend->id,
            'title'           => 'Driver Authentication & Profile',
            'description'     => 'Login, OTP verification, profile management, and session handling',
            'priority'        => 'high',
            'status'          => 'done',
            'progress'        => 100,
            'start_date'      => '2026-08-20',
            'deadline'        => '2026-09-05',
            'estimated_hours' => 20,
            'actual_hours'    => 18,
        ]);

        $t5 = Task::create([
            'project_id'      => $p2->id,
            'milestone_id'    => $m2b->id,
            'assignee_id'     => $frontend->id,
            'title'           => 'Real-time Delivery Tracking Map',
            'description'     => 'Google Maps integration with live driver location, route display, and ETA calculation',
            'priority'        => 'urgent',
            'status'          => 'in_progress',
            'progress'        => 55,
            'start_date'      => '2026-09-05',
            'deadline'        => Carbon::now()->addDays(10)->toDateString(),
            'estimated_hours' => 36,
            'actual_hours'    => 20,
        ]);

        Task::create([
            'project_id'      => $p2->id,
            'milestone_id'    => $m2b->id,
            'assignee_id'     => $backend->id,
            'title'           => 'WebSocket Live Location API',
            'description'     => 'Socket.IO server for broadcasting driver GPS coordinates to active customer sessions',
            'priority'        => 'high',
            'status'          => 'waiting_review',
            'progress'        => 90,
            'start_date'      => '2026-09-01',
            'deadline'        => Carbon::now()->addDays(2)->toDateString(),
            'estimated_hours' => 24,
            'actual_hours'    => 22,
        ]);

        $this->seedDailyProgress($p2, $t5, $frontend, [
            ['2026-09-10', 40, 'Map component initialized, marker placement working', 'Route polyline rendering and live position updates', null, 8],
            ['2026-09-11', 48, 'Route polyline rendering completed', 'WebSocket integration for live driver position', null, 8],
            ['2026-09-12', 55, 'WebSocket integration 50% done, driver position updating every 5s', 'Complete live update + ETA calculation', null, 7.5],
        ]);

        // ─── PROJECT 3: Brand Identity (Planning) ────────────────────────
        $p3 = Project::create([
            'project_code'   => 'PRJ-2026-003',
            'name'           => 'Kopi Literasi Brand Refresh',
            'client_id'      => $clients['CLT-003']->id,
            'description'    => 'Complete brand identity refresh including logo redesign, visual identity system, packaging design, social media templates, and content strategy.',
            'project_type'   => 'Brand Identity & Content',
            'start_date'     => Carbon::now()->addDays(5)->toDateString(),
            'deadline'       => Carbon::now()->addDays(65)->toDateString(),
            'revenue'        => 45_000_000,
            'budget'         => 22_000_000,
            'actual_cost'    => 0,
            'profit'         => 45_000_000,
            'profit_margin'  => 100,
            'status'         => 'planning',
            'health'         => 'on_track',
        ]);

        $this->addMembers($p3, [
            [$designer, 'Designer', 'Logo, visual identity, packaging, and brand guidelines'],
            [$content, 'Content Creator', 'Social media strategy, copywriting, and content calendar'],
        ]);

        Milestone::create([
            'project_id'  => $p3->id,
            'name'        => 'Brand Discovery & Research',
            'description' => 'Client interviews, competitor analysis, moodboard, and brand brief',
            'start_date'  => Carbon::now()->addDays(5)->toDateString(),
            'deadline'    => Carbon::now()->addDays(20)->toDateString(),
            'progress'    => 0,
            'status'      => 'pending',
        ]);

        Milestone::create([
            'project_id'  => $p3->id,
            'name'        => 'Visual Identity Design',
            'description' => 'Logo variants, color palette, typography, and brand guidelines document',
            'start_date'  => Carbon::now()->addDays(20)->toDateString(),
            'deadline'    => Carbon::now()->addDays(45)->toDateString(),
            'progress'    => 0,
            'status'      => 'pending',
        ]);

        // ─── PROJECT 4: E-Learning Platform (Completed) ──────────────────
        $p4 = Project::create([
            'project_code'   => 'PRJ-2025-004',
            'name'           => 'Yayasan PK E-Learning Platform',
            'client_id'      => $clients['CLT-005']->id,
            'description'    => 'Custom LMS with course management, video streaming, quiz engine, certificate generation, and student progress tracking.',
            'project_type'   => 'Web Platform',
            'start_date'     => '2025-09-01',
            'deadline'       => '2026-03-31',
            'revenue'        => 150_000_000,
            'budget'         => 90_000_000,
            'actual_cost'    => 84_500_000,
            'profit'         => 65_500_000,
            'profit_margin'  => 43.67,
            'status'         => 'completed',
            'health'         => 'on_track',
            'closed_at'      => '2026-04-05',
            'closure_notes'  => 'Project completed on time. Final invoice pending client confirmation.',
        ]);

        $this->addMembers($p4, [
            [$backend, 'Backend Developer', 'Laravel LMS backend, video streaming integration'],
            [$frontend, 'Frontend Developer', 'Blade/React frontend, student portal UI'],
            [$designer, 'Designer', 'Platform UX, course card design'],
        ]);

        // ─── PROJECT 5: Enterprise Dashboard (Active, Off Track) ─────────
        $p5 = Project::create([
            'project_code'   => 'PRJ-2026-005',
            'name'           => 'Maju Bersama Digital Enterprise Dashboard',
            'client_id'      => $clients['CLT-001']->id,
            'description'    => 'Enterprise analytics and reporting dashboard aggregating data from ERP, CRM, and POS systems with role-based access and exportable reports.',
            'project_type'   => 'Web Platform',
            'start_date'     => '2026-06-01',
            'deadline'       => Carbon::now()->addDays(14)->toDateString(),
            'revenue'        => 275_000_000,
            'budget'         => 160_000_000,
            'actual_cost'    => 145_000_000,
            'profit'         => 130_000_000,
            'profit_margin'  => 47.27,
            'status'         => 'active',
            'health'         => 'off_track',
        ]);

        $this->addMembers($p5, [
            [$backend, 'Backend Developer', 'API integrations, data aggregation, report engine'],
            [$frontend, 'Frontend Developer', 'Dashboard charts, data tables, export functions'],
            [$designer, 'Designer', 'Dashboard layout, data visualization design'],
            [$content, 'Content Creator', 'User documentation and help content'],
        ]);

        $m5a = Milestone::create([
            'project_id'  => $p5->id,
            'name'        => 'Data Integration Layer',
            'description' => 'Connect ERP, CRM, and POS APIs. Normalize data structures.',
            'start_date'  => '2026-06-01',
            'deadline'    => '2026-07-15',
            'progress'    => 100,
            'status'      => 'completed',
        ]);

        $m5b = Milestone::create([
            'project_id'  => $p5->id,
            'name'        => 'Dashboard & Report Engine',
            'description' => 'All KPI widgets, drill-down charts, scheduled reports, and PDF export',
            'start_date'  => '2026-07-15',
            'deadline'    => Carbon::now()->addDays(10)->toDateString(),
            'progress'    => 40,
            'status'      => 'in_progress',
        ]);

        $overdue1 = Task::create([
            'project_id'      => $p5->id,
            'milestone_id'    => $m5b->id,
            'assignee_id'     => $backend->id,
            'title'           => 'PDF Report Export Engine',
            'description'     => 'Headless Chrome PDF generation from dashboard views with client branding',
            'priority'        => 'urgent',
            'status'          => 'in_progress',
            'progress'        => 35,
            'start_date'      => '2026-08-15',
            'deadline'        => Carbon::now()->subDays(5)->toDateString(), // overdue
            'estimated_hours' => 28,
            'actual_hours'    => 12,
        ]);

        $overdue2 = Task::create([
            'project_id'      => $p5->id,
            'milestone_id'    => $m5b->id,
            'assignee_id'     => $frontend->id,
            'title'           => 'Role-based Dashboard Permissions',
            'description'     => 'Per-user widget visibility, data scope filtering, and admin access control',
            'priority'        => 'high',
            'status'          => 'revision',
            'progress'        => 60,
            'start_date'      => '2026-08-20',
            'deadline'        => Carbon::now()->subDays(3)->toDateString(), // overdue
            'estimated_hours' => 20,
            'actual_hours'    => 15,
        ]);

        Task::create([
            'project_id'      => $p5->id,
            'milestone_id'    => $m5b->id,
            'assignee_id'     => $frontend->id,
            'title'           => 'KPI Summary Widget Set',
            'description'     => 'Revenue, orders, customers, and conversion rate widgets with sparklines',
            'priority'        => 'high',
            'status'          => 'done',
            'progress'        => 100,
            'start_date'      => '2026-07-15',
            'deadline'        => '2026-08-10',
            'estimated_hours' => 24,
            'actual_hours'    => 26,
        ]);

        Blocker::create([
            'project_id'          => $p5->id,
            'task_id'             => $overdue1->id,
            'title'               => 'Client ERP API rate limiting causing report timeouts',
            'description'         => 'The client ERP API limits to 100 requests/min. Large reports with multiple data sources hit the limit and timeout. Need client to whitelist our server IP or increase limits.',
            'priority'            => 'urgent',
            'type'                => 'client',
            'reporter_id'         => $backend->id,
            'responsible_user_id' => $superAdmin->id,
            'status'              => 'open',
        ]);

        Blocker::create([
            'project_id'          => $p5->id,
            'task_id'             => $overdue2->id,
            'title'               => 'Permission matrix specification incomplete',
            'description'         => 'Client has not delivered the full permission matrix for 12 user roles. Cannot complete role-based access control without this specification.',
            'priority'            => 'high',
            'type'                => 'client',
            'reporter_id'         => $frontend->id,
            'responsible_user_id' => $superAdmin->id,
            'status'              => 'open',
        ]);
    }

    private function addMembers(Project $project, array $members): void
    {
        foreach ($members as [$user, $role, $responsibility]) {
            ProjectMember::create([
                'project_id'    => $project->id,
                'user_id'       => $user->id,
                'role'          => $role,
                'responsibility' => $responsibility,
                'assigned_date' => $project->start_date,
                'status'        => 'active',
            ]);
        }
    }

    private function seedDailyProgress(Project $project, ?Task $task, User $user, array $entries): void
    {
        foreach ($entries as [$date, $progress, $completed, $next, $blocker, $hours]) {
            DailyProgress::create([
                'user_id'        => $user->id,
                'project_id'     => $project->id,
                'task_id'        => $task?->id,
                'date'           => $date,
                'progress'       => $progress,
                'completed_work' => $completed,
                'next_plan'      => $next,
                'blocker'        => $blocker,
                'working_hours'  => $hours,
            ]);
        }
    }
}
