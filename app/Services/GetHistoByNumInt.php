<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class GetHistoByNumInt
{
    public function getHistoByNumInt($numInt): JsonResponse
    {
        $histo = DB::table('t_histoappels as h')
            ->where('h.Num_Int', $numInt)
            ->orderBy('h.id', 'desc')
            ->get();

        foreach ($histo as $h) {
            $h->reponses = DB::table('t_reponsesappels')
                ->select('Question', 'Type')
                ->where('NumInt', $numInt)
                ->where('Date', $h->Date_MAJ)
                ->where('Heure', $h->Heure_MAJ)
                ->get();
        }

        return response()->json($histo);
    }
}
