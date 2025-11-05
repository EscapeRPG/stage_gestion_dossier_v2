<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GetInterventionDetail
{
    public function getInterventionDetail($numInt): object
    {
        $intervention = DB::table('t_interventions')
            ->where('NumInt', 'like', $numInt)
            ->first();
        if (!$intervention) {
            return (object) [
                'success' => false,
                'message' => "$numInt : numéro de dossier incorrect.",
                'data' => null
            ];
        }

        $historique = DB::table('t_histoappels')
            ->where('Num_Int', 'like', $numInt)
            ->orderBy('Date_MAJ', 'asc')
            ->get();
        $intervention->historique = $historique;

        $rdv = DB::table('t_planning')
            ->select('Date_RDV', 'Heure_RDV', 'Tech_RDV', 'Valide', 'Obsolete')
            ->where('Num_Int', 'like', $numInt)
            ->where('Obsolete', '=', 'N')
            ->first();
        $intervention->rdv = $rdv;

        $questionsEtat = DB::table('t_questionsappels')
            ->orderBy('Ordre', 'asc')
            ->get();
        $intervention->questions = $questionsEtat;

        $codeAgence = substr($intervention->NumInt, 0, 4);
        $intervention->codeAgence = $codeAgence;

        $salaries = DB::table('t_salarie')
            ->leftJoin('t_resp', 't_resp.CodeSal', '=', 't_salarie.CodeSal')
            ->select('t_salarie.CodeSal', 't_salarie.CodeAgSal', 't_resp.CodeAgSal as RespAg', 't_resp.Defaut')
            ->whereIn('t_salarie.CodeAgSal', ['ADMI', 'DOAG', $codeAgence])
            ->orWhere(function ($query) use ($codeAgence) {
                $query->where('t_salarie.CodeAgSal', 'PLUS')
                    ->whereExists(function ($sub) use ($codeAgence) {
                        $sub->select(DB::raw(1))
                            ->from('t_resp')
                            ->whereColumn('t_resp.CodeSal', 't_salarie.CodeSal')
                            ->where('t_resp.CodeAgSal', $codeAgence);
                    });
            })
            ->get()
            ->groupBy('CodeSal')
            ->map(function ($items) use ($codeAgence) {
                $s = $items->first();
                if ($s->CodeAgSal === $codeAgence) {
                    return [
                        'group' => 'Techniciens',
                        'value' => $s->CodeSal,
                    ];
                }
                if ($items->contains(fn($r) => $r->RespAg === $codeAgence && $r->Defaut === 'O')) {
                    return [
                        'group' => 'Responsables',
                        'value' => $s->CodeSal,
                    ];
                }
                return [
                    'group' => 'Autres',
                    'value' => $s->CodeSal,
                ];
            })
            ->values();
        $grouped = $salaries->groupBy('group')->map(function ($group) {
            return $group->pluck('value')->unique()->sort()->values();
        });
        $desiredOrder = [
            'Techniciens',
            'Responsables',
            'Autres'
        ];
        $ordered = collect();
        foreach ($desiredOrder as $label) {
            if (isset($grouped[$label]) && $grouped[$label]->isNotEmpty()) {
                $ordered[$label] = $grouped[$label];
            }
        }
        $intervention->salaries = $ordered;

        return (object) [
            'success' => true,
            'message' => null,
            'data' => $intervention
        ];
    }
}
