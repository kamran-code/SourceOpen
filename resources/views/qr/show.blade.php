@extends('layout')

@section('title', 'QR for ' . $student['name'])

@section('content')
<div class="flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-xl rounded-xl p-8 max-w-md text-center border">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            QR Code for {{ $student['name'] }} ({{ $student['id'] }})
        </h1>

        <div class="bg-gray-50 p-4 rounded-lg border inline-block">
            {!! $qr !!}
        </div>

        <div class="mt-4 text-sm text-gray-600">
            URL: <a href="{{ $data }}"  class="text-blue-600 hover:underline">{{ $data }}</a>
        </div>

        <div class="mt-6">
            <a href="{{ route('qr.index') }}" class="text-sm text-gray-600 hover:underline">← Back to list</a>
        </div>
    </div>

</div>
@endsection
