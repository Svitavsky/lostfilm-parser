@extends('layouts.app')

@section('content')
    <style>
        .episodes {
            display: flex;
            width: 60%;
            flex-wrap: wrap;
            margin: 0 20%;
        }

        .episode_card, .row {
            display: flex;
            flex-direction: row;
        }

        .episode_card {
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
            transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
            margin: 10px 10px;
            height: 144px;
            flex: 1 0 47%;
            flex-grow: 1;
            display: flex;
            flex-direction: row;
            background-color: whitesmoke;
            justify-content: space-between;
        }

        .episode_card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
        }

        .pag_box {
            width: 100%;
            display: flex;
            justify-content: space-around;
            margin-top: 25px;
        }

        .logo {
            position: relative;
            width: 45%;
            display: flex;
        }

        .logo > .season_episode {
            z-index: 10000;
            background-color: rgba(0, 0, 0, .8);
            position: absolute;
            bottom: 0;
            width: 99%;
            color: #dcdcdc;
            text-align: right;
            padding: 0 10px;
            font-size: 14px;
            height: 30px;
            line-height: 30px;
        }

        .info {
            margin-left: 20px;
            display: flex;
            flex-direction: column;
            width: 55%;
        }

        .info > div {
            width: 100%;
        }

        .series_title {
            height: 40px;
            font-weight: 600;
            font-size: 16px;
        }
        .row {
            padding: 5px 10px;
        }
        .episode_title {
            font-weight: 400;
            font-size: 14px;
        }

        .date, .date_text {
            font-size: 12px;
        }

        .date {
            margin-left: 10px;
        }
    </style>
    <div class="episodes">
        @foreach($episodes as $episode)
            <div class="episode_card">
                <a class="logo" href="{{ $episode->externalLink }}" target="_blank">
                    <img src="{{ $episode->image_url }}"
                         title="{{ $episode->series->description->title }}: {{ $episode->description->title }} Сезон {{ $episode->season_number }} Серия {{ $episode->episode_number }}"
                         alt="{{ $episode->series->description->title }}: {{ $episode->description->title }} Сезон {{ $episode->season_number }} Серия {{ $episode->episode_number }}">
                    <div class="season_episode">Сезон {{ $episode->season_number }}
                        Серия {{ $episode->episode_number }}</div>
                </a>
                <div class="info">
                    <div class="row">
                        <div class="series_title">{{ $episode->series->description->title }}</div>
                    </div>
                    <div class="row">
                        <div class="episode_title">{{ $episode->description->title }}</div>
                    </div>
                    <div class="row">
                        <div class="date_text">Дата выхода:</div>
                        <div class="date">{{ $episode->description->release_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="pag_box">
            {{ $episodes->appends(request()->except('page'))->links() }}
        </div>
    </div>
@endsection