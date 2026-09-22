@extends('layouts.admin')

@section('title', 'Edit Knowledge AI')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.ai_knowledge.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition">
            <i class="ph ph-arrow-left text-xl"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Knowledge AI</h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi untuk Asisten Digital.</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl">
        <ul class="list-disc list-inside text-sm font-medium">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <form action="{{ route('admin.ai_knowledge.update', $knowledge->id) }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="category" value="{{ old('category', $knowledge->category) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Judul Informasi <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $knowledge->title) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Contoh Pertanyaan <span class="text-red-500">*</span></label>
                <textarea name="question" required rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition resize-none">{{ old('question', $knowledge->question) }}</textarea>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Jawaban / Informasi <span class="text-red-500">*</span></label>
                <textarea name="answer" required rows="6" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition resize-y">{{ old('answer', $knowledge->answer) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Kata Kunci (Keywords)</label>
                    <input type="text" name="keywords" value="{{ old('keywords', $knowledge->keywords) }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Prioritas <span class="text-red-500">*</span></label>
                    <input type="number" name="priority" value="{{ old('priority', $knowledge->priority) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition">
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $knowledge->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Aktifkan data ini</span>
                </label>
            </div>

            <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.ai_knowledge.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Batal</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
