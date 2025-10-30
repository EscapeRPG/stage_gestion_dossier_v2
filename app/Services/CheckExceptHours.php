<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckExceptHours
{
    public function checkHours($codeSal): bool
    {
        $now = Carbon::now();

        $horairesExcept = DB::table('t_horaireexcept')
            ->where('Code_Sal', $codeSal)
            ->get();

        foreach ($horairesExcept as $horaire) {
            $Date1 = Carbon::parse($horaire->Date1);
            $Date2 = Carbon::parse($horaire->Date2);

            if ($now->between($Date1, $Date2)) {
                $startAM = $horaire->HoraireJour1;
                $endAM = $horaire->HoraireJour2;
                $startPM = $horaire->HoraireJour3;
                $endPM = $horaire->HoraireJour4;

                if ($now->between($startAM, $endAM) || $now->between($startPM, $endPM)) {
                    return true;
                }
            }
        }

        return false;
    }
}
