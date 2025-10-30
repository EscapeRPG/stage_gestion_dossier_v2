<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function App\Helpers\formatDay;

class CheckHours
{
    public function checkHours($codeSal):bool
    {
        $now = Carbon::now();

        $todayPrefix = formatDay();

        $horaires = DB::table('t_horaire as h')
            ->leftJoin('t_horaireexcept as he', 'h.Code_Sal', '=', 'he.Code_Sal')
            ->select(
                'h.' . $todayPrefix . '1',
                'h.' . $todayPrefix . '2',
                'h.' . $todayPrefix . '3',
                'h.' . $todayPrefix . '4',
                'he.Date1',
                'he.Date2',
                'he.HoraireJour1',
                'he.HoraireJour2',
                'he.HoraireJour3',
                'he.HoraireJour4',
            )
            ->where('h.Code_Sal', $codeSal)
            ->get();

        foreach ($horaires as $horaire) {
            $Date1 = Carbon::parse($horaire->{$todayPrefix . '1'});
            $Date2 = Carbon::parse($horaire->{$todayPrefix . '2'});
            $Date3 = Carbon::parse($horaire->{$todayPrefix . '3'});
            $Date4 = Carbon::parse($horaire->{$todayPrefix . '4'});
            $DateExcept1 = Carbon::parse($horaire->Date1);
            $DateExcept2 = Carbon::parse($horaire->Date2);

            if ($now->between($DateExcept1, $DateExcept2)) {
                $today = Carbon::today();
                $startAM = Carbon::parse($horaire->HoraireJour1)->setDate($today->year, $today->month, $today->day);
                $endAM   = Carbon::parse($horaire->HoraireJour2)->setDate($today->year, $today->month, $today->day);
                $startPM = Carbon::parse($horaire->HoraireJour3)->setDate($today->year, $today->month, $today->day);
                $endPM   = Carbon::parse($horaire->HoraireJour4)->setDate($today->year, $today->month, $today->day);

                if ($now->between($startAM, $endAM) || $now->between($startPM, $endPM)) {
                    return true;
                }
            } else if ($now->between($Date1, $Date2) || ($now->between($Date3, $Date4))) {
                return true;
            }
        }

        return false;
    }
}
