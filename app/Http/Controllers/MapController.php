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
            ->select('t.NumInt', 't.Nom_Cli', 't.Adresse_Cli', 't.CP_Cli', 't.Ville_Cli', 't.Lat_Cli', 't.Lon_Cli', 'p.Date_RDV', 'p.Heure_RDV', 'p.Tech_RDV', 'p.Marque', 'p.Type_App')
            ->whereDate('p.Date_RDV', '=', $date)
            ->where('p.Obsolete', '=', 'N')
            ->get()
            ->map(function ($item) {
                return [
                    'num' => $item->NumInt,
                    'nom' => $item->Nom_Cli ?: 'Client inconnu',
                    'adresse' => "{$item->Adresse_Cli}, {$item->CP_Cli} {$item->Ville_Cli}",
                    'date' => $item->Date_RDV,
                    'heure' => $item->Heure_RDV,
                    'technicien' => $item->Tech_RDV,
                    'machineClient' => $item->Marque . ' - ' . $item->Type_App,
                    'Lat_Cli' => $item->Lat_Cli !== null ? (float)$item->Lat_Cli : null,
                    'Lon_Cli' => $item->Lon_Cli !== null ? (float)$item->Lon_Cli : null
                ];
            });

        return response()->json($rdvs);
    }

    public function generateMap(Request $request)
    {
        $numInt = $request->query('numInt');
        $date = $request->query('date');

        $client = DB::table('t_interventions')
            ->where('NumInt', $numInt)
            ->select('Nom_Cli', 'Adresse_Cli', 'CP_Cli', 'Ville_Cli', 'Marque', 'Type_App')
            ->first();

        $entreprise = [
            'nom' => 'Maintronic',
            'adresse' => 'Parc d\'activité du Moulin, 152 Rue François René de Châteaubriand Bat D4, 44470 Carquefou',
            'lat' => '47.30635848249004',
            'lon' => '-1.4814458294525863'
        ];

        return view('map', compact('client', 'entreprise', 'date'));
    }

    public function reassign(Request $request, $num)
    {
        $request->validate(['technicien' => 'required|string']);

        DB::table('t_planning')->where('Num_Int', $num)
            ->update(['Tech_RDV' => $request->technicien]);

        return response()->json(['success' => true]);
    }
}
