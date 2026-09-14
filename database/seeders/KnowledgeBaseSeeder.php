<?php

namespace Database\Seeders;

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $backend    = User::where('role', 'backend_developer')->first();
        $designer   = User::where('role', 'designer')->first();

        $articles = [
            [
                'title'       => 'SOP: Daily Progress Submission',
                'category'    => 'sop',
                'author_id'   => $superAdmin->id,
                'is_published' => true,
                'content'     => "## SOP Daily Progress Submission\n\n### Tujuan\nMemastikan setiap anggota tim melaporkan progres pekerjaan harian secara konsisten dan tepat waktu.\n\n### Waktu Submission\n- **Batas waktu**: Setiap hari kerja pukul **18:00 WIB**\n- **Tidak ada submission di hari libur nasional**\n\n### Format Wajib\n1. **Task yang dikerjakan** — pilih task yang relevan dari sistem\n2. **Progress (%)** — update persentase penyelesaian\n3. **Pekerjaan yang diselesaikan hari ini** — deskripsi konkret\n4. **Rencana esok hari** — apa yang akan dikerjakan\n5. **Blocker** — jika ada hambatan, deskripsikan dengan detail\n6. **Jam kerja** — default 8 jam, sesuaikan jika berbeda\n\n### Sanksi\nProgress yang tidak disubmit sebelum 18:00 akan menghasilkan notifikasi otomatis ke Super Admin. Tiga kali miss berturut-turut akan menjadi catatan evaluasi performa.\n\n### Tips\n- Submit progress secara real-time, jangan menunggu akhir hari\n- Jika ada blocker, laporkan segera — jangan tunggu submission harian\n- Progress harus akurat dan dapat diverifikasi",
            ],
            [
                'title'       => 'Development Guide: Laravel Project Setup',
                'category'    => 'development_guide',
                'author_id'   => $backend->id,
                'is_published' => true,
                'content'     => "## Laravel Project Setup Guide\n\n### Prerequisites\n- PHP 8.2+\n- Composer 2.x\n- MySQL 8.0+ atau PostgreSQL 15+\n- Node.js 20+ dan npm\n\n### Setup Steps\n\n```bash\n# Clone repository\ngit clone https://github.com/solvia-nova/[project-name].git\ncd [project-name]\n\n# Install dependencies\ncomposer install\nnpm install\n\n# Environment setup\ncp .env.example .env\nphp artisan key:generate\n\n# Database\nphp artisan migrate\nphp artisan db:seed\n\n# Build assets\nnpm run build\n\n# Start development server\nphp artisan serve\n```\n\n### Branch Convention\n- `main` — production-ready code only\n- `develop` — integration branch\n- `feature/[ticket-id]-[short-desc]` — feature branches\n- `fix/[ticket-id]-[short-desc]` — bug fixes\n\n### Commit Message Format\n```\nfeat: add user authentication module\nfix: resolve invoice calculation bug\ndocs: update API documentation\nrefactor: extract payment service layer\n```\n\n### Code Standards\n- PSR-12 coding style\n- Run `php artisan pint` before committing\n- All business logic goes in Service layer\n- Controllers should be thin — max 30 lines per method",
            ],
            [
                'title'       => 'Brand Guideline: Solvia.Nova Visual Identity',
                'category'    => 'brand_guideline',
                'author_id'   => $designer->id,
                'is_published' => true,
                'content'     => "## Solvia.Nova Brand Guidelines\n\n### Brand Personality\nSolvia.Nova adalah perusahaan teknologi yang **modern, profesional, dan dapat dipercaya**. Tone komunikasi: langsung, technical-friendly, tanpa jargon berlebihan.\n\n### Color Palette\n| Color | Hex | Usage |\n|-------|-----|-------|\n| Indigo Primary | `#6366f1` | Primary actions, links, highlights |\n| Slate Dark | `#0f172a` | Backgrounds (dark mode) |\n| Slate Medium | `#1e293b` | Cards, panels |\n| White | `#f8fafc` | Primary text |\n| Emerald | `#10b981` | Success, income, positive |\n| Rose | `#f43f5e` | Errors, danger, expenses |\n| Amber | `#f59e0b` | Warnings, at-risk |\n\n### Typography\n- **Headings**: Plus Jakarta Sans, weight 700–800\n- **Body**: Plus Jakarta Sans, weight 400–500\n- **Code/Mono**: JetBrains Mono, weight 400–600\n\n### Logo Usage\n- Minimum size: 24px height for digital, 10mm for print\n- Never distort proportions\n- Never use on busy backgrounds without contrast\n- Approved variations: Full logo, Monogram (SN)\n\n### Tone of Voice\n- **Do**: 'Your project is at risk' / 'Invoice overdue'\n- **Don't**: 'Uh oh! Something went wrong!' / 'Oops!'\n- Keep error messages actionable: tell the user what to do next",
            ],
            [
                'title'       => 'SOP: Asset Management Lifecycle',
                'category'    => 'sop',
                'author_id'   => $superAdmin->id,
                'is_published' => true,
                'content'     => "## Asset Management Lifecycle SOP\n\n### Lifecycle Stages\n```\nPurchased → Available → Assigned → In Use → Maintenance → Returned → Retired → Disposed\n```\n\n### Receiving New Assets\n1. Catat di Solvia.Nova OS: Resources → Assets → Register Asset\n2. Input: Asset Tag (format: [TYPE]-[NUM], e.g. LAPTOP-005)\n3. Foto kondisi awal dan simpan sebagai dokumen\n4. Jika langsung assign: pilih user penerima\n\n### Assignment\n- Semua assignment harus tercatat di sistem\n- User yang menerima asset bertanggung jawab atas kondisinya\n- Assignment harus ada catatan kondisi saat diterima\n\n### Maintenance\n- Maintenance terjadwal: input di sistem minimal 1 minggu sebelumnya\n- Biaya maintenance otomatis tercatat sebagai expense kategori 'equipment'\n- Setelah maintenance selesai, update status dan accumulated cost\n\n### Return Procedure\n1. User melaporkan ke Super Admin\n2. Super Admin memverifikasi kondisi asset\n3. Update di sistem: Resources → Assets → Return\n4. Input kondisi saat dikembalikan\n5. Asset kembali ke status 'Available'\n\n### Disposal\n- Hanya Super Admin yang dapat mengubah status ke 'Retired' atau 'Disposed'\n- Diperlukan dokumentasi alasan disposal\n- Asset yang di-dispose tidak dihapus dari sistem (audit trail)",
            ],
            [
                'title'       => 'FAQ: Solvia.Nova OS — Pertanyaan Umum',
                'category'    => 'faq',
                'author_id'   => $superAdmin->id,
                'is_published' => true,
                'content'     => "## FAQ Solvia.Nova OS\n\n### Q: Bagaimana cara submit daily progress?\n**A:** Dashboard → My Tasks → Update Progress, atau gunakan menu Daily Progress di sidebar. Pastikan submit sebelum 18:00 WIB.\n\n### Q: Saya tidak bisa melihat data finance. Kenapa?\n**A:** Akses Finance dibatasi untuk Super Admin. Jika Anda memerlukan akses, hubungi Rian Pratama untuk pemberian permission khusus.\n\n### Q: Bagaimana cara melaporkan blocker?\n**A:** Ada dua cara:\n1. Saat submit daily progress — isi field 'Blocker'\n2. Menu Blockers → Report New Blocker (untuk blocker yang perlu tracking khusus)\n\n### Q: Credential akun perusahaan di mana?\n**A:** Resources → Company Accounts. Akses hanya untuk Super Admin dan diaudit setiap akses. Hubungi Super Admin jika membutuhkan credential tertentu.\n\n### Q: Bagaimana cara request pembelian barang?\n**A:** Menu Purchasing → New Request. Isi detail item, estimasi harga, dan alasan. Request akan direview oleh Super Admin.\n\n### Q: Bagaimana cara submit reimbursement?\n**A:** Finance → Reimbursements → Submit Claim. Isi detail, lampirkan bukti jika ada. Akan di-review dan disetujui oleh Super Admin.\n\n### Q: Apakah data salary saya bisa dilihat oleh rekan kerja?\n**A:** Tidak. Data payroll hanya dapat diakses oleh Super Admin. Role lain tidak memiliki akses ke halaman payroll.",
            ],
        ];

        foreach ($articles as $article) {
            KnowledgeBase::create(array_merge($article, [
                'slug' => Str::slug($article['title']) . '-' . Str::random(4),
            ]));
        }
    }
}
