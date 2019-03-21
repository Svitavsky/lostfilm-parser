<?php

namespace App\Models;

use App\Traits\DescriptionAble;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use DescriptionAble;
    protected $descriptionClass = SeriesDescription::class;
    public $with = ['descriptions'];

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function scopeSearchByTitle(Builder $query, array $titles)
    {
        foreach ($titles as $languageCode => $title) {
            $query->orWhere(function ($q) use ($title, $languageCode) {
                $q->where('series_descriptions.language_code', $languageCode)
                    ->where('series_descriptions.title', $title);
            });
        }
    }
}
