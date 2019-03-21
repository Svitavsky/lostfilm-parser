<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesDescription extends Model
{
    protected $fillable = [
        'title',
        'language_code',
    ];
}
