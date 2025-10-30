<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CheckSecretQuestion
{
    public function checkSecretQuestion($codeSal): bool
    {
        return DB::table('t_questions_secretes')
            ->where('Code_Sal', $codeSal)
            ->exists();
    }
}
