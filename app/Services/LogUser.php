<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LogUser
{
    protected $generateID;

    public function __construct(GenerateID $generateID) {
        $this->generateID = $generateID;
    }

    public function logUser($id, $ip, $codeSal, $agence) {
        DB::table('t_log_util')
            ->insert([
                'id' => $id,
                'IP' => $ip,
                'Util' => $codeSal,
                'Agence' => $agence,
                'DateAcces' => date('Y-m-d'),
                'HeureAcces' => date('H:i'),
                'Demat' => '']);
    }
}
