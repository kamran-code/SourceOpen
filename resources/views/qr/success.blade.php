<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Attendance Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white border shadow-xl rounded-xl p-8 max-w-md text-center animate-fade-in-down">
        @if ($alreadyMarked)
            <div class="text-yellow-600 text-2xl font-bold mb-4">⚠️ Already Marked</div>
            <p class="text-gray-700 text-lg">Attendance was already marked today for
                <strong>{{ $student['name'] }}</strong> ({{ $student['id'] }}).</p>
        @else
            <div class="text-green-600 text-2xl font-bold mb-4">✅ Attendance Marked</div>
            <p class="text-gray-700 text-lg">Welcome, <strong>{{ $student['name'] }}</strong> ({{ $student['id'] }})!</p>
            <p class="text-sm text-gray-500 mt-2">Your attendance has been successfully recorded.</p>
        @endif

        <a href="{{ url('/') }}" class="mt-6 inline-block text-blue-600 hover:underline text-sm">← Back to Home</a>
    </div>

    <style>
        .animate-fade-in-down {
            animation: fadeInDown 0.5s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

</body>

</html>
