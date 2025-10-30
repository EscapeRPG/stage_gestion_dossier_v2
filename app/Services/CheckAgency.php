<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckAgency
{
    public function checkAgency($codeSal, $agence, $currentAgence): bool
    {
        if ($agence === 'ADMI') {
            return true;
        }

        if ($agence === 'PLUS') {
            $agenceBDD = DB::table('t_resp')
                ->select('CodeAgSal as respAg')
                ->where('CodeSal', $codeSal)
                ->get();

            if ($agenceBDD->isEmpty()) {
                return false;
            }

            return $agenceBDD->contains('respAg', $currentAgence);
        }

        if ($agence === 'DOAG') {
            if (str_starts_with($currentAgence, 'M') || str_starts_with($currentAgence, 'C')) {
                return true;
            } else {
                return false;
            }
        }

        return $currentAgence === $agence;
    }
}
