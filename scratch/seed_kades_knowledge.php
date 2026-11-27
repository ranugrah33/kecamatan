<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiKnowledge;

$entries = [
    // 1. Overview 10 Desa & Kepala Desa
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Daftar 10 Kepala Desa di Kecamatan Cikampek',
        'question' => 'Siapa saja nama kepala desa di Kecamatan Cikampek? Daftar nama kepala desa di 10 desa Cikampek.',
        'answer' => "Berikut adalah daftar nama Kepala Desa di 10 Desa wilayah Kecamatan Cikampek:\n\n" .
            "1. **Cikampek Barat**: Hj. Yuyun Yunengsih, A.Md.\n" .
            "2. **Cikampek Kota**: Ahmad Nurdin\n" .
            "3. **Cikampek Pusaka**: Pendi Supendi (tercantum pada profil desa; informasi pembaruan terkini belum tercantum)\n" .
            "4. **Cikampek Selatan**: Edi Suparjono (dilantik 16 Maret 2026, masa bakti 2026–2034)\n" .
            "5. **Cikampek Timur**: H. Samsul Bakhri (terpilih Pilkades Digital 22 November 2026 untuk periode 2026–2034, menggantikan petahana Kriswanto, A.Md.)\n" .
            "6. **Dawuan Barat**: Aang Khoerul Hak, S.Kep. (menjabat sebagai Pj. Kepala Desa sejak Agustus 2026)\n" .
            "7. **Dawuan Tengah**: Jejen Jaenal Arifin, S.T.\n" .
            "8. **Dawuan Timur**: Tatang Somantri\n" .
            "9. **Kalihurip**: Herman (terpilih dalam Pilkades Digital 22 November 2026)\n" .
            "10. **Kamojing**: Cahyadi / Chahyadi (tercatat sebagai kepala desa pada profil desa, status jabatan definitif/Pj terkini dalam pembaruan)\n\n" .
            "Untuk informasi administrasi desa lebih lanjut, masyarakat dapat menghubungi kantor desa setempat atau Kantor Kecamatan Cikampek.",
        'keywords' => 'kepala desa, kades, lurah, kuwu, daftar kades, nama kepala desa, 10 desa, sepuluh desa, cikampek barat, cikampek kota, cikampek pusaka, cikampek selatan, cikampek timur, dawuan barat, dawuan tengah, dawuan timur, kalihurip, kamojing, pimpinan desa, pemerintahan desa',
        'priority' => 100,
        'is_active' => true,
    ],

    // 2. Cikampek Barat
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Cikampek Barat: Hj. Yuyun Yunengsih, A.Md.',
        'question' => 'Siapa nama kepala desa dari Cikampek Barat? Siapa kades Cikampek Barat?',
        'answer' => "Kepala Desa Cikampek Barat saat ini adalah **Hj. Yuyun Yunengsih, A.Md.**\n\nDesa Cikampek Barat merupakan salah satu dari 10 desa di wilayah Kecamatan Cikampek, Kabupaten Karawang.",
        'keywords' => 'kepala desa cikampek barat, kades cikampek barat, lurah cikampek barat, yuyun yunengsih, hj yuyun yunengsih, cikampek barat kades',
        'priority' => 95,
        'is_active' => true,
    ],

    // 3. Cikampek Kota
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Cikampek Kota: Ahmad Nurdin',
        'question' => 'Siapa nama kepala desa dari Cikampek Kota? Siapa kades Cikampek Kota?',
        'answer' => "Kepala Desa Cikampek Kota saat ini adalah **Ahmad Nurdin** (tercatat resmi dalam profil desa www.cikampek-kota.id).\n\nDesa Cikampek Kota merupakan salah satu pusat aktivitas pemerintahan dan perekonomian desa di Kecamatan Cikampek.",
        'keywords' => 'kepala desa cikampek kota, kades cikampek kota, lurah cikampek kota, ahmad nurdin, cikampek kota kades, siapa kades cikampek kota',
        'priority' => 95,
        'is_active' => true,
    ],

    // 4. Cikampek Pusaka
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Cikampek Pusaka: Pendi Supendi',
        'question' => 'Siapa nama kepala desa dari Cikampek Pusaka? Siapa kades Cikampek Pusaka?',
        'answer' => "Kepala Desa Cikampek Pusaka yang tercantum pada profil desa resmi adalah **Pendi Supendi**.\n\nDesa Cikampek Pusaka merupakan salah satu desa di wilayah Kecamatan Cikampek, Kabupaten Karawang.",
        'keywords' => 'kepala desa cikampek pusaka, kades cikampek pusaka, pendi supendi, lurah cikampek pusaka, cikampek pusaka kades',
        'priority' => 95,
        'is_active' => true,
    ],

    // 5. Cikampek Selatan
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Cikampek Selatan: Edi Suparjono',
        'question' => 'Siapa nama kepala desa dari Cikampek Selatan? Siapa kades Cikampek Selatan?',
        'answer' => "Kepala Desa Cikampek Selatan saat ini adalah **Edi Suparjono** (dilantik pada 16 Maret 2026 untuk masa bakti 2026–2034).\n\nDesa Cikampek Selatan merupakan salah satu dari 10 desa di wilayah Kecamatan Cikampek.",
        'keywords' => 'kepala desa cikampek selatan, kades cikampek selatan, edi suparjono, lurah cikampek selatan, cikampek selatan kades, masa bakti 2026 2034',
        'priority' => 95,
        'is_active' => true,
    ],

    // 6. Cikampek Timur
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Cikampek Timur: H. Samsul Bakhri',
        'question' => 'Siapa nama kepala desa dari Cikampek Timur? Siapa kades Cikampek Timur?',
        'answer' => "Kepala Desa Cikampek Timur adalah **H. Samsul Bakhri**, yang terpilih dalam Pilkades Digital pada 22 November 2026 untuk masa bakti 2026–2034 (menggantikan petahana Kriswanto, A.Md.).\n\nDesa Cikampek Timur berada di wilayah Kecamatan Cikampek, Kabupaten Karawang.",
        'keywords' => 'kepala desa cikampek timur, kades cikampek timur, h samsul bakhri, samsul bakhri, kriswanto, pilkades cikampek timur, lurah cikampek timur',
        'priority' => 95,
        'is_active' => true,
    ],

    // 7. Dawuan Barat
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Dawuan Barat: Aang Khoerul Hak, S.Kep.',
        'question' => 'Siapa nama kepala desa dari Dawuan Barat? Siapa kades Dawuan Barat?',
        'answer' => "Pimpinan Kepala Desa Dawuan Barat saat ini dijabat oleh **Aang Khoerul Hak, S.Kep.** sebagai Penjabat (Pj.) Kepala Desa sejak serah terima jabatan pada Agustus 2026 (dabar.id).\n\nDesa Dawuan Barat merupakan salah satu wilayah desa di Kecamatan Cikampek.",
        'keywords' => 'kepala desa dawuan barat, kades dawuan barat, pj kades dawuan barat, aang khoerul hak, aang khoerul haq, lurah dawuan barat, dabar',
        'priority' => 95,
        'is_active' => true,
    ],

    // 8. Dawuan Tengah
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Dawuan Tengah: Jejen Jaenal Arifin, S.T.',
        'question' => 'Siapa nama kepala desa dari Dawuan Tengah? Siapa kades Dawuan Tengah?',
        'answer' => "Kepala Desa Dawuan Tengah saat ini adalah **Jejen Jaenal Arifin, S.T.**\n\nDesa Dawuan Tengah merupakan lokasi kantor Kecamatan Cikampek berada (Jl. Jendral Ahmad Yani No. 105, Dawuan Tengah, Cikampek).",
        'keywords' => 'kepala desa dawuan tengah, kades dawuan tengah, jejen jaenal arifin, jejen jaenal, lurah dawuan tengah, dawuan tengah kades',
        'priority' => 95,
        'is_active' => true,
    ],

    // 9. Dawuan Timur
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Dawuan Timur: Tatang Somantri',
        'question' => 'Siapa nama kepala desa dari Dawuan Timur? Siapa kades Dawuan Timur?',
        'answer' => "Kepala Desa Dawuan Timur saat ini adalah **Tatang Somantri** (tercatat resmi dalam data www.datim.id).\n\nDesa Dawuan Timur merupakan salah satu dari 10 desa di wilayah Kecamatan Cikampek.",
        'keywords' => 'kepala desa dawuan timur, kades dawuan timur, tatang somantri, datim, lurah dawuan timur, dawuan timur kades',
        'priority' => 95,
        'is_active' => true,
    ],

    // 10. Kalihurip
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Kalihurip: Herman',
        'question' => 'Siapa nama kepala desa dari Kalihurip? Siapa kades Kalihurip?',
        'answer' => "Kepala Desa Kalihurip terpilih adalah **Herman**, yang terpilih dalam pemilihan kepala desa (Pilkades Digital) pada 22 November 2026.\n\nDesa Kalihurip merupakan kawasan industri dan pemukiman penting di wilayah Kecamatan Cikampek.",
        'keywords' => 'kepala desa kalihurip, kades kalihurip, herman, pilkades kalihurip, lurah kalihurip, kalihurip kades',
        'priority' => 95,
        'is_active' => true,
    ],

    // 11. Kamojing
    [
        'category' => 'Profil Kecamatan',
        'title' => 'Kepala Desa Kamojing: Cahyadi / Chahyadi',
        'question' => 'Siapa nama kepala desa dari Kamojing? Siapa kades Kamojing?',
        'answer' => "Kepala Desa Kamojing yang tercatat pada profil desa resmi adalah **Cahyadi / Chahyadi** (desakamojing.com).\n\n*Catatan*: Terkait status kepemimpinan definitif atau Pelaksana Tugas (Pj.) terkini, informasi pembaruan resmi masih dalam sinkronisasi administrasi. Desa Kamojing terkenal dengan keindahan wisata alam Situ Kamojing di Kecamatan Cikampek.",
        'keywords' => 'kepala desa kamojing, kades kamojing, cahyadi, chahyadi, lurah kamojing, kamojing kades, situ kamojing',
        'priority' => 95,
        'is_active' => true,
    ],
];

echo "Seeding " . count($entries) . " Kepala Desa knowledge entries into ai_knowledges table...\n";

foreach ($entries as $data) {
    $knowledge = AiKnowledge::updateOrCreate(
        ['title' => $data['title']],
        $data
    );
    echo "Saved: [ID {$knowledge->id}] {$knowledge->title}\n";
}

echo "Total AI Knowledges in DB: " . AiKnowledge::count() . "\n";
