<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\EpisodeDescription;
use App\Models\Series;
use App\Models\SeriesDescription;
use App\Traits\ExecutionTimer;
use DiDom\Document;
use DiDom\Element;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParseLostFilm extends Command
{
    use ExecutionTimer;

    /**
     * Regex for getting episode and season numbers from string
     * @var \Illuminate\Config\Repository
     */
    private $seasonEpisodeRegex;

    /**
     * Regex for getting release date from string
     * @var \Illuminate\Config\Repository
     */
    private $releaseDateRegex;

    /**
     * How many episodes added
     * @var int
     */
    private $episodesAdded = 0;

    /**
     * Episodes count per page
     * @var \Illuminate\Config\Repository|mixed
     */
    private $episodesPerPage;

    /**
     * Service global URL
     * @var string
     */
    private $globalUrl;

    /**
     * The name and signature of the console command.
     *
     * --all - remove all previous data and parse all episodes
     * --refresh - parse all not existing episodes from all pages, no previous deleting
     * If no flag, only latest episodes will be added
     *
     * @var string
     */
    protected $signature = 'parse:lostfilm {--all} {--refresh}';

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
        $this->episodesPerPage = config('lostfilm.episodes_per_page');
        $this->globalUrl = config('lostfilm.url');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Prevent using both options
        if ($this->option('all') && $this->option('refresh')) {
            $this->output->error('You can use only one option!');
            return;
        }

        if (empty($this->globalUrl) || !filter_var($this->globalUrl, FILTER_VALIDATE_URL)) {
            Log::error('[LostFilm parser] No url provided in lostfilm.php!');
            return;
        }

        $this->executionStart();
        if ($this->option('all')) {
            DB::table((new Episode)->getTable())->delete();
        }
        $this->storeEpisodesFromPage($this->globalUrl);
        $this->executionEnd();

        Log::info("[LostFilm parser] {Finished} {$this->episodesAdded} episodes added! It took {$this->executionTimeForHuman()}");
    }

    /**
     * Iterate over all episodes on page and store them if not exists
     * @param string $url
     */
    private function storeEpisodesFromPage(string $url)
    {
        $document = new Document($url, true);
        $rows = $document->find('.serials-list > .body > .row');

        if ($hasNextPage = $document->first('.pagging-pane > a > .next-link.active')) {
            $href = $hasNextPage->parent()->attr('href');
            $pieces = explode('/', $href);
            $nextPageUrl = $this->globalUrl . end($pieces);

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                Log::error("[LostFilm parser] Wrong url for next page generated! Exit. (url = {$url})");
                return;
            }
        }

        if (count($rows)) {
            foreach ($rows as $row) {
                // Different languages data
                $ruData = $row->find('.details-pane > .alpha');
                $enData = $row->find('.details-pane > .beta');

                $hasEpisodeTitleRu = isset($ruData[0]) && $ruData[0] instanceof Element;
                $hasEpisodeTitleEn = isset($enData[0]) && $enData[0] instanceof Element;
                $hasEpisodeReleaseDateRu = isset($ruData[1]) && $ruData[1] instanceof Element;
                $hasEpisodeReleaseDateEn = isset($enData[1]) && $enData[1] instanceof Element;

                $episodeReleaseDateRu = $hasEpisodeReleaseDateRu ? $this->getReleaseDate($ruData[1]->text()) : null;
                $episodeReleaseDateEn = $hasEpisodeReleaseDateEn ? $this->getReleaseDate($enData[1]->text()) : null;

                // Series section

                $titleRu = $row->first('.body .name-ru')->text();
                $titleEn = $row->first('.body .name-en')->text();
                $titles = [
                    'ru' => $titleRu,
                    'en' => $titleEn,
                ];

                // Check if series already exists
                $series = Series::select('series.id')
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

                // Check if episode exists
                if (!$series->wasRecentlyCreated) {
                    $episode = $series
                        ->episodes()
                        ->where('season_number', $seasonNumber)
                        ->where('episode_number', $episodeNumber)
                        ->first();

                    if ($episode) {
                        $today = !$episodeReleaseDateRu->setTimezone('Europe/Moscow')->isToday();

                        if(!$today && !$this->option('refresh')) {
                            break;
                        }

                        $hasNextPage = $this->option('refresh');
                        continue;
                    }
                }

                $episode = new Episode([
                    'link' => $episodeLink,
                    'image_url' => $episodeImage,
                    'rank' => (float)$episodeRank,
                    'season_number' => $seasonNumber,
                    'episode_number' => $episodeNumber
                ]);

                $series->episodes()->save($episode);


                // Episode descriptions section

                $episodeTitleRu = $hasEpisodeTitleRu ? $ruData[0]->text() : null;
                $episodeTitleEn = $hasEpisodeTitleEn ? $enData[0]->text() : null;

                $descriptions = [
                    new EpisodeDescription([
                        'language_code' => 'ru',
                        'title' => $episodeTitleRu,
                        'release_date' => $episodeReleaseDateRu,
                    ]),
                    new EpisodeDescription([
                        'language_code' => 'en',
                        'title' => $episodeTitleEn,
                        'release_date' => $episodeReleaseDateEn,
                    ])
                ];

                $episode->descriptions()->saveMany($descriptions);

                $this->episodesAdded++;
            }
        }

        // Do we need to go to the next page?
        $needNextRecursionCycle = !($this->episodesAdded % $this->episodesPerPage) || $this->option('all') || $this->option('refresh');

        if ($hasNextPage && isset($nextPageUrl) && $needNextRecursionCycle) {
            $this->storeEpisodesFromPage($nextPageUrl);
        }
    }

    /**
     * Get episode and season numbers by using regexp
     * @param string $seasonEpisodeString
     * @return array
     */
    private function getSeasonEpisodeNumbers(string $seasonEpisodeString)
    {
        if (!preg_match_all("/{$this->seasonEpisodeRegex}/", $seasonEpisodeString, $matches)) {
            Log::info('[LostFilm parser] Can not parse episode and season numbers from string, check page changes or your regex at config/lostfilm.php!');
        }

        $seasonNumber = $matches[0][0] ?? null;
        $episodeNumber = $matches[0][1] ?? null;
        return [(int)$seasonNumber, (int)$episodeNumber];
    }

    /**
     * Get release date by using regexp
     * @param string $releaseDate
     * @return \Carbon\CarbonInterface|Carbon|null
     */
    private function getReleaseDate(string $releaseDate)
    {
        if (!preg_match("/{$this->releaseDateRegex}/", $releaseDate, $matches)) {
            Log::info('[LostFilm parser] Can not parse string release date from string, check page changes or your regex at config/lostfilm.php!');
        }

        return isset($matches[1]) ? Carbon::parse($matches[1]) : null;
    }
}
