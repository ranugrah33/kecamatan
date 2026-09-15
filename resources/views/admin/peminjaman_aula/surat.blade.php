<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Peminjaman Aula - {{ $peminjaman->nomor_pengajuan }}</title>
    <style>
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
            margin-bottom: 5px;
        }
        .kop-logo-container {
            width: 15%;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo {
            width: 75px; /* Sesuaikan ukuran logo */
            height: auto;
        }
        .kop-text {
            width: 85%;
            text-align: center;
            vertical-align: middle;
            padding-right: 15%; /* Mengimbangi logo di kiri agar teks benar-benar di tengah */
        }
        .kop-text h1 {
            font-size: 16pt;
            font-weight: normal;
            margin: 0;
            font-family: 'Times New Roman', Times, serif;
            letter-spacing: 1px;
        }
        .kop-text h2 {
            font-size: 20pt;
            font-weight: bold;
            margin: 2px 0;
            font-family: 'Times New Roman', Times, serif;
            letter-spacing: 2px;
        }
        .kop-text p {
            font-size: 11pt;
            margin: 2px 0;
            font-family: 'Times New Roman', Times, serif;
        }

        .kop-surat-border {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-bottom: 10px;
            margin-top: 5px;
        }

        /* Tanggal & Tempat */
        .tanggal-surat {
            text-align: right;
            margin-bottom: 5px;
        }

        /* Nomor, Sifat, dll */
        .meta-surat {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-label {
            width: 80px;
            padding: 0;
            vertical-align: top;
        }
        .meta-colon {
            width: 15px;
            padding: 0;
            vertical-align: top;
        }
        .meta-value {
            padding: 0;
            font-weight: bold;
            vertical-align: top;
        }

        /* Yth */
        .tujuan-surat {
            margin-bottom: 20px;
        }
        .tujuan-surat p {
            margin: 0;
        }

        /* Isi Surat */
        .isi-surat {
            text-align: justify;
        }
        .isi-surat p {
            margin-bottom: 10px;
            text-indent: 40px;
        }

        /* Detail Kegiatan */
        .detail-kegiatan {
            width: 90%;
            margin: 0 auto 15px 40px;
        }
        .detail-label {
            width: 120px;
            padding: 2px 0;
            vertical-align: top;
        }
        .detail-colon {
            width: 15px;
            padding: 2px 0;
            vertical-align: top;
        }
        .detail-value {
            padding: 2px 0;
            vertical-align: top;
        }

        /* Penutup */
        .penutup {
            margin-bottom: 40px;
            text-align: justify;
            text-indent: 40px;
        }

        /* Tanda Tangan */
        .tanda-tangan-container {
            width: 100%;
        }
        .tanda-tangan {
            float: right;
            text-align: center;
            width: 300px;
            margin-top: 10px;
        }
        .tanda-tangan p {
            margin: 0;
        }
        .jabatan {
            margin-bottom: 80px;
        }
        .nama-camat {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Area Tanda Tangan Elektronik placeholder for standard formatting */
        .ttd-box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 10px;
            text-align: left;
            font-size: 10pt;
        }
        .ttd-box-logo {
            float: left;
            width: 40px;
            margin-right: 10px;
        }
        .ttd-box-text {
            float: left;
            width: 220px;
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

    <div class="tanggal-surat">
        Karawang, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
    </div>

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
            <td class="meta-value"><b>Perizinan Peminjaman Aula</b></td>
        </tr>
    </table>

    <div class="tujuan-surat">
        <p>Yth. {{ $peminjaman->user->name }}</p>
        <p>Di Tempat</p>
    </div>

    <div class="isi-surat">
        <p>
            Sehubungan dengan rencana pelaksanaan kegiatan, melalui surat permohonan Nomor: {{ $peminjaman->nomor_surat_permohonan ?? '-' }}, kami selaku masyarakat mengajukan permohonan izin peminjaman Aula Kecamatan Cikampek untuk digunakan sebagai tempat pelaksanaan kegiatan Pada :
        </p>
    </div>

    <table class="detail-kegiatan">
        <tr>
            <td class="detail-label">Hari / Tanggal</td>
            <td class="detail-colon">:</td>
            <td class="detail-value">{{ \Carbon\Carbon::parse($peminjaman->tanggal)->locale('id')->translatedFormat('l / d F Y') }}</td>
        </tr>
        <tr>
            <td class="detail-label">Waktu</td>
            <td class="detail-colon">:</td>
            <td class="detail-value">{{ substr($peminjaman->jam_mulai, 0, 5) }} WIB - {{ substr($peminjaman->jam_selesai, 0, 5) }} WIB</td>
        </tr>
        <tr>
            <td class="detail-label">Tempat</td>
            <td class="detail-colon">:</td>
            <td class="detail-value">Aula Kecamatan Cikampek</td>
        </tr>
        <tr>
            <td class="detail-label">Penanggung Jawab</td>
            <td class="detail-colon">:</td>
            <td class="detail-value">{{ $peminjaman->penanggung_jawab ?? '-' }}</td>
        </tr>
    </table>

    <div class="penutup">
        Demikian undangan ini disampaikan. Atas perhatian dan kehadiran Bapak, kami ucapkan terima kasih.
    </div>

    <div class="tanda-tangan-container clearfix">
        <div class="tanda-tangan">
            <p class="jabatan">CAMAT CIKAMPEK</p>
            
            <img src="{{ public_path('images/tanda-camat.png') }}" alt="Tanda Tangan Camat" style="width: 200px; height: auto; margin: 15px auto; display: block;">
        </div>
    </div>

</body>
</html>
