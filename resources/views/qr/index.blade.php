@extends('layout')

@section('title', 'QR List')

@section('content')
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-md" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    <div class="flex h-screen">

        @php
            $eventId = 1;
        @endphp
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">👥 Student List</h2>
            <ul class="space-y-2">
                @foreach ($students as $student)
                    <li>
                        <a href="{{ route('qr.show', ['id' => $student['id'], 'eventId' => $eventId]) }}"
                            class="block px-3 py-2 rounded-lg hover:bg-blue-100 text-gray-700 font-medium">
                            {{ $student['name'] }} <span class="text-sm text-gray-500">({{ $student['id'] }})</span>
                        </a>

                    </li>
                @endforeach
                <li>
                    <form action="{{ url('/clear') }}" method="GET" class="inline">
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200 ease-in-out">
                            🚨 Truncate Attendance
                        </button>
                    </form>
                </li>
            </ul>
        </aside>

        <!-- Instruction -->
        <main class="flex-1 flex items-center justify-center p-8 text-gray-500 text-xl">

            Click a student to view their QR code
        </main>

    </div>
@endsection
