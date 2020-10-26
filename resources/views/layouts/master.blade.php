<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ (Auth::user()) ? Auth::user()->api_token : '' }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('fontawesome/css/all.min.css') }}" >
    @yield('page_specific_styles')
    <title>
        {{ config('app.name') }}
        @if(View::hasSection('title'))
            :: @yield('title')
        @endif
    </title></head>
<body>

<div id="app">
    @yield('app')
</div>

<script src="{{ mix('js/app.js') }}" type="text/javascript"></script>
</body>
</html>

