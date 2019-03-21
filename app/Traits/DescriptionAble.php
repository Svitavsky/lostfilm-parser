<?php

namespace App\Traits;

trait DescriptionAble
{
    public function descriptions()
    {
        if ($this->foreignKey) {
            return $this->hasMany($this->descriptionClass, $this->foreignKey);
        }
        return $this->hasMany($this->descriptionClass);
    }

    public function getTranslationsAttribute()
    {
        return $this->descriptions->keyBy('language_code');
    }

    public function getDescriptionAttribute()
    {
        return $this->descriptions
            ->whereIn('language_code', [session('language_code', 'ru'), 'ru'])
            ->filter(function ($description) {
                if (isset($this->titleField)) {
                    return isset($description->{$this->titleField}) && !empty($description->{$this->titleField});
                }
                return isset($description->title) && !empty($description->title);
            })
            ->sortByDesc('id')
            ->first();
    }

    public function getTitleByLanguageAttribute()
    {
        if ($this->description) {
            $differentField = isset($this->titleField) && isset($this->description->{$this->titleField});
            return $differentField ? $this->description->{$this->titleField} : $this->description->title;
        }
        return null;
    }
}