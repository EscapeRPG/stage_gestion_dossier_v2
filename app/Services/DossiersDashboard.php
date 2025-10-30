<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DossiersDashboard
{
    public function getDossiersDashboard($codeSal): array
    {
        $today = date("Y-m-d");
        $codeAgence = session('user')->CodeAgSal;

        if ($codeAgence === 'PLUS') {
            $agences = DB::table('t_resp')
                ->select('CodeAgSal')
                ->where('CodeSal', $codeSal)
                ->pluck('CodeAgSal')
                ->toArray();

            $dossiers = DB::table('t_interventions')
                ->select('NumInt')
                ->where(function ($query) use ($agences) {
                    foreach ($agences as $ag) {
                        $query->orWhere('NumInt', 'like', $ag . '%');
                    }
                })
                ->where('Date_Enr', '>=', now()->subMonths(3))
                ->distinct()
                ->get();
        } else {
            $pattern = $codeAgence === 'DOAG' ? 'M%' : $codeAgence . '%';

            $dossiers = DB::table('t_interventions')
                ->select('NumInt')
                ->where('NumInt', 'like', $pattern)
                ->where('Date_Enr', '>=', now()->subMonths(3))
                ->distinct()
                ->get();
        }

        $planning = DB::table('t_histoappels')
            ->where('Tech_Affecte', $codeSal)
            ->orWhere('Tech_RDV', $codeSal)
            ->where('AFaire_Date', '>=', $today)
            ->orWhere('Date_RDV', '>=', $today)
            ->get();
        $rdvs = DB::table('t_planning')
            ->where('Tech_RDV', $codeSal)
            ->where('Date_RDV', '>=', $today)
            ->get()
            ->groupBy('Num_Int');
        $numInts = $planning->pluck('Num_Int')->unique();
        $allToDos = DB::table('t_reponsesappels')
            ->whereIn('NumInt', $numInts)
            ->where('Type', 'À Faire')
            ->where('AFaire_Date', '>=', $today)
            ->get()
            ->groupBy(function ($item) {
                return $item->NumInt . '|' . $item->Date . '|' . $item->Heure;
            });

        $timelineToday = collect();
        $timeline = collect();

        foreach ($planning as $item) {
            if (!empty($item->AFaire_Date)) {
                $key = $item->Num_Int . '|' . $item->Date_MAJ . '|' . $item->Heure_MAJ;
                $toDos = $allToDos[$key] ?? collect();

                if ($toDos->isNotEmpty()) {
                    $entry = (object)[
                        'type' => 'tache',
                        'dossier' => $item->Num_Int,
                        'heure' => substr($item->AFaire_Heure, 0, 5),
                        'date' => $item->AFaire_Date,
                        'prio' => $item->prio,
                        'contenu' => $toDos->pluck('Question')->unique()->values()->all(),
                    ];

                    if ($item->AFaire_Date === $today) {
                        $timelineToday[$key] = $entry;
                    } else {
                        $timeline[$key] = $entry;
                    }
                }
            }

            foreach ($rdvs as $numInt => $rdvGroup) {
                foreach ($rdvGroup as $rdv) {
                    $keyRdv = $rdv->Num_Int . '|' . $rdv->Date_RDV . '|' . $rdv->Heure_RDV;

                    $entry = (object)[
                        'type' => 'rdv',
                        'dossier' => $rdv->Num_Int,
                        'heure' => substr($rdv->Heure_RDV, 0, 5),
                        'date' => $rdv->Date_RDV,
                        'prio' => null,
                        'contenu' => [$rdv->Nom_Cli ?? 'Client inconnu'],
                    ];

                    if ($rdv->Date_RDV === $today) {
                        $timelineToday[$keyRdv] = $entry;
                    } else {
                        $timeline[$keyRdv] = $entry;
                    }
                }
            }
        }

        $groupByDate = function (Collection $entries) {
            return $entries
                ->values()
                ->sortBy(function ($e) {
                    return Carbon::parse($e->date);
                })
                ->groupBy(function ($e) {
                    Carbon::setLocale('fr');
                    return Carbon::parse($e->date)->translatedFormat('d M Y');
                })
                ->map(function ($group) {
                    return $group->sortBy('heure')->values();
                });
        };

        return [
            'dossiers' => $dossiers,
            'timelineToday' => $groupByDate($timelineToday),
            'timeline' => $groupByDate($timeline),
        ];
    }
}
