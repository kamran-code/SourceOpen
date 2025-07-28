<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{

    private $students = [
        ['id' => 'S101', 'name' => 'Alice'],
        ['id' => 'S102', 'name' => 'Bob'],
        ['id' => 'S103', 'name' => 'Charlie'],
        ['id' => 'S104', 'name' => 'Diana'],
        ['id' => 'S105', 'name' => 'Ethan'],
        ['id' => 'S106', 'name' => 'Fiona'],
        ['id' => 'S107', 'name' => 'George'],
        ['id' => 'S108', 'name' => 'Hannah'],
        ['id' => 'S109', 'name' => 'Ivan'],
        ['id' => 'S110', 'name' => 'Jasmine'],
    ];
    public function index()
    {
        return view('index');
    }
    public function index1()
    {
        return view('qr.index', [
            'students' => $this->students
        ]);
    }
    public function generate($id, $eventId)
    {
        $student = collect($this->students)->firstWhere('id', $id);

        if (!$student) {
            abort(404, 'Student not found');
        }

        $data = json_encode([
            'user_id' => $student['id'],
            'event_id' => $eventId
        ], JSON_UNESCAPED_UNICODE);

        $qr = QrCode::format('svg')->size(300)->generate($data);

        return view('qr.show', [
            'student' => $student,
            'qr' => $qr,
            'data' => $data
        ]);
    }





    public function mark(Request $request)
    {
        $studentId = $request->input('student_id');
        $eventId   = $request->input('event_id');

        if (!$studentId || !$eventId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing student_id or event_id.',
            ], 400);
        }

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('student_id', $studentId)
            ->where('event_id', $eventId)
            ->where('date', $today)
            ->first();

        // Generate dummy name/class (for response only)
        $dummyName  = 'Student_' . substr($studentId, -3);     // e.g., Student_101
        $dummyClass = 'Class-' . rand(1, 12);                  // e.g., Class-7

        if (!$attendance) {
            // First-time check-in
            Attendance::create([
                'student_id' => $studentId,
                'event_id'   => $eventId,
                'date'       => $today,
                'checkin_at' => now(),
            ]);

            return response()->json([
                'success'      => true,
                'status'       => 'checkin',
                'student_id'   => $studentId,
                'event_id'     => $eventId,
                'student_name' => $dummyName,
                'class'        => $dummyClass,
                'timestamp'    => now()->toDateTimeString(),
                'message'      => 'Check-in successful.',
            ]);
        }

        if ($attendance->checkin_at && !$attendance->checkout_at) {
            // Mark check-out
            $attendance->update(['checkout_at' => now()]);

            return response()->json([
                'success'      => true,
                'status'       => 'checkout',
                'student_id'   => $studentId,
                'event_id'     => $eventId,
                'student_name' => $dummyName,
                'class'        => $dummyClass,
                'timestamp'    => now()->toDateTimeString(),
                'message'      => 'Check-out successful.',
            ]);
        }

        return response()->json([
            'success'      => false,
            'status'       => 'completed',
            'student_id'   => $studentId,
            'event_id'     => $eventId,
            'student_name' => $dummyName,
            'class'        => $dummyClass,
            'message'      => 'Attendance already completed for today.',
        ]);
    }
}
