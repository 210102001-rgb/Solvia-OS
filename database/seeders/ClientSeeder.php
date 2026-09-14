<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'client_code' => 'CLT-001',
                'name' => 'PT Maju Bersama Digital',
                'contact_person' => 'Budi Santoso',
                'email' => 'budi@majubersama.co.id',
                'phone' => '08211112222',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'website' => 'https://majubersama.co.id',
                'notes' => 'Enterprise client. Recurring projects for digital transformation initiatives.',
                'status' => 'active',
            ],
            [
                'client_code' => 'CLT-002',
                'name' => 'CV Agritech Nusantara',
                'contact_person' => 'Dewi Puspitasari',
                'email' => 'dewi@agritechnusantara.id',
                'phone' => '08222223333',
                'address' => 'Jl. Pahlawan No. 10, Bandung, Jawa Barat',
                'website' => 'https://agritechnusantara.id',
                'notes' => 'IoT monitoring system for agricultural farms. High-value long-term client.',
                'status' => 'active',
            ],
            [
                'client_code' => 'CLT-003',
                'name' => 'Toko Kopi Literasi',
                'contact_person' => 'Ahmad Fauzi',
                'email' => 'ahmad@kopiliterasi.com',
                'phone' => '08233334444',
                'address' => 'Jl. Braga No. 22, Bandung, Jawa Barat',
                'website' => 'https://kopiliterasi.com',
                'notes' => 'Local coffee shop brand needing digital presence and content strategy.',
                'status' => 'active',
            ],
            [
                'client_code' => 'CLT-004',
                'name' => 'PT Logistik Cepat Indonesia',
                'contact_person' => 'Rudi Hermawan',
                'email' => 'rudi@logistikcepat.co.id',
                'phone' => '08244445555',
                'address' => 'Jl. Raya Bogor KM 30, Depok, Jawa Barat',
                'website' => 'https://logistikcepat.co.id',
                'notes' => 'Logistics tracking system and mobile app. Long-term maintenance contract.',
                'status' => 'active',
            ],
            [
                'client_code' => 'CLT-005',
                'name' => 'Yayasan Pendidikan Kreatif',
                'contact_person' => 'Ibu Sari Indah',
                'email' => 'sari@yayasanpk.org',
                'phone' => '08255556666',
                'address' => 'Jl. Kebon Jeruk No. 5, Jakarta Barat',
                'website' => 'https://yayasanpk.org',
                'notes' => 'E-learning platform for creative education. Completed project, pending final invoice.',
                'status' => 'active',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
