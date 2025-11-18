<table>
    <thead>
    <tr>
        <th>Numéro de dossier</th>
        <th>Client</th>
        <th>Technicien</th>
        <th>À faire le</th>
        <th>Tâches</th>
        <th>Rendez-vous</th>
    </tr>
    </thead>

    <tbody>
    @foreach($paged as $dossier)
        @php
            $todos = $dossier->actions->where('type', 'tache');
            $rdvs = $dossier->actions->where('type', 'rdv');
            $firstAction = $dossier->actions->sortBy('date')->first();
            $groupedActions = $todos->groupBy(function($todo) {
                $date = $todo->date ?? '';
                $heure = $todo->heure ?? '';
                $tech  = $todo->tech ?? '';
                return $date . '|' . $heure . '|' . $tech;
            });
        @endphp

        <tr>
            <td>
                <a href="/ClientInfo?id={{ session('user')->idUser }}&action=detail-dossier&numInt={{ $dossier->NumInt }}">
                    {{ $dossier->NumInt }}
                </a>
            </td>

            <td style="text-align: left">
                <strong>{{ $dossier->Nom_Cli }}</strong>
                <span class="adresse">
                    {{ $dossier->Marque }} - {{ $dossier->Type_App }}
                </span>
            </td>

            <td>
                @foreach($groupedActions as $key => $group)
                    @php
                        $parts = explode('|', $key);
                        $tech = $parts[2] ?? '';
                        $isCurrentUser = $tech === session('user')->CodeSal;
                    @endphp
                    <span class="{{ $isCurrentUser ? 'highlight-tech' : '' }}">
                        {{ $tech }}
                    </span>
                @endforeach
            </td>

            <td>
                @foreach($groupedActions as $key => $group)
                    @php
                        $parts = explode('|', $key);
                        $date = $parts[0] ?? '';
                        $heure = $parts[1] ?? '';
                        if (!$date) continue;
                        [$year, $month, $day] = explode('-', $date);
                        $dateFormatee = implode('/', [$day, $month]);
                    @endphp

                    <strong>{{ $dateFormatee }}</strong> à <strong>{{ substr($heure, 0, 5) }}</strong>
                @endforeach
            </td>

            <td>
                <div class="tickets">
                    @foreach($groupedActions as $key => $group)
                        @foreach($group as $todo)
                            @foreach($todo->contenu as $question)
                                <div class="actions tache">
                                    {{ $question }}
                                    @if($todo->prio === 'O')
                                        <span class="dossier-urgent">!</span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            </td>

            <td>
                <div class="tickets">
                    @foreach($rdvs as $rdv)
                        @php
                            [$year, $month, $day] = explode('-', $rdv->date);
                            $dateFormatee = implode('/', [$day, $month]);
                        @endphp

                        <div class="actions rdv">
                            <strong>{{ $dateFormatee }}</strong> à <strong>{{ substr($rdv->heure, 0, 5) }}</strong> :
                            <em>{{ $rdv->tech }}</em>
                        </div>
                    @endforeach
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="pagination-links">
    {{ $paged->links() }}
</div>
