<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Pengembalian Barang Inventaris</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333333;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .container {
            max-width: 600px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #1e40af;
            color: #ffffff;
            padding: 24px 28px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #bfdbfe;
        }
        .content {
            padding: 28px;
            line-height: 1.6;
            font-size: 14px;
        }
        .greeting {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        .alert-box-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 18px 0;
            color: #92400e;
            font-size: 13px;
        }
        .alert-box-danger {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 18px 0;
            color: #991b1b;
            font-size: 13px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            margin: 20px 0 10px 0;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 6px;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .detail-table td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 13px;
        }
        .detail-table td.label {
            width: 40%;
            color: #6b7280;
            font-weight: 500;
        }
        .detail-table td.colon {
            width: 4%;
            color: #6b7280;
        }
        .detail-table td.value {
            color: #111827;
            font-weight: 600;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 20px 0;
            background-color: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table td {
            padding: 10px 12px;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 28px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    @php
        $indoMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $formatDateIndo = function($date) use ($indoMonths) {
            if (!$date) return '-';
            $c = \Carbon\Carbon::parse($date);
            return $c->format('d') . ' ' . ($indoMonths[(int)$c->format('m')] ?? $c->format('F')) . ' ' . $c->format('Y');
        };

        $referenceDate = isset($checkDate) && $checkDate ? \Carbon\Carbon::parse($checkDate)->startOfDay() : \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'))->startOfDay();
        $returnDate = \Carbon\Carbon::parse($borrowing->return_date)->startOfDay();
        $isOverdue = $returnDate->lessThan($referenceDate);
        $daysLate = $isOverdue ? $returnDate->diffInDays($referenceDate) : 0;
    @endphp

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>KECAMATAN CIKAMPEK</h1>
            <p>SIPAF - Sistem Informasi Pengelolaan Peminjaman Fasilitas</p>
        </div>

        <!-- Body Content -->
        <div class="content">
            @if(isset($isTest) && $isTest)
                <div style="background-color: #eff6ff; border: 2px dashed #2563eb; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; color: #1e40af; font-size: 13px; line-height: 1.5;">
                    <div style="font-weight: 700; font-size: 14px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <span>🔔 [PENGUJIAN SISTEM SIPAF KECAMATAN CIKAMPEK]</span>
                    </div>
                    <div>Email ini merupakan <strong>pengujian simulasi reminder</strong> pengembalian barang inventaris. Pengujian ini <strong>tidak mengubah status transaksi sebenarnya</strong> maupun data operasional di sistem SIPAF.</div>
                </div>
            @endif

            <div class="greeting">
                Yth. Bapak/Ibu {{ $borrowing->applicant_name }},
            </div>

            @if($isOverdue)
                <p style="margin: 0 0 14px 0;">
                    Kami ingin menginformasikan bahwa masa peminjaman barang inventaris yang Anda ajukan melalui Sistem Informasi Pengelolaan Peminjaman Fasilitas dan Pengajuan Sertifikat/Piagam (<strong>SIPAF</strong>) Kecamatan Cikampek <strong>telah melewati batas tanggal pengembalian</strong> yang telah ditentukan (terlambat {{ $daysLate }} hari{{ $borrowing->return_time ? ', batas: ' . \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') . ' WIB' : '' }}) dan hingga saat ini status barang belum tercatat dikembalikan.
                </p>

                <div class="alert-box-danger">
                    <strong>Pemberitahuan Keterlambatan:</strong> Peminjaman barang inventaris Anda telah melewati batas waktu pengembalian. Mohon segera melakukan pengembalian barang kepada petugas Kecamatan Cikampek hari ini agar inventaris dapat diperiksa kelengkapannya dan tidak mengganggu penggunaan oleh masyarakat lain.
                </div>
            @else
                <p style="margin: 0 0 14px 0;">
                    Kami ingin mengingatkan bahwa barang inventaris yang Anda pinjam melalui Sistem Informasi Pengelolaan Peminjaman Fasilitas dan Pengajuan Sertifikat/Piagam (<strong>SIPAF</strong>) Kecamatan Cikampek <strong>dijadwalkan untuk dikembalikan pada hari ini{{ $borrowing->return_time ? ' paling lambat pukul ' . \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') . ' WIB' : '' }}</strong>.
                </p>

                <div class="alert-box-warning">
                    <strong>Perhatian:</strong> Mohon segera melakukan proses pengembalian kepada petugas Kecamatan Cikampek hari ini sesuai jadwal yang telah disepakati agar inventaris dapat diperiksa kelengkapannya.
                </div>
            @endif

            <div class="section-title">Detail Peminjaman Inventaris</div>
            <table class="detail-table">
                <tr>
                    <td class="label">Kode Pengajuan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->request_code }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Pemohon</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->applicant_name }}</td>
                </tr>
                @if($borrowing->institution)
                <tr>
                    <td class="label">Instansi / Lembaga</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->institution }}</td>
                </tr>
                @endif
                @if($borrowing->penanggung_jawab)
                <tr>
                    <td class="label">Penanggung Jawab</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->penanggung_jawab }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Tanggal Peminjaman</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $formatDateIndo($borrowing->borrow_date) }}
                        @if($borrowing->borrowing_time)
                            (Pukul {{ \Carbon\Carbon::parse($borrowing->borrowing_time)->format('H:i') }} WIB)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pengembalian</td>
                    <td class="colon">:</td>
                    <td class="value" style="color: {{ $isOverdue ? '#b91c1c' : '#b45309' }};">
                        {{ $formatDateIndo($borrowing->return_date) }}
                        @if($isOverdue)
                            (Terlambat {{ $daysLate }} Hari)
                        @else
                            (Hari Ini)
                        @endif
                    </td>
                </tr>
                @if($borrowing->return_time)
                <tr>
                    <td class="label">Batas Waktu Pengembalian</td>
                    <td class="colon">:</td>
                    <td class="value" style="color: {{ $isOverdue ? '#b91c1c' : '#b45309' }};">
                        {{ \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') }} WIB
                        @if($isOverdue)
                            (Telah Melewati Batas Waktu)
                        @endif
                    </td>
                </tr>
                @endif
                <tr>
                    <td class="label">Status Saat Ini</td>
                    <td class="colon">:</td>
                    <td class="value">
                        <span style="color: #2563eb;">{{ $borrowing->status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Keperluan Peminjaman</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->purpose }}</td>
                </tr>
            </table>

            <div class="section-title">Daftar Barang yang Dipinjam</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 10%; text-align: center;">No</th>
                        <th style="width: 65%;">Nama Barang</th>
                        <th style="width: 25%; text-align: right;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowing->details as $index => $detail)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $detail->item->name ?? 'Barang Inventaris' }}</strong>
                            @if(isset($detail->item->category) && $detail->item->category)
                                <br><small style="color: #6b7280;">Kategori: {{ $detail->item->category }}</small>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 600;">{{ $detail->quantity }} Unit</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #6b7280;">Tidak ada detail barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <p style="margin: 16px 0 0 0;">
                Mohon segera melakukan pengembalian barang kepada petugas Kecamatan Cikampek sesuai jadwal yang telah ditentukan. Pastikan kondisi barang dalam keadaan bersih dan lengkap seperti saat diserahkan.
            </p>

            <p style="margin: 14px 0 0 0;">
                Terima kasih atas perhatian dan kerja sama Anda.
            </p>

            <div style="margin-top: 24px; border-top: 1px dashed #d1d5db; padding-top: 14px;">
                <p style="margin: 0; font-weight: 600; color: #111827;">Hormat kami,</p>
                <p style="margin: 2px 0 0 0; color: #4b5563;">Petugas Kecamatan Cikampek</p>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #6b7280;">SIPAF - Kecamatan Cikampek</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirimkan secara otomatis oleh sistem SIPAF Kecamatan Cikampek.</p>
            <p>&copy; {{ date('Y') }} Kantor Kecamatan Cikampek. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</body>
</html>
