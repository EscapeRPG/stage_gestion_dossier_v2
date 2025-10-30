<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function getRDV(Request $request): JsonResponse
    {
        $date = $request->query('date');

        $rdvs = DB::table('t_planning as p')
            ->leftJoin('t_interventions as t', 't.NumInt', '=', 'p.Num_Int')
            ->select('t.NumInt', 't.Nom_Cli', 't.Adresse_Cli', 't.CP_Cli', 't.Ville_Cli', 'p.Date_RDV', 'p.Heure_RDV', 'p.Tech_RDV')
            ->whereDate('p.Date_RDV', '=', $date)
            ->get()
            ->map(function ($item) {
                return [
                    'num' => $item->NumInt,
                    'nom' => $item->Nom_Cli ?: 'Client inconnu',
                    'adresse' => "{$item->Adresse_Cli}, {$item->CP_Cli} {$item->Ville_Cli}",
                    'date' => $item->Date_RDV,
                    'heure' => $item->Heure_RDV,
                    'technicien' => $item->Tech_RDV,
                ];
            });

        return response()->json($rdvs);
    }

    public function generateMap()
    {
        return view('map');
    }
}
