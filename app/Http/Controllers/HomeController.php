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
    $students = [
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

    // Accept raw JSON body (use request()->getContent() if necessary)
    $data = $request->all();

    $userId  = $data['user_id'] ?? null;
    $eventId = $data['event_id'] ?? null;

    if (!$userId || !$eventId) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid QR data: user_id or event_id missing.',
        ], 400);
    }

    $student = collect($students)->firstWhere('id', $userId);

    if (!$student) {
        return response()->json([
            'success' => false,
            'message' => 'Student not found.',
        ], 404);
    }

    $today = Carbon::today()->toDateString();

    $alreadyMarked = Attendance::where('student_id', $userId)
        ->where('date', $today)
        ->exists();

    if ($alreadyMarked) {
        return response()->json([
            'success' => false,
            'message' => "Attendance already marked for {$student['name']} today.",
        ]);
    }

    Attendance::create([
        'student_id'   => $student['id'],
        'student_name' => $student['name'],
        'date'         => $today,
        'marked_at'    => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => "Attendance marked for {$student['name']}.",
    ]);
}

}
