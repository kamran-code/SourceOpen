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
    public function generate($id)
    {
        $student = collect($this->students)->firstWhere('id', $id);

        if (!$student) {
            abort(404, 'Student not found');
        }

        $url = url('/api/scan?student=' . $student['id']);
        $qr = QrCode::format('svg')->size(300)->generate($url);

        return view('qr.show', [
            'student' => $student,
            'qr' => $qr,
            'url' => $url
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

        $id = $request->query('student');
        $student = collect($students)->firstWhere('id', $id);

        if (!$student) {
            return view('qr.index', [
                'students' => $students,
                'message' => 'Invalid student ID.',
                'messageType' => 'error',
            ]);
        }

        $today = Carbon::today()->toDateString();

        $alreadyMarked = Attendance::where('student_id', $id)
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
            'message' => "Attendance marked for {$student['name']}."
        ]);
    }
}
