<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    const PER_PAGE = 10;
    private $model;

    public function __construct(Episode $episode)
    {
        $this->model = $episode;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $episodes = $this->model
            ->index($request)
            ->with('descriptions', 'series', 'series.descriptions')
            ->paginate(self::PER_PAGE);
        return view('episode.list', compact('episodes'));
    }
}
