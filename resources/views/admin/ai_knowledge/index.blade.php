@extends('layouts.admin')

@section('title', 'Knowledge Base AI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Knowledge Base AI</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data informasi untuk Asisten Digital Kecamatan.</p>
        </div>
        <a href="{{ route('admin.ai_knowledge.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
            <i class="ph ph-plus text-lg"></i>
            Tambah Data
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-start gap-3">
        <i class="ph ph-check-circle text-xl flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-medium text-sm">Berhasil</p>
            <p class="text-sm opacity-90">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200/80 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.ai_knowledge.index') }}" method="GET" class="relative w-full sm:w-96">
                <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari pertanyaan, judul, kategori..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition">
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200/80 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Kategori & Judul</th>
                        <th class="px-6 py-4 font-semibold w-1/3">Pertanyaan</th>
                        <th class="px-6 py-4 font-semibold text-center w-24">Prioritas</th>
                        <th class="px-6 py-4 font-semibold text-center w-28">Status</th>
                        <th class="px-6 py-4 font-semibold text-right w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($knowledges as $knowledge)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-medium bg-blue-50 text-blue-700 mb-1 border border-blue-200/60">
                                {{ $knowledge->category }}
                            </span>
                            <p class="font-medium text-gray-900">{{ $knowledge->title }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-600 line-clamp-2" title="{{ $knowledge->question }}">{{ $knowledge->question }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs border border-gray-200">
                                {{ $knowledge->priority }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('admin.ai_knowledge.toggle', $knowledge->id) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $knowledge->is_active ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100' }}">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $knowledge->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                    {{ $knowledge->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.ai_knowledge.edit', $knowledge->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('admin.ai_knowledge.destroy', $knowledge->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                <i class="ph ph-database text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Tidak ada data knowledge base.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($knowledges->hasPages())
        <div class="px-6 py-4 border-t border-gray-200/80 bg-gray-50/50">
            {{ $knowledges->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
