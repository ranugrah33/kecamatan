<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Draft Surat Persetujuan Peminjaman Inventaris - {{ $borrowing->request_code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 22mm 18mm 22mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.45;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 11.5pt;
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
            margin: 2px 0 0 0;
        }

        .kop-surat-border {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-bottom: 14px;
            margin-top: 6px;
        }

        /* Tanggal Surat (Kanan Atas) */
        .tanggal-surat {
            text-align: right;
            margin-bottom: 10px;
            font-size: 11.5pt;
        }

        /* Nomor, Sifat, Lampiran, Perihal (Kiri) */
        .meta-surat {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .meta-label {
            width: 85px;
            padding: 1px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .meta-colon {
            width: 16px;
            padding: 1px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .meta-value {
            padding: 1px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }

        /* Yth. Tujuan Surat */
        .tujuan-surat {
            margin-bottom: 12px;
            line-height: 1.35;
        }
        .tujuan-surat p {
            margin: 0;
        }

        /* Salam Pembuka */
        .salam-pembuka {
            margin-bottom: 8px;
        }
        .salam-pembuka p {
            margin: 0;
        }

        /* Isi Paragraf */
        .isi-surat {
            text-align: justify;
            margin-bottom: 8px;
            line-height: 1.45;
        }
        .isi-surat p {
            margin: 0 0 8px 0;
        }

        /* Rincian Peminjaman */
        .rincian-table {
            width: 100%;
            margin: 4px 0 10px 18px;
            border-collapse: collapse;
        }
        .rincian-label {
            width: 170px;
            padding: 2px 0;
            vertical-align: top;
            font-size: 11pt;
        }
        .rincian-colon {
            width: 16px;
            padding: 2px 0;
            vertical-align: top;
            font-size: 11pt;
        }
        .rincian-value {
            padding: 2px 0;
            vertical-align: top;
            font-size: 11pt;
        }

        /* Tabel Daftar Barang */
        .barang-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 12px 0;
            font-size: 11pt;
        }
        .barang-table th, .barang-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: left;
            vertical-align: middle;
        }
        .barang-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center !important;
        }

        /* Ketentuan Peminjaman */
        .ketentuan {
            margin: 8px 0 12px 0;
            font-size: 10.5pt;
            line-height: 1.4;
        }
        .ketentuan ol {
            margin: 4px 0 0 0;
            padding-left: 20px;
        }
        .ketentuan li {
            margin-bottom: 3px;
            text-align: justify;
        }

        /* Penutup */
        .penutup {
            text-align: justify;
            margin-bottom: 18px;
            line-height: 1.45;
        }
        .penutup p {
            margin: 0;
        }

        /* Tanda Tangan */
        .tanda-tangan-container {
            width: 100%;
            margin-top: 10px;
        }
        .tanda-tangan {
            float: right;
            text-align: center;
            width: 270px;
        }
        .jabatan {
            margin: 0 0 6px 0;
            font-size: 11.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .ttd-space {
            height: 70px;
        }
        .pejabat-nama {
            font-weight: bold;
            font-size: 11.5pt;
            text-decoration: underline;
            margin: 0;
        }
        .pejabat-nip {
            font-size: 10.5pt;
            margin: 2px 0 0 0;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Resmi -->
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

    <!-- Meta Surat -->
    <table class="meta-surat">
        <tr>
            <td class="meta-label">Nomor</td>
            <td class="meta-colon">:</td>
            <td class="meta-value">
                @if($borrowing->activeFinalDocument?->nomor_surat)
                    {{ $borrowing->activeFinalDocument->nomor_surat }}
                @else
                    ................................................................
                @endif
            </td>
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
            <td class="meta-value"><strong>Persetujuan Peminjaman Inventaris</strong></td>
        </tr>
    </table>

    <!-- Tujuan Surat -->
    @php
        $namaPenerima = $borrowing->applicant_name ?? $borrowing->penanggung_jawab;
        $sapaan = (stripos($namaPenerima, 'Saudara') === 0 || stripos($namaPenerima, 'Bpk') === 0 || stripos($namaPenerima, 'Ibu') === 0) ? '' : 'Saudara/i ';
    @endphp
    <div class="tujuan-surat">
        <p>Yth. {{ $sapaan }}<strong>{{ $namaPenerima }}</strong></p>
        @if($borrowing->institution)
            <p>{{ $borrowing->institution }}</p>
        @endif
        <p>Di Tempat</p>
    </div>

    <!-- Salam Pembuka -->
    <div class="salam-pembuka">
        <p>Dengan hormat,</p>
    </div>

    <!-- Paragraf Pembuka -->
    <div class="isi-surat">
        <p>
            Berdasarkan permohonan peminjaman barang inventaris dengan Kode Transaksi <strong>{{ $borrowing->request_code }}</strong>@if($borrowing->institution), atas nama <strong>{{ $borrowing->institution }}</strong>@endif, perihal permohonan peminjaman inventaris perlengkapan Kantor Kecamatan Cikampek, dengan ini kami menyampaikan bahwa permohonan peminjaman tersebut dapat <strong>disetujui</strong> dengan rincian sebagai berikut:
        </p>
    </div>

    <!-- Rincian Peminjaman -->
    <table class="rincian-table">
        <tr>
            <td class="rincian-label">Keperluan / Kegiatan</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ $borrowing->purpose }}</td>
        </tr>
        <tr>
            <td class="rincian-label">Penanggung Jawab</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ $borrowing->penanggung_jawab }}</td>
        </tr>
        <tr>
            <td class="rincian-label">No. Telepon / WhatsApp</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ $borrowing->phone ?? '-' }}</td>
        </tr>
        <tr>
            <td class="rincian-label">Waktu Peminjaman</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">
                {{ \Carbon\Carbon::parse($borrowing->borrow_date)->locale('id')->translatedFormat('l, j F Y') }}
                @if($borrowing->borrowing_time)
                    pukul {{ $borrowing->formatted_borrowing_time }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="rincian-label">Waktu Pengembalian</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">
                {{ \Carbon\Carbon::parse($borrowing->return_date)->locale('id')->translatedFormat('l, j F Y') }}
                @if($borrowing->return_time)
                    pukul {{ $borrowing->formatted_return_time }}
                @endif
            </td>
        </tr>
        @if($borrowing->notes)
        <tr>
            <td class="rincian-label">Catatan Pemohon</td>
            <td class="rincian-colon">:</td>
            <td class="rincian-value">{{ $borrowing->notes }}</td>
        </tr>
        @endif
    </table>

    <!-- Tabel Rincian Barang yang Disetujui -->
    <div style="font-weight: bold; margin-bottom: 4px; font-size: 11pt;">Daftar Barang Inventaris yang Dipinjam:</div>
    <table class="barang-table">
        <thead>
            <tr>
                <th style="width: 35px;">No.</th>
                <th>Nama Barang</th>
                <th style="width: 90px;" class="text-center">Jumlah</th>
                <th style="width: 140px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($borrowing->details as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $detail->item->name ?? '-' }}</strong></td>
                <td class="text-center">{{ $detail->quantity }} unit</td>
                <td>Kondisi baik &amp; layak pakai</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada rincian barang.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ketentuan Peminjaman -->
    <div class="ketentuan">
        <strong>Ketentuan &amp; Tata Tertib Peminjaman:</strong>
        <ol>
            <li>Barang inventaris hanya dipergunakan untuk keperluan sebagaimana tercantum dalam surat permohonan.</li>
            <li>Peminjam wajib menjaga keutuhan, kebersihan, dan keselamatan fisik barang inventaris selama masa peminjaman.</li>
            <li>Pengembalian barang wajib dilaksanakan tepat waktu sesuai jadwal yang telah ditentukan.</li>
            <li>Apabila terjadi kerusakan atau kehilangan barang, peminjam bertanggung jawab penuh untuk memperbaiki atau mengganti sesuai ketentuan yang berlaku di Kecamatan Cikampek.</li>
        </ol>
    </div>

    <!-- Paragraf Penutup -->
    <div class="penutup">
        <p>
            Demikian surat persetujuan ini disampaikan untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
        </p>
    </div>

    <!-- Tanda Tangan Pejabat Berwenang (Sesuai Aturan 5.F: Tanpa Tanda Tangan Tiruan/Palsu) -->
    <div class="tanda-tangan-container clearfix">
        <div class="tanda-tangan">
            <p class="jabatan">CAMAT CIKAMPEK</p>
            <div class="ttd-space"></div>
            <p class="pejabat-nama">( .................................................... )</p>
            <p class="pejabat-nip">NIP. ....................................................</p>
        </div>
    </div>

</body>
</html>
