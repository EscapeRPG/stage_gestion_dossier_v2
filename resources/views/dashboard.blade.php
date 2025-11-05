@extends('layouts.app')

@section('title', 'Dashboard')

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
    <main>
        @if ($errors->has('noSecretQuestion'))
            <div class="no-secret">
                <p>
                    {!! $errors->first('noSecretQuestion') !!}
                </p>
            </div>
        @endif

        @if (isset($successQuestion))
            <div class="success-container">
                <p>{{ $successQuestion }}</p>
            </div>
        @endif

        <div class="dashboard-container">
            <h1>Tableau de bord</h1>

            <p class="id-session">
                Vous êtes connecté avec l'id de session : {{ session('user')->idUser }}
            </p>

            <h2>Vos informations</h2>

            <div class="infos">
                <div class="infos-row">
                    <p>Code Salarié</p>
                    <p>{{ session('user')->CodeSal }}</p>
                </div>
                <div class="infos-row">
                    <p>Nom</p>
                    <p>{{ session('user')->NomSal }}</p>
                </div>
                <div class="infos-row">
                    <p>Agence</p>
                    <p>{{ session('user')->CodeAgSal }}</p>
                </div>
            </div>
        </div>

        <div class="dashboard-container">
            <form action="/ClientInfo?id={{ session('user')->idUser }}&action=suivi-dossiers" method="post">
                @csrf

                <h2>Contrats</h2>

                <div class="applications">
                    <button name="menu" value="automenu9.16" class="blue">
                        Gestion des contrats
                    </button>
                </div>
            </form>
        </div>

        <!-- @include('includes.menus-dashboard') -->
    </main>
@endsection
