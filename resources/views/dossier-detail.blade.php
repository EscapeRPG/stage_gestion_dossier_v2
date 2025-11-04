@extends('layouts.app')

@section('title', 'Suivi de contrat')

@section('stylesheets')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/gestion-dossiers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/historique.css') }}">
    <link rel="stylesheet" href="{{ asset('css/today-calendar.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection

@section('content')
    <main>
        <div class="main-container">
            @include('includes.calendrier')

            <form action="/ClientInfo?id={{ session('user')->idUser }}&action=save-dossier&numInt={{ $intervention->NumInt }}" method="post" class="dossier-detail-form">
                @csrf

                <div class="dossier-detail-container">
                    <div class="container">
                        @include('includes.historique')
                        @include('includes.etat-int', ['intervention' => $intervention])
                        @include('includes.commentaires-int')
                    </div>
                    <div class="planning-container">
                        @include('includes.planning', ['intervention' => $intervention])
                    </div>
                </div>

                <input type="hidden" name="numInt" id="numInt" value="{{ $intervention->NumInt }}">
                <button class="save-btn">Enregistrer le dossier</button>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/historique.js') }}"></script>
    <script src="{{ asset('js/show-hide.js') }}"></script>
    <script src="{{ asset('js/auto-checks.js') }}"></script>
    <script src="{{ asset('js/gestion-dossier.js') }}" type="module"></script>
@endsection
