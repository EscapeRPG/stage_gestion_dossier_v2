@extends('layouts.app')

@section('title', 'Suivi de contrat')

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('css/suivi-dossiers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/today-calendar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/table.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
@endsection

@section('content')
    <main class="suivi-dossiers">
        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="planning-day">
            @include('includes.timeline-today', ['timelineToday' => $timelineToday])
            @include('includes.upcoming-todos')
        </div>
    </main>

    <script src="{{ asset('js/numint-redirect.js') }}"></script>
    <script src="{{ asset('js/contracts-shown.js') }}"></script>
@endsection
