<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profil Desa {{ $desa['nama'] }} - Kecamatan Cikampek</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.4;
            color: #1a202c;
            background: #ffffff;
            margin: 0;
            padding: 0;
            font-size: 11pt;
        }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            margin-bottom: 2px;
            border-collapse: collapse;
        }
        .kop-logo-container {
            width: 15%;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo {
            width: 70px;
            height: auto;
        }
        .kop-text {
            width: 85%;
            text-align: center;
            vertical-align: middle;
            padding-right: 15%;
        }
        .kop-text h1 {
            font-size: 14pt;
            font-weight: normal;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text h2 {
            font-size: 17pt;
            font-weight: bold;
            margin: 2px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-text p {
            font-size: 10.5pt;
            margin: 1px 0 0 0;
        }

        .kop-border {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-bottom: 14px;
            margin-top: 6px;
        }

        /* Document Header */
        .doc-header {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-badge {
            display: inline-block;
            background: #064e3b;
            color: #ffffff;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            padding: 3px 12px;
            border-radius: 4px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .doc-title {
            font-size: 15pt;
            font-weight: bold;
            color: #064e3b;
            margin: 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .doc-subtitle {
            font-size: 10pt;
            color: #4b5563;
            margin: 0;
        }

        /* Section Block */
        .section-box {
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            border-bottom: 1.5px solid #059669;
            padding-bottom: 3px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        /* Box Pimpinan / Kades */
        .kades-box {
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 4px solid #059669;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 3px;
        }
        .kades-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kades-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .kades-label {
            width: 150px;
            font-weight: bold;
            color: #064e3b;
        }
        .kades-colon {
            width: 15px;
            font-weight: bold;
        }
        .kades-value {
            color: #1f2937;
        }

        /* Stat Grid / Table */
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .stat-table td {
            width: 50%;
            padding: 5px 8px;
            border: 1px solid #d1fae5;
            background: #fafdfb;
            vertical-align: top;
        }
        .stat-label {
            font-size: 9pt;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .stat-val {
            font-size: 12pt;
            font-weight: bold;
            color: #064e3b;
        }
        .stat-sub {
            font-size: 8.5pt;
            color: #4b5563;
            margin-top: 1px;
        }

        /* Text Content */
        .text-desc {
            font-size: 10pt;
            line-height: 1.45;
            text-align: justify;
            color: #374151;
            margin: 0 0 4px 0;
        }

        /* Facilities Chips / List */
        .facility-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .facility-table td {
            width: 50%;
            padding: 3px 6px;
            font-size: 9.5pt;
            color: #1f2937;
            vertical-align: middle;
        }
        .facility-bullet {
            color: #059669;
            font-weight: bold;
            margin-right: 4px;
        }

        /* Catatan Penutup Dokumen */
        .doc-footer-note {
            margin-top: 18px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            font-size: 8.5pt;
            color: #64748b;
            line-height: 1.4;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Resmi Kecamatan Cikampek -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo-container">
                <img src="{{ public_path('images/kop-surat.png') }}" alt="Logo Kabupaten Karawang" class="kop-logo" onerror="this.style.display='none'">
            </td>
            <td class="kop-text">
                <h1>PEMERINTAH KABUPATEN KARAWANG</h1>
                <h2>KECAMATAN CIKAMPEK</h2>
                <p>Jalan A.Yani 105 Dawuan Tengah Cikampek</p>
                <p>Laman : Cikampek.karawangkab.go.id</p>
            </td>
        </tr>
    </table>
    <div class="kop-border"></div>

    <!-- Header Dokumen Profil -->
    <div class="doc-header">
        <span class="doc-badge">DOKUMEN RESMI STATISTIK & PROFIL WILAYAH</span>
        <div class="doc-title">DESA {{ strtoupper($desa['nama']) }}</div>
        <p class="doc-subtitle">Kecamatan Cikampek, Kabupaten Karawang &bull; {{ $desa['kategoriLabel'] }} (Kode Pos: {{ $desa['kodePos'] }})</p>
    </div>

    <!-- Informasi Pimpinan Desa / Kepala Desa -->
    <div class="section-box">
        <div class="section-title">I. Pimpinan & Pemerintahan Desa</div>
        <div class="kades-box">
            <table class="kades-table">
                <tr>
                    <td class="kades-label">Kepala Desa</td>
                    <td class="kades-colon">:</td>
                    <td class="kades-value"><strong>{{ $desa['kades'] }}</strong></td>
                </tr>
                <tr>
                    <td class="kades-label">Status Jabatan</td>
                    <td class="kades-colon">:</td>
                    <td class="kades-value">{{ $desa['jabatan'] }}</td>
                </tr>
                <tr>
                    <td class="kades-label">Status Kemandirian</td>
                    <td class="kades-colon">:</td>
                    <td class="kades-value"><span style="color: #065f46; font-weight: bold;">{{ $desa['status'] }}</span> (Indeks Desa Membangun)</td>
                </tr>
                @if(!empty($desa['kadesCatatan']))
                <tr>
                    <td class="kades-label">Keterangan / Catatan</td>
                    <td class="kades-colon">:</td>
                    <td class="kades-value" style="font-style: italic; color: #4b5563;">{{ $desa['kadesCatatan'] }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <!-- Indikator Geografis & Demografi -->
    <div class="section-box">
        <div class="section-title">II. Indikator Geografis & Demografi (BPS Karawang 2025)</div>
        <table class="stat-table">
            <tr>
                <td>
                    <div class="stat-label">Luas Wilayah</div>
                    <div class="stat-val">{{ $desa['luas'] }}</div>
                    <div class="stat-sub">{{ $desa['persentaseLuas'] }} dari total luas Kecamatan Cikampek</div>
                </td>
                <td>
                    <div class="stat-label">Jumlah Penduduk</div>
                    <div class="stat-val">{{ $desa['penduduk'] }}</div>
                    <div class="stat-sub">Laki-laki: {{ $desa['pendudukLaki'] }} &bull; Perempuan: {{ $desa['pendudukPerempuan'] }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="stat-label">Kode Pos Wilayah</div>
                    <div class="stat-val">{{ $desa['kodePos'] }}</div>
                    <div class="stat-sub">Wilayah Hukum Pos Cikampek</div>
                </td>
                <td>
                    <div class="stat-label">Klaster / Karakteristik Wilayah</div>
                    <div class="stat-val" style="font-size: 11pt;">{{ $desa['kategoriLabel'] }}</div>
                    <div class="stat-sub">Sektor unggulan penggerak perekonomian desa</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Profil & Gambaran Wilayah -->
    <div class="section-box">
        <div class="section-title">III. Profil & Gambaran Wilayah</div>
        <p class="text-desc">{{ $desa['profil'] }}</p>
    </div>

    <!-- Potensi Utama Wilayah -->
    <div class="section-box">
        <div class="section-title">IV. Potensi Utama Wilayah</div>
        <p class="text-desc">{{ $desa['potensi'] }}</p>
    </div>

    <!-- Fasilitas & Layanan Publik Unggulan -->
    <div class="section-box">
        <div class="section-title">V. Fasilitas & Layanan Publik Unggulan</div>
        <table class="facility-table">
            @php
                $chunks = array_chunk($desa['fasilitas'], 2);
            @endphp
            @foreach($chunks as $row)
            <tr>
                @foreach($row as $item)
                <td><span class="facility-bullet">&#10003;</span> {{ $item }}</td>
                @endforeach
                @if(count($row) === 1)
                <td></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Catatan Resmi Penutup Dokumen -->
    <div class="doc-footer-note">
        <strong>Catatan Resmi:</strong> Lembar profil dan data statistik desa ini diterbitkan secara digital melalui Sistem Informasi Terpadu Pelayanan Publik Kecamatan Cikampek, Kabupaten Karawang berdasarkan data kompilasi resmi kecamatan dan publikasi BPS Kabupaten Karawang.
    </div>

</body>
</html>
