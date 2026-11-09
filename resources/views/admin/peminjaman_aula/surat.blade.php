<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Persetujuan Peminjaman Aula - {{ $peminjaman->nomor_pengajuan }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 25mm 20mm 25mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.5;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 12pt;
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
            width: 75px;
            height: auto;
        }
        .kop-text {
            width: 85%;
            text-align: center;
            vertical-align: middle;
            padding-right: 15%; /* Mengimbangi logo di kiri agar teks tepat di tengah kertas */
        }
        .kop-text h1 {
            font-size: 15pt;
            font-weight: normal;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text h2 {
            font-size: 18pt;
            font-weight: bold;
            margin: 2px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-text p {
            font-size: 11pt;
            margin: 2px 0 0 0;
        }

        .kop-surat-border {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-bottom: 16px;
            margin-top: 8px;
        }

        /* Tanggal Surat (Kanan Atas) */
        .tanggal-surat {
            text-align: right;
            margin-bottom: 12px;
            font-size: 12pt;
        }

        /* Nomor, Sifat, Lampiran, Perihal (Kiri) */
        .meta-surat {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .meta-label {
            width: 85px;
            padding: 1px 0;
            vertical-align: top;
            font-size: 12pt;
        }
        .meta-colon {
            width: 18px;
            padding: 1px 0;
            vertical-align: top;
            font-size: 12pt;
        }
        .meta-value {
            padding: 1px 0;
            vertical-align: top;
            font-size: 12pt;
        }

        /* Yth. Tujuan Surat */
        .tujuan-surat {
            margin-bottom: 16px;
            line-height: 1.4;
        }
        .tujuan-surat p {
            margin: 0;
        }

        /* Salam Pembuka */
        .salam-pembuka {
            margin-bottom: 12px;
        }
        .salam-pembuka p {
            margin: 0;
        }

        /* Isi Paragraf */
        .isi-surat {
            text-align: justify;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .isi-surat p {
            margin: 0 0 10px 0;
        }

        /* Rincian Kegiatan (Tabel tanpa bullet) */
        .rincian-kegiatan {
            width: 100%;
            margin: 6px 0 14px 28px;
            border-collapse: collapse;
        }
        .rincian-label {
            width: 150px;
            padding: 2px 0;
            vertical-align: top;
            font-size: 12pt;
        }
        .rincian-colon {
            width: 18px;
            padding: 2px 0;
            vertical-align: top;
            font-size: 12pt;
        }
        .rincian-value {
            padding: 2px 0;
            vertical-align: top;
            font-size: 12pt;
        }

        /* Penutup */
        .penutup {
            text-align: justify;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .penutup p {
            margin: 0;
        }

        /* Tanda Tangan */
        .tanda-tangan-container {
            width: 100%;
            margin-top: 20px;
        }
        .tanda-tangan {
            float: right;
            text-align: center;
            width: 260px;
        }
        .jabatan {
            margin: 0 0 10px 0;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .ttd-image {
            width: 210px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
            border-bottom: 1px solid #000;
            margin-bottom: 3px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
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
    <div class="kop-surat-border"></div>

    <!-- Tanggal Surat -->
    <div class="tanggal-surat">
        Cikampek, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('j F Y') }}
    </div>

    <!-- Nomor, Sifat, Lampiran, Perihal -->
    <table class="meta-surat">
        <tr>
            <td class="meta-label">Nomor</td>
            <td class="meta-colon">:</td>
            <td class="meta-value"></td>
        </tr>
        <tr>
            <td class="meta-label">Sifat</td>
            <td class="meta-colon">:</td>
            <td class="meta-value">Biasa</td>
        </tr>
        <tr>
            <td class="meta-label">Lampiran</td>
            <td class="meta-colon">:</td>
            <td class="meta-value">-</td>
        </tr>
        <tr>
            <td class="meta-label">Perihal</td>
            <td class="meta-colon">:</td>
            <td class="meta-value"><strong>Persetujuan Peminjaman Aula</strong></td>
        </tr>
    </table>

    <!-- Tujuan Surat -->
    @php
        $namaPenerima = $peminjaman->user->name ?? $peminjaman->penanggung_jawab;
        $sapaan = (stripos($namaPenerima, 'Saudara') === 0 || stripos($namaPenerima, 'Bpk') === 0 || stripos($namaPenerima, 'Ibu') === 0) ? '' : 'Saudara ';
    @endphp
    <div class="tujuan-surat">
        <p>Yth. {{ $sapaan }}{{ $namaPenerima }}</p>
        <p>Di Tempat</p>
    </div>

    <!-- Salam Pembuka -->
    <div class="salam-pembuka">
        <p>Dengan hormat,</p>
    </div>

    <!-- Paragraf Pembuka -->
    <div class="isi-surat">
        <p>
            Berdasarkan surat permohonan Nomor: {{ $peminjaman->nomor_surat_permohonan ?? '-' }}, perihal permohonan peminjaman Aula Kecamatan Cikampek, dengan ini kami menyampaikan bahwa permohonan peminjaman aula dapat disetujui untuk pelaksanaan kegiatan dengan rincian sebagai berikut:
        </p>
    </div>

    <!-- Rincian Kegiatan -->
    <table class="rincian-kegiatan">
        <tr>
            <td class="rincian-label">Hari/Tanggal</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ \Carbon\Carbon::parse($peminjaman->tanggal)->locale('id')->translatedFormat('l, j F Y') }}</td>
        </tr>
        <tr>
            <td class="rincian-label">Waktu</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ \Carbon\Carbon::parse($peminjaman->jam_mulai)->format('H.i') }} - {{ \Carbon\Carbon::parse($peminjaman->jam_selesai)->format('H.i') }} WIB</td>
        </tr>
        <tr>
            <td class="rincian-label">Tempat</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ $peminjaman->aula->nama_aula ?? 'Aula Kecamatan Cikampek' }}</td>
        </tr>
        <tr>
            <td class="rincian-label">Penanggung Jawab</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ $peminjaman->penanggung_jawab ?? '-' }}</td>
        </tr>
    </table>

    <!-- Paragraf Himbauan -->
    <div class="isi-surat">
        <p>
            Sehubungan dengan hal tersebut, pemohon diharapkan dapat menggunakan fasilitas aula dengan tertib, menjaga kebersihan, serta bertanggung jawab atas fasilitas yang digunakan.
        </p>
    </div>

    <!-- Paragraf Penutup -->
    <div class="penutup">
        <p>
            Demikian surat persetujuan ini disampaikan untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
        </p>
    </div>

    <!-- Tanda Tangan -->
    <div class="tanda-tangan-container clearfix">
        <div class="tanda-tangan">
            <p class="jabatan">CAMAT CIKAMPEK</p>
            <img src="{{ public_path('images/tanda-camat.png') }}" alt="Tanda Tangan Elektronik Camat" class="ttd-image">
        </div>
    </div>

</body>
</html>
