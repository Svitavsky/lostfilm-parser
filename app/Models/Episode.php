<?php

namespace App\Models;

use App\Traits\DescriptionAble;
use Illuminate\Http\Request;
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

    public function getExternalLinkAttribute()
    {
        return 'https://www.lostfilm.tv/' . $this->link;
    }

    /**
     * Listing of episodes
     * @param Builder $query
     * @param Request $request
     */
    public function scopeIndex(Builder $query, Request $request)
    {
        $query
            ->distinct()
            ->select('episodes.*', 'episode_descriptions.release_date')
            ->join('episode_descriptions', 'episode_descriptions.episode_id', '=', 'episodes.id')
            ->where('episode_descriptions.language_code', session('language_code', 'ru'))
            ->when($request->get('search'), function ($q) use ($request) {
                $q->search($request->get('search'));
            })
            ->orderByDesc('episode_descriptions.release_date');
    }

    public function scopeSearch(Builder $query, string $search)
    {
        $query
            ->join('episode_descriptions as search_table_1', 'search_table_1.episode_id', '=', 'episodes.id')
            ->join('series_descriptions as search_table_2', 'search_table_2.series_id', '=', 'episodes.series_id')
            ->where('search_table_1.title', 'like', "%$search%")
            ->orWhere('search_table_2.title', 'like', "%$search%");
    }

    /**
     * Search episodes by title
     * @param Builder $query
     * @param array $titles
     */
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
