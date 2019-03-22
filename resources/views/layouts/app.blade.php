<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @if (isset($title))
        <title>{{ $title }}</title>
    @else
        <title>{{ config('app.name', 'Laravel') }}</title>
    @endif
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="fragment" content="!">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<style>
    body {
        background-color: #dcdcdc;;
        padding: 0 !important;
        font-family: Roboto, sans-serif;
    }
</style>
@include('layouts.nav')

<main>
    @yield('content')
</main>

</body>
</html>
