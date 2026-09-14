<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $allUsers   = User::all();

        // ═══════════════════════════════════════
        // ANNOUNCEMENTS
        // ═══════════════════════════════════════
        $announcements = [
            [
                'author_id'      => $superAdmin->id,
                'title'          => 'Q3 2026 Company Performance Review',
                'content'        => "Tim Solvia.Nova,\n\nAlhamdulillah, performa Q3 2026 sangat positif. Revenue mencapai 424,5 juta IDR dengan net profit margin rata-rata 68%. Project portfolio terdiri dari 4 active projects dengan total contract value 725 juta IDR.\n\nBeberapa highlight:\n- Smart Farm (CLT-002): Fase hardware selesai 100%, backend dalam progress\n- Logistik Mobile (CLT-004): On track dengan progress 45%\n- Enterprise Dashboard (CLT-001): Off track — perlu perhatian extra dari tim\n\nMari kita fokus di sisa Q3 untuk menyelesaikan milestone-milestone kritis. Terima kasih atas kerja keras semua.\n\nRian Pratama\nFounder & CEO, Solvia.Nova",
                'category'       => 'company',
                'priority'       => 'high',
                'audience_type'  => 'all',
                'publish_date'   => Carbon::now()->subDays(2),
                'expiry_date'    => Carbon::now()->addDays(28),
                'status'         => 'published',
            ],
            [
                'author_id'      => $superAdmin->id,
                'title'          => '[URGENT] Enterprise Dashboard — All Hands on Deck',
                'content'        => "Team,\n\nProject Enterprise Dashboard (PRJ-2026-005) saat ini berstatus OFF TRACK dengan deadline 14 hari lagi. Ada 2 task overdue dan 2 blocker yang belum resolved.\n\nYang perlu dilakukan segera:\n1. Dimas: PDF Report Engine harus selesai dalam 4 hari\n2. Nadia: Permission matrix — koordinasi langsung dengan client\n3. Semua blocker: Rian akan follow up ke client hari ini untuk permission matrix dan API rate limit\n\nDaily standup akan dimajukan ke jam 08:30 mulai besok untuk project ini.\n\nTidak ada toleransi slip lebih lanjut untuk project ini.",
                'category'       => 'urgent',
                'priority'       => 'urgent',
                'audience_type'  => 'all',
                'publish_date'   => Carbon::now()->subDays(1),
                'expiry_date'    => Carbon::now()->addDays(14),
                'status'         => 'published',
            ],
            [
                'author_id'      => $superAdmin->id,
                'title'          => 'Prosedur Baru: Daily Progress Wajib Sebelum 18:00',
                'content'        => "Mulai 15 September 2026, daily progress WAJIB disubmit sebelum pukul 18:00 WIB setiap hari kerja.\n\nFormat yang diisi:\n- Task yang dikerjakan\n- Persentase progress\n- Completed work hari ini\n- Rencana besok\n- Blocker (jika ada)\n\nProgress yang tidak disubmit akan otomatis menghasilkan notifikasi ke Super Admin dan reminder ke user bersangkutan pada pukul 18:30.\n\nSistem ini membantu project monitoring yang lebih akurat dan menghindari surprise pada review mingguan.",
                'category'       => 'policy',
                'priority'       => 'high',
                'audience_type'  => 'role',
                'audience_target' => 'backend_developer,frontend_developer,iot_engineer,designer,content_creator',
                'publish_date'   => Carbon::now()->subDays(5),
                'expiry_date'    => null,
                'status'         => 'published',
            ],
            [
                'author_id'      => $superAdmin->id,
                'title'          => 'Info: Renewal Domain agritechnusantara.id',
                'content'        => "Domain agritechnusantara.id (client CLT-002) akan expired dalam 22 hari.\n\nAction required:\n- Konfirmasi dengan client apakah domain di-renew atau dipindah ke registrar lain\n- Proses renewal sebelum H-7 untuk menghindari grace period\n- Update resource registry setelah renewal\n\nBiaya renewal: ~Rp 150.000/tahun via Namecheap.",
                'category'       => 'technical',
                'priority'       => 'medium',
                'audience_type'  => 'all',
                'publish_date'   => Carbon::now()->subHours(3),
                'expiry_date'    => Carbon::now()->addDays(22),
                'status'         => 'published',
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::create($data);
        }

        // ═══════════════════════════════════════
        // NOTIFICATIONS — Action-driven
        // ═══════════════════════════════════════
        $backend  = User::where('role', 'backend_developer')->first();
        $frontend = User::where('role', 'frontend_developer')->first();
        $iot      = User::where('role', 'iot_engineer')->first();

        // Super Admin notifications
        $superAdminNotifs = [
            ['danger', 'Project Off Track: Enterprise Dashboard', 'PRJ-2026-005 progress is at 40% against expected 75%. Deadline in 14 days. 2 overdue tasks, 2 open blockers.', '/projects/5'],
            ['warning', 'Invoice Overdue: INV-2026-004', 'Invoice INV-2026-004 for PT Maju Bersama Digital (Rp 82,500,000) is 8 days overdue.', '/finance/invoices'],
            ['warning', 'Domain Expiring: agritechnusantara.id', 'Domain agritechnusantara.id expires in 22 days. Auto-renewal is OFF.', '/resources/infrastructure'],
            ['warning', 'Subscription Due: Adobe Creative Cloud (5 days)', 'Adobe Creative Cloud billing of Rp 850,000 is due in 5 days.', '/resources/subscriptions'],
            ['warning', 'SSL Expiring: staging.solvia.id (12 days)', 'SSL certificate for staging.solvia.id expires in 12 days.', '/resources/infrastructure'],
            ['info', 'Purchase Request: REQ-2026-001 (Arduino Nano)', 'Bagas Wicaksono submitted a purchase request for Arduino Nano x10. Rp 650,000.', '/purchasing'],
            ['info', 'Purchase Request: REQ-2026-005 (Soil Moisture Sensors)', 'Bagas Wicaksono submitted urgent purchase request for 20 Soil Moisture Sensors. Rp 900,000.', '/purchasing'],
            ['warning', 'Low Inventory: Sensor DHT22 (3 pcs, min: 5)', 'DHT22 sensor stock (3 pcs) is below minimum (5 pcs). Consider raising a purchase request.', '/resources/inventory'],
            ['warning', 'Low Inventory: Arduino Nano (0 pcs, out of stock)', 'Arduino Nano is completely out of stock. Immediate restock required.', '/resources/inventory'],
            ['info', 'Reimbursement Submitted: Sinta Rahayu', 'Font License reimbursement of Rp 350,000 submitted for review.', '/finance/reimbursements'],
            ['warning', 'Payroll Pending: September 2026', '5 employee payrolls approved and awaiting disbursement for period 2026-09.', '/finance/payroll'],
            ['danger', 'Blocker Open: MQTT SSL Certificate Expired', 'Critical blocker on PRJ-2026-001: MQTT broker SSL certificate expired. Blocking IoT sensor integration.', '/blockers'],
        ];

        foreach ($superAdminNotifs as [$level, $title, $message, $url]) {
            Notification::create([
                'user_id'    => $superAdmin->id,
                'type'       => 'system_alert',
                'title'      => $title,
                'message'    => $message,
                'action_url' => $url,
                'level'      => $level,
                'is_read'    => false,
            ]);
        }

        // Backend developer notifications
        $backendNotifs = [
            ['danger', 'Task Overdue: PDF Report Export Engine', 'Your task "PDF Report Export Engine" on PRJ-2026-005 is 5 days overdue.', '/projects/5'],
            ['warning', 'Blocker Assigned to You: MQTT SSL Certificate', 'You are responsible for resolving the MQTT broker SSL certificate blocker on PRJ-2026-001.', '/blockers'],
            ['info', 'Task Assigned: WebSocket Live Location API', 'You have been assigned "WebSocket Live Location API" task on PRJ-2026-002. Deadline in 2 days.', '/projects/2'],
        ];

        foreach ($backendNotifs as [$level, $title, $message, $url]) {
            Notification::create([
                'user_id'    => $backend->id,
                'type'       => 'task_alert',
                'title'      => $title,
                'message'    => $message,
                'action_url' => $url,
                'level'      => $level,
                'is_read'    => false,
            ]);
        }

        // Frontend notifications
        Notification::create([
            'user_id'    => $frontend->id,
            'type'       => 'task_alert',
            'title'      => 'Task Overdue: Role-based Dashboard Permissions',
            'message'    => 'Your task "Role-based Dashboard Permissions" on PRJ-2026-005 is 3 days overdue. Revision requested by Super Admin.',
            'action_url' => '/projects/5',
            'level'      => 'danger',
            'is_read'    => false,
        ]);

        Notification::create([
            'user_id'    => $frontend->id,
            'type'       => 'task_alert',
            'title'      => 'Task Due Soon: Real-time Delivery Tracking Map (10 days)',
            'message'    => 'Task "Real-time Delivery Tracking Map" on Logistik Cepat Mobile App is due in 10 days. Current progress: 55%.',
            'action_url' => '/projects/2',
            'level'      => 'warning',
            'is_read'    => false,
        ]);

        // IoT notifications
        Notification::create([
            'user_id'    => $iot->id,
            'type'       => 'system_alert',
            'title'      => 'Your Purchase Request Approved: Wacom Tablet',
            'message'    => 'Purchase request REQ-2026-002 (Wacom Intuos Pro) has been approved by Rian Pratama.',
            'action_url' => '/purchasing',
            'level'      => 'success',
            'is_read'    => true,
        ]);

        Notification::create([
            'user_id'    => $iot->id,
            'type'       => 'system_alert',
            'title'      => 'Inventory Alert: DHT22 Sensor Low Stock',
            'message'    => 'DHT22 sensor stock is at 3 pcs, below minimum (5). Your PRJ-2026-001 Phase 2 requires 5 additional sensors.',
            'action_url' => '/resources/inventory',
            'level'      => 'warning',
            'is_read'    => false,
        ]);
    }
}
