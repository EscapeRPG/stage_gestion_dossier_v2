<?php

namespace App\Helpers;

use Carbon\Carbon;

if (!function_exists('formatDay')) {
    function formatDay(): string
    {
        $now = Carbon::now()->locale('fr');

        return $now->isoFormat('dd');
    }
}
