@extends('layouts.app')

@section('title', 'Suivi de contrat')

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('css/suivi-dossiers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/today-calendar.css') }}">
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

        <form action="/ClientInfo?id={{ session('user')->idUser }}&action=detail-dossier&numInt=" method="post"
              class="dossier-search" id="numIntForm">
            @csrf

            <input list="liste-dossiers" name="dossier-list" id="dossier-list" placeholder="Choisissez ou entrez un numéro de dossier">
            <datalist id="liste-dossiers">
                @foreach($dossiers as $dossier)
                    <option value="{{ $dossier->NumInt }}"></option>
                @endforeach
            </datalist>

            <button>Rechercher</button>

            <input type="hidden" id="id" value="{{ session('user')->idUser }}">
        </form>

        <div class="planning-day">
            <h2>Planning</h2>
            <h3>Aujourd'hui</h3>
            <div class="today-calendar">
                @php
                    $now = now()->minute < 30
                        ? now()->setTime(now()->hour, 0)
                        : now()->setTime(now()->hour, 30);

                    $next = $now->copy()->addMinutes(29);
                @endphp

                @for ($i = 9; $i < 18; $i++)
                    @for ($j = 0; $j <= 30; $j += 30)
                        @php
                            $hourStr = sprintf('%02d:%02d', $i, $j);
                            $boucleTime = \Carbon\Carbon::createFromFormat('H:i', $hourStr);

                            if ($boucleTime->between($now, $next)) {
                                $class = 'now';
                            } elseif ($boucleTime->lt($now)) {
                                $class = 'passed';
                            } else {
                                $class = '';
                            }
                        @endphp

                        <div class="horaire {{ $class }}">
                            <p>{{ $hourStr }}</p>

                            @if ($timelineToday->isNotEmpty())
                                @foreach ($timelineToday as $heure => $events)
                                    @foreach ($events as $event)
                                        @if($event->heure === $hourStr)
                                            @foreach ($event->contenu as $item)
                                                <a href="/ClientInfo?id={{ session('user')->idUser }}&action=detail-dossier&numInt={{ $event->dossier }}">
                                                    <div
                                                        class="actions {{ $event->type }} {{ $event->prio === 'O' ? 'prio' : '' }}">
                                                        {{ $item }}
                                                        <div class="dossier {{ $event->type }}">
                                                            #{{ $event->dossier }}
                                                        </div>
                                                        @if ($event->prio === 'O')
                                                            <span class="dossier-urgent">
                                                                !
                                                            </span>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endforeach
                            @endif
                        </div>
                    @endfor
                @endfor
            </div>

            @if ($timelineToday->isEmpty())
                <p>Pas de tâche spécifique prévue aujourd'hui.</p>
            @endif

            <h3>À venir</h3>
            <div class="next-calendar">
                @if ($timeline->isNotEmpty())
                    @foreach ($timeline as $date => $events)
                        <div class="horaire">
                            <p>{{ $date }}</p>

                            @foreach ($events as $event)
                                @foreach ($event->contenu as $item)
                                    <a href="/ClientInfo?id={{ session('user')->idUser }}&action=detail-dossier&numInt={{ $event->dossier }}">
                                        <div
                                            class="actions {{ $event->type }} {{ $event->prio === 'O' ? 'prio' : '' }}">
                                            {{ $item }}
                                            <div class="dossier {{ $event->type }}">
                                                #{{ $event->dossier }}
                                            </div>
                                            @if ($event->prio === 'O')
                                                <span class="dossier-urgent">
                                                    !
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>

            @if ($timeline->isEmpty())
                <p>Pas de tâche spécifique prévue pour les jours à venir.</p>
            @endif
        </div>
    </main>

    <script src="{{ asset('js/numint-redirect.js') }}"></script>
@endsection
