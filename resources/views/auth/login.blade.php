@extends('layouts.app')

@section('title', 'Вхід до адмін-панелі')

@section('content')
    <div class="flex justify-center">
        <div class="w-full max-w-md bg-white shadow rounded-lg p-8">
            <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Вхід до адмін-панелі</h1>

            @if(session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.perform') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Логін</label>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-2 px-4 text-white font-semibold bg-blue-600 hover:bg-blue-700 rounded-md shadow"
                >
                    Увійти
                </button>
            </form>

            <div class="mt-6 text-sm text-gray-500">
                <p>Стандартний логін: <span class="font-semibold text-gray-700">admin</span></p>
                <p>Пароль: <span class="font-semibold text-gray-700">engapp2024</span></p>
            </div>
        </div>
    </div>
@endsection
