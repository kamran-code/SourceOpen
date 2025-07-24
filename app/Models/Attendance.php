<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    public $timestamps = false;


    protected $table = 'attendances';


    protected $fillable = [
        'student_id',
        'student_name',
        'date',
        'marked_at',
    ];
}
