<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lawyerUnavailability extends Model
{
    // I-specify ang table name kay basin 'lawyer_unavailabilities' ang gipangita sa Laravel
    protected $table = 'lawyer_unavailabilities';

    protected $fillable = [
        'lawyer_id', 
        'unavailable_date', 
        'reason', 
        'is_whole_day'
    ];
}