<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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
        $userId  = $request->input('user_id');
        $eventId = $request->input('event_id');
        $action = $request->input('action');
        $type = $request->input('type');

        $queryParams = [
            'action'   => $action,
            'type'     => $type,
            'user_id'  => $userId,
            'event_id' => $eventId
        ];

        $url = 'https://online.xl-education.co.uk/scanner/backend/action?' . http_build_query($queryParams);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true); // POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, ''); // Empty POST body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Optional timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL check if needed

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return response()->json([
                'status' => 0,
                'message' => 'cURL error: ' . $error,
            ], 500);
        }

        curl_close($ch);


        return response($response, 200)->header('Content-Type', 'application/json');
    }
}
