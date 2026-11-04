<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $certRequest->document_type ?? 'Sertifikat' }} - {{ $certRequest->request_code }}</title>
    <style>
        @page {
            size: 297mm 210mm; /* A4 Landscape */
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: "DejaVu Serif", Georgia, "Times New Roman", serif;
            background-image: url('{{ $bgBase64 ?? "" }}');
            background-size: 297mm 210mm;
            background-position: center top;
            background-repeat: no-repeat;
            position: relative;
            color: #1b4d3e;
        }

        /* BLOK KONTEN ATAS (DI ATAS GARIS HIJAU) */
        .top-block {
            position: absolute;
            top: 84.5mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
        }

        /* 1. PERIHAL */
        .perihal {
            font-size: 11.5pt;
            font-weight: bold;
            letter-spacing: 2.5px;
            color: #1b4d3e;
            text-transform: uppercase;
            margin-bottom: 1.5mm;
        }

        /* 2. NOMOR SURAT DI BAWAH PERIHAL */
        .nomor-surat {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 1.2px;
            color: #1b4d3e;
            margin-bottom: 3.5mm;
        }

        /* 3. NAMA PENERIMA (TEPAT DI ATAS GARIS HIJAU) */
        .nama-penerima {
            font-size: 26pt;
            font-weight: bold;
            letter-spacing: 3px;
            color: #1b4d3e;
            text-transform: uppercase;
            line-height: 1.1;
            margin: 0;
        }

        /* BLOK KONTEN BAWAH (DI BAWAH GARIS HIJAU) */
        .bottom-block {
            position: absolute;
            top: 118mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
        }

        /* 4. KATA KATA HARI INI / KETERANGAN KEGIATAN */
        .keterangan {
            font-size: 10.5pt;
            font-weight: bold;
            line-height: 1.6;
            color: #1b4d3e;
            letter-spacing: 0.5px;
            max-width: 235mm;
            margin: 0 auto;
            text-transform: uppercase;
            word-wrap: break-word;
            word-break: break-word;
        }

        /* 5. POJOK KANAN BAWAH (TANGGAL & CAMAT CIKAMPEK) */
        .pejabat-box {
            position: absolute;
            top: 151.5mm;
            right: 14mm;
            width: 76mm;
            text-align: center;
            color: #1b4d3e;
        }

        .tgl-surat {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin-bottom: 1mm;
        }

        .jabatan {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .ttd-space {
            height: 20mm;
            position: relative;
        }

        .ttd-image {
            max-height: 18mm;
            max-width: 60mm;
            display: block;
            margin: 1mm auto;
        }

        .nama-pejabat {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    {{-- BLOK DI ATAS GARIS HIJAU --}}
    <div class="top-block">
        <div class="perihal">
            {{ !empty($certRequest->activity_theme) ? $certRequest->activity_theme : 'PERIHAL' }}
        </div>

        <div class="nomor-surat">
            Nomor: {{ $certRequest->nomor_sertifikat ?? ('421.1/' . str_pad($certRequest->id, 3, '0', STR_PAD_LEFT) . '/Kec-Ckp/' . date('Y')) }}
        </div>

        <div class="nama-penerima">
            {{ $certRequest->certificate_name ?: $certRequest->applicant_name }}
        </div>
    </div>

    {{-- BLOK DI BAWAH GARIS HIJAU --}}
    <div class="bottom-block">
        <div class="keterangan">
            @if($certRequest->certificate_text)
                {{ $certRequest->certificate_text }}
            @else
                ATAS KONTRIBUSI DAN DEDIKASI DALAM {{ strtoupper($certRequest->activity_name) }}, SERTA KINERJA, KREATIVITAS, DAN PROFESIONALISME DALAM MENCAPAI TUJUAN PROYEK.
            @endif
        </div>
    </div>

    {{-- POJOK KANAN BAWAH (TANGGAL & TANDA TANGAN CAMAT) --}}
    <div class="pejabat-box">
        <div class="tgl-surat">
            Cikampek, {{ \Carbon\Carbon::parse($certRequest->activity_date)->translatedFormat('d F Y') }}
        </div>
        <div class="jabatan">
            CAMAT CIKAMPEK
        </div>
        <div class="ttd-space">
            {{-- Ruang tanda tangan / stempel --}}
        </div>
        <div class="nama-pejabat">
            ADI FIRMANSYAH, S.H., M.M.
        </div>
    </div>
</body>
</html>
