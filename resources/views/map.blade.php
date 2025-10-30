@extends('layouts.app')

@section('title', 'Carte des rendez-vous')

@section('stylesheets')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/map.css') }}">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    <main>
        <div id="sidebar">
            <h2>Rendez-vous du jour</h2>
            <div id="rdvList">Chargement...</div>
        </div>

        <div class="map-container">
            <div id="map"
                 data-client='@json($client)'
                 data-entreprise='@json($entreprise)'>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/map.js') }}"></script>
@endsection
