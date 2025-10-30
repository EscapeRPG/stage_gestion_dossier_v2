<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetDayInfo
{
    public function getDayInfo($date): Collection
    {
        $rdvs = DB::table('t_planning')
            ->whereDate('Date_RDV', $date)
            ->orderBy('Heure_RDV', 'asc')
            ->select(
                'Num_Int',
                'Code_Sal',
                'Heure_Enrg',
                'Tech_RDV',
                'Date_RDV',
                'Heure_RDV',
                'Valide',
                'Nom_Cli',
                'Adresse_Cli',
                'Ville_Cli',
                'Num_Tel_Cli',
                'Mail_Cli'
            )
            ->get()
            ->map(fn($item) => (object)[
                'type' => 'rdv',
                'NumInt' => $item->Num_Int,
                'Code_Sal' => $item->Code_Sal,
                'Tech_Affecte' => null,
                'Tech_RDV' => $item->Tech_RDV,
                'Valide' => $item->Valide,
                'date' => $item->Date_RDV,
                'heure' => $item->Heure_RDV,
                'Heure_Enrg' => $item->Heure_Enrg,
                'Nom_Cli' => $item->Nom_Cli ?? 'Client inconnu',
                'Adresse_Cli' => $item->Adresse_Cli,
                'Ville_Cli' => $item->Ville_Cli,
                'Num_Tel_Cli' => $item->Num_Tel_Cli,
                'Mail_Cli' => $item->Mail_Cli,
            ]);

        $histos = DB::table('t_histoappels')
            ->whereDate('AFaire_Date', $date)
            ->select(
                'Num_Int',
                'Code_Sal',
                'Heure_MAJ',
                'Tech_Affecte',
                'Tech_RDV',
                'AFaire_Date',
                'AFaire_Heure',
                'Nom_Cli'
            )
            ->get();

        $tachesGrouped = DB::table('t_reponsesappels')
            ->where('Type', 'À Faire')
            ->whereDate('AFaire_Date', $date)
            ->select('NumInt', 'Question', 'AFaire_Heure', 'AFaire_Tech')
            ->get()
            ->groupBy(fn($item) => $item->NumInt . '|' . ($item->AFaire_Tech ?? '') . '|' . $item->AFaire_Heure);

        $todos = $histos->map(function ($item) use ($tachesGrouped) {
            $key = $item->Num_Int . '|' . ($item->Tech_Affecte ?? '') . '|' . $item->AFaire_Heure;

            return (object)[
                'type' => 'tache',
                'NumInt' => $item->Num_Int,
                'Code_Sal' => $item->Code_Sal,
                'Tech_Affecte' => $item->Tech_Affecte,
                'Tech_RDV' => $item->Tech_RDV,
                'Heure_Enrg' => $item->Heure_MAJ,
                'date' => $item->AFaire_Date,
                'heure' => $item->AFaire_Heure,
                'Nom_Cli' => $item->Nom_Cli ?? 'Client inconnu',
                'taches' => $tachesGrouped[$key] ?? collect(),
            ];
        });

        $todos = $todos
            ->groupBy(fn($item) => ($item->Tech_Affecte ?? '') . '|' . $item->heure)
            ->map(function ($group) {
                $first = $group->first();
                $mergedTasks = $group->flatMap(fn($t) => $t->taches)->unique(fn($t) => $t->Question)->values();

                return (object)[
                    'type' => 'tache',
                    'Code_Sal' => $first->Code_Sal,
                    'Tech_Affecte' => $first->Tech_Affecte,
                    'Tech_RDV' => $first->Tech_RDV,
                    'Heure_Enrg' => $first->Heure_Enrg,
                    'date' => $first->date,
                    'heure' => $first->heure,
                    'Nom_Cli' => $group->pluck('Nom_Cli')->unique()->implode(', '),
                    'taches' => $mergedTasks,
                    'NumInt' => $group->pluck('NumInt')->unique()->values(),
                ];
            })
            ->values();

        return $rdvs->merge($todos)
            ->sortBy('heure')
            ->values();
    }
}
