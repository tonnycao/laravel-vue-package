<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ (Auth::user()) ? Auth::user()->api_token : '' }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app_bmt.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}" >
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <title>
        {{ config('app.name') }}
        @if(View::hasSection('title'))
            :: @yield('title')
        @endif
    </title>
</head>
<body>
<style type="text/css">
    .level{display:flex;align-items:center;}
    .flex{flex:1;}
</style>
<div>
    @include('layouts.navbar')
    <div class="container" style="padding-top:20px;">
        @yield('content')
    </div>
</div>
@yield('css')
<script src="/vendors/bootstrap/assets/js/jquery.js"></script>
<script src="/vendors/bootstrap/dist/js/bootstrap.js"></script>
@yield('js')
</body>
</html>