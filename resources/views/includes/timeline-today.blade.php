<h3>Aujourd'hui</h3>

@php
    $now = now()->minute < 30
        ? now()->setTime(now()->hour, 0)
        : now()->setTime(now()->hour, 30);
    $next = $now->copy()->addMinutes(29);
    $eventsByHour = $timelineToday->groupBy('heure');
@endphp

<div class="today-calendar">
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

                $eventsAtHour = $eventsByHour->get($hourStr, collect());
            @endphp

            <div class="horaire {{ $class }}">
                <p>{{ $hourStr }}</p>

                @foreach ($eventsAtHour as $event)
                    <a href="/ClientInfo?id={{ session('user')->idUser }}&action=detail-dossier&numInt={{ $event->dossier }}">
                        <div class="actions {{ $event->type }} {{ $event->prio === 'O' ? 'prio' : '' }}">
                            @foreach($event->contenu as $item)
                                {{ $item }}
                            @endforeach
                            <div class="dossier {{ $event->type }}">
                                #{{ $event->dossier }}
                            </div>
                            @if ($event->prio === 'O')
                                <span class="dossier-urgent">!</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endfor
    @endfor
</div>

@if ($timelineToday->isEmpty())
    <p>Pas de tâche spécifique prévue aujourd'hui.</p>
@endif
