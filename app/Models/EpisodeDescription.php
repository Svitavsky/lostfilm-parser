<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpisodeDescription extends Model
{
    protected $fillable = [
      'language_code',
      'title',
      'release_date'
    ];
}
