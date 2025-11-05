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
                                    <span class="dossier-urgent">!</span>
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
