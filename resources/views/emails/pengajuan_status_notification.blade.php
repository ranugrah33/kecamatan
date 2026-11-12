<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $status === 'Perlu Perbaikan' ? 'Pengajuan Memerlukan Perbaikan' : 'Pemberitahuan Penolakan Pengajuan' }} - SIPAF Kecamatan Cikampek</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #1f2937;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f6f9;
            padding: 20px 12px;
            box-sizing: border-box;
        }
        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }
        .header {
            padding: 24px 28px;
            text-align: center;
            color: #ffffff;
        }
        .header-revision {
            background-color: #b45309;
        }
        .header-rejected {
            background-color: #991b1b;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            opacity: 0.92;
        }
        .content {
            padding: 26px 28px;
            line-height: 1.65;
            font-size: 14px;
            color: #374151;
        }
        .greeting {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        .alert-box {
            padding: 14px 16px;
            border-radius: 8px;
            margin: 18px 0;
            font-size: 13.5px;
            line-height: 1.55;
        }
        .alert-revision {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        .alert-rejected {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        .note-box {
            border-radius: 8px;
            padding: 15px 17px;
            margin: 18px 0;
        }
        .note-box-revision {
            background-color: #fefce8;
            border: 1px solid #fde68a;
        }
        .note-box-rejected {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
        }
        .note-title {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .note-title-revision {
            color: #92400e;
        }
        .note-title-rejected {
            color: #9f1239;
        }
        .note-body {
            font-size: 13.5px;
            line-height: 1.6;
            color: #1f2937;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin: 22px 0 10px 0;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 6px;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .detail-table td {
            padding: 7px 0;
            vertical-align: top;
            font-size: 13px;
        }
        .detail-table td.label {
            width: 38%;
            color: #6b7280;
            font-weight: 500;
            padding-right: 8px;
        }
        .detail-table td.colon {
            width: 4%;
            color: #6b7280;
        }
        .detail-table td.value {
            width: 58%;
            color: #111827;
            font-weight: 600;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 9999px;
        }
        .badge-revision {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-rejected {
            background-color: #ffe4e6;
            color: #9f1239;
        }
        .guide-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px 17px;
            margin: 20px 0;
            font-size: 13px;
            color: #334155;
            line-height: 1.6;
        }
        .guide-box ol {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        .guide-box li {
            margin-bottom: 6px;
        }
        .button-wrapper {
            text-align: center;
            margin: 24px 0 10px 0;
        }
        .button {
            display: inline-block;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
        }
        .button-revision {
            background-color: #d97706;
        }
        .button-rejected {
            background-color: #b91c1c;
        }
        .secondary-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 12.5px;
            color: #2563eb;
            text-decoration: underline;
        }
        .signature {
            margin-top: 24px;
            border-top: 1px dashed #d1d5db;
            padding-top: 14px;
            font-size: 13px;
        }
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 18px 24px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .footer p {
            margin: 4px 0;
        }
        @media only screen and (max-width: 600px) {
            .wrapper {
                padding: 10px 6px !important;
            }
            .header {
                padding: 20px 18px !important;
            }
            .content {
                padding: 20px 18px !important;
            }
            .detail-table td.label {
                width: 42% !important;
            }
            .detail-table td.value {
                width: 54% !important;
            }
            .button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }
        }
    </style>
</head>
<body>
@php
    $isRevision = $status === 'Perlu Perbaikan';
@endphp
<div class="wrapper">
    <div class="container">
        <!-- Header -->
        <div class="header {{ $isRevision ? 'header-revision' : 'header-rejected' }}">
            <h1>SIPAF — Kecamatan Cikampek</h1>
            <p>Sistem Informasi Pelayanan Administrasi &amp; Fasilitas</p>
        </div>

        <!-- Body -->
        <div class="content">
            <div class="greeting">
                Yth. Bapak/Ibu {{ $applicantName }},
            </div>

            @if($isRevision)
                <p style="margin: 0 0 14px 0;">
                    Terima kasih telah mengajukan layanan <strong>{{ $serviceName }}</strong> melalui website <strong>SIPAF — Kecamatan Cikampek</strong>. Setelah diperiksa oleh petugas, pengajuan Anda saat ini <strong>memerlukan perbaikan</strong> sebelum dapat diproses ke tahap berikutnya.
                </p>

                <div class="alert-box alert-revision">
                    <strong>Status Pengajuan: Perlu Perbaikan.</strong><br>
                    Mohon meninjau catatan perbaikan dari petugas di bawah ini dan melakukan pengajuan kembali setelah data atau berkas diperbarui.
                </div>

                <div class="note-box note-box-revision">
                    <div class="note-title note-title-revision">Alasan / Catatan Perbaikan dari Admin:</div>
                    <div class="note-body">{!! nl2br(e($adminNote)) !!}</div>
                </div>
            @else
                <p style="margin: 0 0 14px 0;">
                    Terima kasih telah menggunakan layanan <strong>{{ $serviceName }}</strong> melalui website <strong>SIPAF — Kecamatan Cikampek</strong>. Dengan berat hati kami menginformasikan bahwa pengajuan Anda <strong>ditolak</strong> dan belum dapat diproses lebih lanjut.
                </p>

                <div class="alert-box alert-rejected">
                    <strong>Status Pengajuan: Ditolak.</strong><br>
                    Pengajuan dengan kode <strong>{{ $requestCode }}</strong> tidak dapat disetujui berdasarkan hasil verifikasi petugas.
                </div>

                <div class="note-box note-box-rejected">
                    <div class="note-title note-title-rejected">Alasan Penolakan dari Admin:</div>
                    <div class="note-body">{!! nl2br(e($adminNote)) !!}</div>
                </div>
            @endif

            <div class="section-title">Ringkasan Data Pengajuan</div>
            <table class="detail-table">
                <tr>
                    <td class="label">Kode Pengajuan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $requestCode }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Layanan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $serviceName }}</td>
                </tr>
                <tr>
                    <td class="label">Status Terbaru</td>
                    <td class="colon">:</td>
                    <td class="value">
                        <span class="badge {{ $isRevision ? 'badge-revision' : 'badge-rejected' }}">
                            {{ $status }}
                        </span>
                    </td>
                </tr>
                @foreach($details as $detail)
                    @if(!empty($detail['value']))
                    <tr>
                        <td class="label">{{ $detail['label'] }}</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $detail['value'] }}</td>
                    </tr>
                    @endif
                @endforeach
            </table>

            @if($isRevision)
                <div class="guide-box">
                    <strong style="display: block; margin-bottom: 4px; color: #1e293b;">Langkah Perbaikan &amp; Pengajuan Kembali:</strong>
                    <ol>
                        <li>Masuk ke akun SIPAF Anda dan buka halaman detail pengajuan (kode <strong>{{ $requestCode }}</strong>) untuk melihat informasi lengkapnya.</li>
                        <li>Lengkapi atau perbaiki data maupun dokumen pendukung sesuai catatan admin di atas.</li>
                        <li>Ajukan kembali permohonan layanan <strong>{{ $serviceName }}</strong> melalui menu layanan terkait di SIPAF Kecamatan Cikampek.</li>
                    </ol>
                </div>

                <div class="button-wrapper">
                    <a href="{{ $actionUrl }}" class="button button-revision" target="_blank">
                        Lihat Detail Pengajuan di SIPAF
                    </a>
                    @if(!empty($resubmitUrl))
                        <br>
                        <a href="{{ $resubmitUrl }}" class="secondary-link" target="_blank">
                            Atau langsung buat pengajuan perbaikan baru
                        </a>
                    @endif
                </div>
            @else
                @if(!empty($followUpInfo))
                <div class="guide-box">
                    <strong style="display: block; margin-bottom: 4px; color: #1e293b;">Informasi Tindak Lanjut:</strong>
                    <p style="margin: 0;">{{ $followUpInfo }}</p>
                </div>
                @endif

                <div class="button-wrapper">
                    <a href="{{ $actionUrl }}" class="button button-rejected" target="_blank">
                        Buka Detail Pengajuan di SIPAF
                    </a>
                </div>
            @endif

            <div class="signature">
                <p style="margin: 0; font-weight: 600; color: #111827;">Hormat kami,</p>
                <p style="margin: 2px 0 0 0; color: #4b5563;">Petugas Pelayanan Kecamatan Cikampek</p>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #6b7280;">SIPAF — Kecamatan Cikampek</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>SIPAF — Kecamatan Cikampek</strong></p>
            <p>Jl. Ir. H. Juanda No. 12, Cikampek, Kabupaten Karawang, Jawa Barat</p>
            <p style="margin-top: 8px; color: #9ca3af; font-size: 11px;">
                Email ini dikirim secara otomatis oleh sistem SIPAF Kecamatan Cikampek. Mohon untuk tidak membalas email ini secara langsung.
            </p>
        </div>
    </div>
</div>
</body>
</html>

