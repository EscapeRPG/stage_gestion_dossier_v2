<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateHeureIntervention
{
    public function updateHeureIntervention($validated): JsonResponse
    {
        try {
            if ($validated['type'] === 'rdv') {
                DB::table('t_planning')
                    ->where('Num_Int', $validated['numInt'])
                    ->whereDate('Date_RDV', $validated['date'])
                    ->where('Heure_RDV', $validated['prevHeure'])
                    ->update([
                        'Heure_RDV' => $validated['heure'],
                        'Tech_RDV' => $validated['technicien']
                    ]);
            } else {
                DB::table('t_histoappels')
                    ->where('Num_Int', $validated['numInt'])
                    ->whereDate('AFaire_Date', $validated['date'])
                    ->where('AFaire_Heure', $validated['prevHeure'])
                    ->update([
                        'AFaire_Heure' => $validated['heure'],
                        'Tech_Affecte' => $validated['technicien']
                    ]);
            }

            return response()->json(['status' => 'success']);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
