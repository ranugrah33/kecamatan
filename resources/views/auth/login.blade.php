@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">Kecamatan Cikampek</h1>
            <p class="text-sm text-gray-500 mt-2">Sistem Pelayanan Masyarakat</p>
        </div>

        @if ($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="space-y-4" action="{{ route('login') }}" method="POST">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Masukkan email Anda" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full px-4 py-2 mt-1 text-sm border rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-150">
                Login
            </button>
        </form>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Daftar</a>
            </p>
        </div>
    </div>
</div>
@endsection
