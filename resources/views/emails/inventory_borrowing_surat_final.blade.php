<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Persetujuan Peminjaman Inventaris Telah Terbit — SIPAF Kecamatan Cikampek</title>
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
            background-color: #065f46;
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
            margin-bottom: 8px;
        }
        .salutation {
            font-size: 14px;
            font-weight: 600;
            color: #047857;
            margin-bottom: 12px;
        }
        .alert-box-success {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 14px 18px;
            border-radius: 6px;
            margin: 18px 0;
            color: #065f46;
            font-size: 13px;
            line-height: 1.5;
        }
        .alert-box-revision {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 6px;
            margin: 18px 0;
            color: #92400e;
            font-size: 13px;
            line-height: 1.5;
        }
        .guide-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px 18px;
            margin: 20px 0;
            color: #166534;
            font-size: 13px;
            line-height: 1.6;
        }
        .guide-box ol {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }
        .guide-box li {
            margin-bottom: 8px;
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
        .btn-container {
            text-align: center;
            margin: 26px 0 16px 0;
        }
        .btn {
            display: inline-block;
            background-color: #047857;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 28px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>KECAMATAN CIKAMPEK</h1>
            <p>Sistem Informasi Pengelolaan Peminjaman Fasilitas dan Pengajuan Sertifikat/Piagam (SIPAF)</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Yth. Bapak/Ibu <strong>{{ $borrowing->applicant_name }}</strong>,
            </div>
            <div class="salutation">
                Sampurasun!
            </div>

            @if($document && $document->version > 1)
                <div class="alert-box-revision">
                    <strong>Pemberitahuan Pembaruan Dokumen:</strong><br>
                    Surat persetujuan peminjaman inventaris resmi untuk pengajuan Anda telah diperbarui ke versi {{ $document->version }}. Dokumen versi terbaru telah ditandatangani Camat Cikampek / pejabat berwenang dan kini siap diunduh melalui akun SIPAF Anda.
                </div>
            @else
                <div class="alert-box-success">
                    <strong>Permohonan Peminjaman Disetujui!</strong><br>
                    Kami informasikan bahwa permohonan peminjaman barang/inventaris Anda telah disetujui oleh Kecamatan Cikampek. Surat persetujuan resmi yang telah ditandatangani oleh Camat Cikampek sudah tersedia dan dapat diunduh melalui akun SIPAF Anda.
                </div>
            @endif

            <p style="margin: 0 0 14px 0; color: #374151;">
                Berikut adalah rincian informasi peminjaman barang inventaris Anda:
            </p>

            <div class="section-title">Informasi Peminjaman</div>
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
                @if($document && $document->nomor_surat)
                <tr>
                    <td class="label">Nomor Surat Persetujuan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $document->nomor_surat }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Tanggal Peminjaman</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $borrowing->borrow_date->translatedFormat('d F Y') }}
                        @if($borrowing->formatted_borrowing_time)
                            (Pukul {{ $borrowing->formatted_borrowing_time }} WIB)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pengembalian</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $borrowing->return_date->translatedFormat('d F Y') }}
                        @if($borrowing->formatted_return_time)
                            (Pukul {{ $borrowing->formatted_return_time }} WIB)
                        @endif
                    </td>
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
                @if($borrowing->purpose)
                <tr>
                    <td class="label">Keperluan</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $borrowing->purpose }}</td>
                </tr>
                @endif
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
                            <strong>{{ $detail->item->name ?? '-' }}</strong>
                            @if(isset($detail->item->category) && $detail->item->category)
                                <br><small style="color: #6b7280;">Kategori: {{ $detail->item->category }}</small>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #047857;">{{ $detail->quantity }} unit</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #6b7280;">Tidak ada detail barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="guide-box">
                <strong style="display: block; font-size: 14px; margin-bottom: 8px; color: #065f46;">
                    LANGKAH PENGAMBILAN BARANG:
                </strong>
                <ol>
                    <li><strong>Login ke SIPAF:</strong> Silakan login ke website resmi SIPAF Kecamatan Cikampek menggunakan akun Anda.</li>
                    <li><strong>Buka menu Peminjaman Inventaris:</strong> Masuk ke menu riwayat peminjaman barang Anda.</li>
                    <li><strong>Buka detail transaksi:</strong> Pilih transaksi peminjaman dengan kode <strong>{{ $borrowing->request_code }}</strong>.</li>
                    <li><strong>Unduh surat persetujuan final:</strong> Unduh berkas Surat Persetujuan resmi yang telah ditandatangani Camat Cikampek.</li>
                    <li><strong>Datang ke Kantor Kecamatan Cikampek sesuai jadwal peminjaman:</strong> Datang langsung ke kantor kecamatan sesuai jadwal tanggal dan waktu peminjaman yang telah disetujui.</li>
                    <li><strong>Bawa atau tunjukkan surat persetujuan kepada petugas sebagai bukti pengambilan barang:</strong> Harap membawa atau menunjukkan surat persetujuan peminjaman kepada petugas di lokasi sebagai bukti saat pengambilan barang.</li>
                </ol>
            </div>

            <p style="margin: 14px 0; color: #374151; font-size: 13px;">
                Mohon lakukan pengambilan barang di Kantor Kecamatan Cikampek sesuai jadwal peminjaman yang telah disetujui. Pastikan Anda membawa atau menunjukkan surat persetujuan sebagai bukti pengambilan barang kepada petugas.
            </p>

            <div class="btn-container">
                <a href="{{ route('masyarakat.peminjaman_inventaris.show', $borrowing->id) }}" class="btn">
                    Lihat Peminjaman &amp; Unduh Surat di SIPAF
                </a>
            </div>

            <p style="margin-top: 18px; color: #6b7280; font-size: 12px; font-style: italic;">
                * Berkas fisik Surat Persetujuan resmi juga telah kami lampirkan pada email ini untuk kemudahan Anda.
            </p>

            <p style="margin-top: 24px; font-size: 13px; color: #374151;">
                Demikian pemberitahuan ini kami sampaikan. Terima kasih atas perhatian dan kerja samanya.
            </p>

            <div style="margin-top: 20px; border-top: 1px dashed #d1d5db; padding-top: 14px;">
                <p style="margin: 0; font-weight: 600; color: #111827;">Hormat kami,</p>
                <p style="margin: 2px 0 0 0; font-weight: bold; color: #065f46;">Kecamatan Cikampek</p>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #6b7280;">Sistem Informasi Pengelolaan Peminjaman Fasilitas dan Pengajuan Sertifikat/Piagam (SIPAF)</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">
                Email ini dikirim otomatis oleh Sistem Informasi Pengelolaan Peminjaman Fasilitas dan Pengajuan Sertifikat/Piagam (SIPAF) Kecamatan Cikampek.<br>
                Jl. A.Yani No. 105 Dawuan Tengah, Cikampek, Karawang, Jawa Barat.
            </p>
        </div>
    </div>
</body>
</html>
