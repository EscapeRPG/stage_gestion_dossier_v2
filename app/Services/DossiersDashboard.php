<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DossiersDashboard
{
    public function getDossiersDashboard(): Collection
    {
        $codeAgence = session('user')->CodeAgSal;
        return DB::table('t_interventions')
            ->where(function ($query) use ($codeAgence) {
                if ($codeAgence === 'PLUS') {
                    $agences = DB::table('t_resp')
                        ->where('CodeSal', session('user')->CodeSal)
                        ->pluck('CodeAgSal')
                        ->toArray();
                    $query->where(function ($q) use ($agences) {
                        foreach ($agences as $ag) {
                            $q->orWhere('NumInt', 'like', $ag . '%');
                        }
                    });
                } else {
                    $pattern = $codeAgence === 'DOAG' ? 'M%' : $codeAgence . '%';
                    $query->where('NumInt', 'like', $pattern);
                }
            })
            ->where('Date_Fin_Int', '=', '0000-00-00')
            ->where('Lieu_Int', 'like', 'site')
            ->select('NumInt', 'Nom_Cli', 'Adresse_Cli', 'CP_Cli', 'Ville_Cli', 'Num_Tel_Cli', 'Mail_Cli')
            ->get();
    }

    public function getTodayCalendar(): Collection
    {
        $today = date("Y-m-d");
        $timelineToday = collect();

        $todayTodos = DB::table('t_reponsesappels')
            ->where('Type', 'À Faire')
            ->where('AFaire_Date', $today)
            ->where('Obsolete', 'N')
            ->get();
        foreach ($todayTodos as $todo) {
            $timelineToday->push((object)[
                'type' => 'tache',
                'dossier' => $todo->NumInt,
                'heure' => substr($todo->AFaire_Heure, 0, 5),
                'date' => $todo->AFaire_Date,
                'prio' => $todo->prio ?? 'N',
                'contenu' => [$todo->Question],
                'tech' => $todo->AFaire_Tech,
            ]);
        }

        $todayRdvs = DB::table('t_planning')
            ->where('Date_RDV', $today)
            ->where('Obsolete', 'N')
            ->get();
        foreach ($todayRdvs as $rdv) {
            $timelineToday->push((object)[
                'type' => 'rdv',
                'dossier' => $rdv->Num_Int,
                'heure' => substr($rdv->Heure_RDV, 0, 5),
                'date' => $rdv->Date_RDV,
                'prio' => null,
                'contenu' => [$rdv->Nom_Cli],
                'tech' => $rdv->Tech_RDV,
            ]);
        }

        return $timelineToday->sortBy('heure');
    }

    public function getToDoNext($dossiers): Collection
    {
        $tech = session('user')->CodeSal;
        $actions = DB::table('t_interventions as i')
            ->leftJoin('t_reponsesappels as t', 't.NumInt', '=', 'i.NumInt')
            ->leftJoin('t_planning as p', 'p.Num_Int', '=', 'i.NumInt')
            ->whereIn('i.NumInt', $dossiers->pluck('NumInt'))
            ->where('t.Obsolete', 'N')
            ->select(
                'i.NumInt',
                'i.Nom_Cli', 'i.Adresse_Cli', 'i.CP_Cli', 'i.Ville_Cli', 'i.Num_Tel_Cli', 'i.Mail_Cli',
                't.Type as t_type', 't.AFaire_Tech', 't.AFaire_Date', 't.AFaire_Heure', 't.Question', 't.prio',
                'p.Tech_RDV', 'p.Date_RDV', 'p.Heure_RDV', 'p.Nom_Cli as p_nom_cli'
            )
            ->get();

        $grouped = $dossiers->map(function ($dossier) use ($actions, $tech) {
            $actionsForDossier = $actions->where('NumInt', $dossier->NumInt);

            $taches = $actionsForDossier->filter(fn($a) => $a->t_type === 'À Faire')->map(function ($a) {
                return (object)[
                    'type' => 'tache',
                    'tech' => $a->AFaire_Tech,
                    'date' => $a->AFaire_Date,
                    'heure' => $a->AFaire_Heure,
                    'prio' => $a->prio,
                    'contenu' => [$a->Question],
                ];
            });

            $rdvs = $actionsForDossier->filter(fn($a) => $a->Date_RDV)->map(function ($a) {
                return (object)[
                    'type' => 'rdv',
                    'tech' => $a->Tech_RDV,
                    'date' => $a->Date_RDV,
                    'heure' => $a->Heure_RDV,
                    'contenu' => [$a->p_nom_cli],
                ];
            })
                ->unique(fn($rdv) => $rdv->date . '|' . $rdv->heure . '|' . $rdv->tech)
                ->values();

            $priority = 4;
            if ($taches->contains(fn($t) => $t->prio === 'O' && $t->tech === $tech)) $priority = 1;
            elseif ($taches->contains(fn($t) => $t->prio === 'O')) $priority = 2;
            elseif ($taches->contains(fn($t) => $t->tech === $tech)) $priority = 3;

            $date = collect([$taches, $rdvs])->flatten()->pluck('date')->filter()->sort()->first();
            $heure = collect([$taches, $rdvs])->flatten()->pluck('heure')->filter()->sort()->first();

            return (object)[
                'NumInt' => $dossier->NumInt,
                'Nom_Cli' => $dossier->Nom_Cli,
                'Adresse_Cli' => $dossier->Adresse_Cli,
                'CP_Cli' => $dossier->CP_Cli,
                'Ville_Cli' => $dossier->Ville_Cli,
                'Num_Tel_Cli' => $dossier->Num_Tel_Cli,
                'Mail_Cli' => $dossier->Mail_Cli,
                'actions' => $taches->merge($rdvs),
                'priority' => $priority,
                'date' => $date,
                'heure' => $heure,
            ];
        });

        return $grouped
            ->sortBy(function ($item) {
                return sprintf(
                    '%03d_%s_%s',
                    $item->priority ?? 999,
                    $item->date ?? '9999-99-99',
                    $item->heure ?? '99:99:99'
                );
            });
    }

    public function paginateCollection(Collection $items, int $perPage, int $page, array $options = []): LengthAwarePaginator
    {
        $offset = ($page - 1) * $perPage;
        return new LengthAwarePaginator(
            $items->slice($offset, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            $options
        );
    }
}
