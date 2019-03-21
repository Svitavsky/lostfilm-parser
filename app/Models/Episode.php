<?php

namespace App\Models;

use App\Traits\DescriptionAble;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeSearchByTitle(Builder $query, array $titles)
    {
        $query->join('episode_descriptions', 'episode_descriptions.episode_id', '=', 'episodes.id');

        foreach ($titles as $languageCode => $title) {
            $query->orWhere(function ($q) use ($title, $languageCode) {
                $q->where('episode_descriptions.language_code', $languageCode)
                    ->where('episode_descriptions.title', $title);
            });
        }
    }
}
