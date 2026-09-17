@extends('layouts.admin')
@section('title', 'Pengajuan Sertifikat / Piagam')

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-gray-900">Pengajuan Sertifikat / Piagam</h1>
</div>

{{-- Filter --}}
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.sertifikat.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Semua</a>
    @foreach(['Diajukan','Diproses','Menunggu Dokumen','Selesai','Ditolak'] as $s)
    <a href="{{ route('admin.sertifikat.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') == $s ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">{{ $s }}</a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Kode</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Pemohon</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Jenis</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Nama Kegiatan</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Tanggal</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">File Hasil</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($requests as $r)
                @php
                    $color = 'blue';
                    if ($r->status == 'Selesai') $color = 'green';
                    if ($r->status == 'Ditolak') $color = 'red';
                    if ($r->status == 'Menunggu Dokumen') $color = 'yellow';
                @endphp
                <tr class="hover:bg-gray-50/80 transition">
                    <td class="px-4 py-3 text-xs font-bold text-gray-900">{{ $r->request_code }}</td>
                    <td class="px-4 py-3 text-xs text-gray-700">{{ $r->user->name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $r->document_type }}</td>
                    <td class="px-4 py-3 text-xs text-gray-700">{{ $r->activity_name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $r->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700">{{ $r->status }}</span></td>
                    <td class="px-4 py-3 text-xs">
                        @if($r->latestFile)
                            <span class="text-green-600 flex items-center gap-1"><i class="ph ph-check-circle"></i> Sudah</span>
                        @else
                            <span class="text-gray-400">Belum</span>
                        @endif
                    </td>
                    <td class="px-4 py-3"><a href="{{ route('admin.sertifikat.show', $r->id) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Detail →</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-xs text-gray-500">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
