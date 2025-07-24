@extends('layout')

@section('title', 'QR List')

@section('content')
<div class="flex h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">👥 Student List</h2>
        <ul class="space-y-2">
            @foreach ($students as $student)
                <li>
                    <a href="{{ route('qr.show', ['id' => $student['id']]) }}"
                       class="block px-3 py-2 rounded-lg hover:bg-blue-100 text-gray-700 font-medium">
                        {{ $student['name'] }} <span class="text-sm text-gray-500">({{ $student['id'] }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    <!-- Instruction -->
    <main class="flex-1 flex items-center justify-center p-8 text-gray-500 text-xl">
        Click a student to view their QR code
    </main>

</div>
@endsection
