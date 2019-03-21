<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\EpisodeDescription;
use App\Models\Series;
use App\Models\SeriesDescription;
use DiDom\Document;
use DiDom\Element;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ParseLostFilm extends Command
{
    private $seasonEpisodeRegex;
    private $releaseDateRegex;

    private $episodesAdded = 0;

    /**
     * The name and signature of the console command.
     *
     * --all - parse all episodes, not only latest
     *
     * @var string
     */
    protected $signature = 'parse:lostfilm {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse episodes from LostFilm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->seasonEpisodeRegex = config('lostfilm.season_episode_regex');
        $this->releaseDateRegex = config('lostfilm.release_date_regex');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = config('lostfilm.url');

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            Log::error('[LostFilm parser] No url provided in lostfilm.php!');
            return;
        }

        $document = new Document($url, true);

        $rows = $document->find('.serials-list > .body > .row');

        if (count($rows)) {
            foreach ($rows as $row) {
                // Series section

                $titleRu = $row->first('.body .name-ru')->text();
                $titleEn = $row->first('.body .name-en')->text();
                $titles = [
                    'ru' => $titleRu,
                    'en' => $titleEn,
                ];

                $series = Series::select('series.id')
                    ->join('series_descriptions', 'series_descriptions.series_id', '=', 'series.id')
                    ->searchByTitle($titles)
                    ->first();

                if (!$series) {
                    $series = Series::create([]);
                    $series->descriptions()->saveMany([
                        new SeriesDescription(['title' => $titleRu, 'language_code' => 'ru']),
                        new SeriesDescription(['title' => $titleEn, 'language_code' => 'en']),
                    ]);
                }


                // Episode section

                $episodeLink = $row->first('a')->attr('href');
                $episodeImage = $row->first('.picture-box > img')->attr('src');
                $episodeRank = $row->first('.mark-green-box')->text();
                $episodeSeasonString = $row->first('.picture-box > .overlay > .left-part')->text();
                list($seasonNumber, $episodeNumber) = $this->getSeasonEpisodeNumbers($episodeSeasonString);

                $episode = new Episode([
                    'link' => $episodeLink,
                    'image_url' => $episodeImage,
                    'rank' => (float)$episodeRank,
                    'season_number' => (int)$seasonNumber,
                    'episode_number' => (int)$episodeNumber
                ]);

                $series->episodes()->save($episode);

                // Episode descriptions section

                $ruData = $row->find('.details-pane > .alpha');
                $enData = $row->find('.details-pane > .beta');

                $hasEpisodeTitleRu = isset($ruData[0]) && $ruData[0] instanceof Element;
                $hasEpisodeTitleEn = isset($enData[0]) && $enData[0] instanceof Element;
                $hasEpisodeReleaseDateRu = isset($ruData[1]) && $ruData[1] instanceof Element;
                $hasEpisodeReleaseDateEn = isset($enData[1]) && $enData[1] instanceof Element;

                $episodeTitleRu = $hasEpisodeTitleRu ? $ruData[0]->text() : null;
                $episodeTitleEn = $hasEpisodeTitleEn ? $enData[0]->text() : null;
                $episodeReleaseDateRu = $hasEpisodeReleaseDateRu ? $this->getReleaseDate($ruData[1]->text()) : null;
                $episodeReleaseDateEn = $hasEpisodeReleaseDateEn ? $this->getReleaseDate($enData[1]->text()) : null;

                $descriptions = [
                    new EpisodeDescription([
                        'language_code' => 'ru',
                        'title' => $episodeTitleRu,
                        'release_date' => Carbon::parse($episodeReleaseDateRu),
                    ]),
                    new EpisodeDescription([
                        'language_code' => 'en',
                        'title' => $episodeTitleEn,
                        'release_date' => Carbon::parse($episodeReleaseDateEn),
                    ])
                ];

                $episode->descriptions()->saveMany($descriptions);

                $this->episodesAdded++;
            }
        }

        Log::info("[LostFilm parser] {$this->episodesAdded} episodes added!");
    }

    private function getSeasonEpisodeNumbers(string $seasonEpisodeString)
    {
        if (!preg_match_all("/{$this->seasonEpisodeRegex}/", $seasonEpisodeString, $matches)) {
            Log::info('[LostFilm parser] Can not parse episode and season numbers from string, check page changes or your regex at config/lostfilm.php!');
        }

        $seasonNumber = $matches[0][0] ?? null;
        $episodeNumber = $matches[0][1] ?? null;
        return [$seasonNumber, $episodeNumber];
    }

    private function getReleaseDate(string $releaseDate)
    {
        if (!preg_match("/{$this->releaseDateRegex}/", $releaseDate, $matches)) {
            Log::info('[LostFilm parser] Can not parse string release date from string, check page changes or your regex at config/lostfilm.php!');
        }

        return $matches[1] ?? null;
    }
}
