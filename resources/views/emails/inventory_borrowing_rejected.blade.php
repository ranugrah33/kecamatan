<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Penolakan Peminjaman Barang Inventaris</title>
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
            background-color: #991b1b;
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
            color: #fecaca;
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
        .alert-box-danger {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 14px 18px;
            border-radius: 6px;
            margin: 18px 0;
            color: #991b1b;
            font-size: 13px;
            line-height: 1.5;
        }
        .rejection-box {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 18px 0;
        }
        .rejection-box-title {
            font-weight: 700;
            color: #9f1239;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .rejection-box-body {
            color: #4c0519;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-line;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 20px 0;
            color: #334155;
            font-size: 13px;
            line-height: 1.6;
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
        .badge-status {
            display: inline-block;
            background-color: #ffe4e6;
            color: #9f1239;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 9999px;
            font-size: 12px;
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
    @endphp

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>KECAMATAN CIKAMPEK</h1>
            <p>SIPAF - Sistem Informasi Pengelolaan Peminjaman Fasilitas</p>
        </div>

        <!-- Body Content -->
        <div class="content">
            <div class="greeting">
                Yth. Bapak/Ibu {{ $borrowing->applicant_name }},
            </div>

            <p style="margin: 0 0 14px 0;">
                Terima kasih telah mengajukan permohonan peminjaman barang inventaris melalui Sistem Informasi Pengelolaan Peminjaman Fasilitas dan Pengajuan Sertifikat/Piagam (<strong>SIPAF</strong>) Kecamatan Cikampek.
            </p>

            <div class="alert-box-danger">
                <strong>Pemberitahuan Status Penolakan:</strong> Mohon maaf, permohonan peminjaman barang inventaris Anda dengan kode transaksi <strong>{{ $borrowing->request_code }}</strong> tidak dapat disetujui oleh pihak Kecamatan Cikampek.
            </div>

            <div class="rejection-box">
                <div class="rejection-box-title">Alasan Penolakan:</div>
                <div class="rejection-box-body">
@if(!empty(trim($borrowing->rejection_reason ?? '')))
{{ $borrowing->rejection_reason }}
@else
<em>Tidak ada catatan alasan penolakan khusus yang dicantumkan oleh petugas kecamatan.</em>
@endif
                </div>
            </div>

            <div class="section-title">Detail Pengajuan Peminjaman</div>
            <table class="detail-table">
                <tr>
                    <td class="label">Kode Transaksi</td>
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
                    <td class="label">Tanggal Peminjaman Diajukan</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $formatDateIndo($borrowing->borrow_date) }}
                        @if($borrowing->borrowing_time)
                            (Pukul {{ \Carbon\Carbon::parse($borrowing->borrowing_time)->format('H:i') }} WIB)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Tanggal Rencana Pengembalian</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $formatDateIndo($borrowing->return_date) }}
                        @if($borrowing->return_time)
                            (Pukul {{ \Carbon\Carbon::parse($borrowing->return_time)->format('H:i') }} WIB)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Status Saat Ini</td>
                    <td class="colon">:</td>
                    <td class="value">
                        <span class="badge-status">Ditolak</span>
                    </td>
                </tr>
                @if($borrowing->purpose)
                <tr>
                    <td class="label">Keperluan Peminjaman</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->purpose }}</td>
                </tr>
                @endif
            </table>

            <div class="section-title">Daftar Barang yang Diajukan</div>
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

            <div class="info-box">
                <strong style="display: block; margin-bottom: 4px; color: #1e293b;">Informasi Lebih Lanjut:</strong>
                <p style="margin: 0 0 6px 0;">
                    Apabila Anda membutuhkan klarifikasi atau penjelasan lebih lanjut mengenai penolakan pengajuan ini, atau ingin berkonsultasi mengenai ketersediaan jadwal serta barang inventaris di waktu mendatang, silakan menghubungi petugas di Kantor Kecamatan Cikampek pada jam kerja operasional.
                </p>
                <p style="margin: 0;">
                    Anda juga dapat mengajukan permohonan baru di lain waktu melalui website SIPAF sesuai dengan ketersediaan fasilitas yang ada.
                </p>
            </div>

            <p style="margin: 14px 0 0 0;">
                Demikian informasi ini kami sampaikan. Terima kasih atas pengertian dan kerja sama Anda.
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

