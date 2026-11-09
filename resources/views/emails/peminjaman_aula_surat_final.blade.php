<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Peminjaman Aula Tersedia</title>
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
            background-color: #047857;
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
            color: #a7f3d0;
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
        .alert-box-success {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 18px 0;
            color: #065f46;
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
            width: 38%;
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
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 9999px;
            background-color: #d1fae5;
            color: #065f46;
        }
        .button-wrapper {
            text-align: center;
            margin: 24px 0 12px 0;
        }
        .button {
            display: inline-block;
            background-color: #059669;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .button:hover {
            background-color: #047857;
        }
        .notes {
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 12px;
            color: #4b5563;
            margin-top: 18px;
        }
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 28px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SIPAF KECAMATAN CIKAMPEK</h1>
            <p>Sistem Informasi Pelayanan Administrasi & Fasilitas</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Yth. Bapak/Ibu {{ $peminjaman->user->name ?? 'Pemohon' }},
            </div>

            <p>
                Pemberitahuan resmi mengenai pengajuan peminjaman aula yang Anda ajukan melalui sistem SIPAF Kecamatan Cikampek:
            </p>

            <div class="alert-box-success">
                <strong>Status Terkini:</strong> Surat Izin Peminjaman Aula telah ditandatangani dan berkas final resmi telah tersedia untuk diunduh.
            </div>

            <div class="section-title">Detail Pengajuan Peminjaman Aula</div>
            <table class="detail-table">
                <tr>
                    <td class="label">Nomor Pengajuan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peminjaman->nomor_pengajuan }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="colon">:</td>
                    <td class="value"><span class="badge">Surat Tersedia (Final)</span></td>
                </tr>
                @if(!empty($peminjaman->nomor_surat_izin))
                <tr>
                    <td class="label">Nomor Surat Izin</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peminjaman->nomor_surat_izin }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Nama Kegiatan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peminjaman->nama_kegiatan }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Kegiatan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peminjaman->jenis_kegiatan }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Kegiatan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ \Carbon\Carbon::parse($peminjaman->tanggal)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Pelaksanaan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ substr($peminjaman->jam_mulai, 0, 5) }} - {{ substr($peminjaman->jam_selesai, 0, 5) }} WIB</td>
                </tr>
                <tr>
                    <td class="label">Lokasi</td>
                    <td class="colon">:</td>
                    <td class="value">Aula Kantor Kecamatan Cikampek</td>
                </tr>
                @if(!empty($peminjaman->penanggung_jawab))
                <tr>
                    <td class="label">Penanggung Jawab</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peminjaman->penanggung_jawab }}</td>
                </tr>
                @endif
            </table>

            <div class="button-wrapper">
                <a href="{{ route('masyarakat.peminjaman_aula.show', $peminjaman->id) }}" class="button" target="_blank">
                    Buka Detail & Download Surat Izin
                </a>
            </div>

            <div class="notes">
                <strong>Catatan Penting:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 20px;">
                    <li>Surat Izin Peminjaman Aula ini sah dan resmi ditandatangani oleh pejabat Kecamatan Cikampek.</li>
                    <li>File surat izin juga terlampir bersama email pemberitahuan ini (jika format didukung).</li>
                    <li>Harap tunjukkan dokumen surat izin fisik/digital ini kepada petugas piket/pengelola aula pada hari pelaksanaan.</li>
                    <li>Jaga kebersihan, ketertiban, dan fasilitas aula selama kegiatan berlangsung.</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Pemerintah Kecamatan Cikampek</strong></p>
            <p>Jl. Ir. H. Juanda No. 12, Cikampek, Kabupaten Karawang, Jawa Barat</p>
            <p style="margin-top: 10px; color: #9ca3af; font-size: 11px;">
                Email ini dikirimkan secara otomatis oleh Sistem SIPAF Kecamatan Cikampek. Mohon untuk tidak membalas email ini secara langsung.
            </p>
        </div>
    </div>
</body>
</html>

