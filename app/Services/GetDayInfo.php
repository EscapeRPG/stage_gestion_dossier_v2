<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetDayInfo
{
    public function getDayInfo($date): Collection
    {
        $rdvs = DB::table('t_planning as p')
            ->join('t_interventions as i', 'i.NumInt', '=', 'p.Num_Int')
            ->whereDate('p.Date_RDV', $date)
            ->where('p.Obsolete', '=', 'N')
            ->orderBy('p.Heure_RDV', 'asc')
            ->select(
                'p.Num_Int',
                'p.Code_Sal',
                'p.Heure_Enrg',
                'p.Tech_RDV',
                'p.Date_RDV',
                'p.Heure_RDV',
                'p.Valide',
                'i.Nom_Cli',
                'i.Adresse_Cli',
                'i.Ville_Cli',
                'i.Num_Tel_Cli',
                'i.Mail_Cli',
                'i.Marque',
                'i.Type_App'
            )
            ->get()
            ->map(fn($item) => (object)[
                'type' => 'rdv',
                'NumInt' => $item->Num_Int,
                'Code_Sal' => $item->Code_Sal,
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
                'Marque' => $item->Marque,
                'Type_App' => $item->Type_App,
            ]);

        $taches = DB::table('t_reponsesappels as r')
            ->join('t_interventions as i', 'i.NumInt', '=', 'r.NumInt')
            ->where('r.Type', 'À Faire')
            ->where('r.Obsolete', '=', 'N')
            ->whereDate('r.AFaire_Date', $date)
            ->select(
                'r.NumInt',
                'r.Question',
                'r.AFaire_Heure',
                'r.AFaire_Tech',
                'r.AFaire_Date',
                'i.Nom_Cli',
                'i.Adresse_Cli',
                'i.Ville_Cli',
                'i.Num_Tel_Cli',
                'i.Mail_Cli'
            )
            ->get()
            ->groupBy(fn($item) => $item->NumInt . '|' . ($item->AFaire_Tech ?? '') . '|' . $item->AFaire_Heure)
            ->map(function ($group) {
                $first = $group->first();

                return (object)[
                    'type' => 'tache',
                    'NumInt' => $first->NumInt,
                    'Tech_Affecte' => $first->AFaire_Tech,
                    'date' => $first->AFaire_Date,
                    'heure' => $first->AFaire_Heure,
                    'Nom_Cli' => $first->Nom_Cli ?? 'Client inconnu',
                    'Adresse_Cli' => $first->Adresse_Cli,
                    'Ville_Cli' => $first->Ville_Cli,
                    'Num_Tel_Cli' => $first->Num_Tel_Cli,
                    'Mail_Cli' => $first->Mail_Cli,
                    'taches' => $group->map(fn($t) => (object)[
                        'question' => $t->Question
                    ]),
                ];
            })
            ->values();

        return $rdvs
            ->merge($taches)
            ->sortBy('heure')
            ->values();
    }
}
