<?php

namespace App\Models;

use App\Traits\DescriptionAble;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use DescriptionAble;

    protected $descriptionClass = EpisodeDescription::class;
    protected $fillable = [
        'link',
        'image_url',
        'rank',
        'season_number',
        'episode_number'
    ];

    public function description()
    {
        return $this->hasOne(EpisodeDescription::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
