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
        return response()->json([
            'status'       => 1,
            'checkin'      => false,
            'message'      => 'Saved',
            'student_name' => 'Swara Mahabhashyam',
            'event_name'   => 'XLE2425FSCE02',
            'start_time'   => '23 February 2025 10:00 AM',
            'end_time'     => '02 March 2025 09:00 PM',
        ]);
    }
}
