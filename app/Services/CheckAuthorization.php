<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckAuthorization
{
    protected CheckHours $checkHours;
    protected CheckAgency $checkAgency;

    public function __construct(CheckHours $checkHours, CheckAgency $checkAgency)
    {
        $this->checkHours = $checkHours;
        $this->checkAgency = $checkAgency;
    }

    public function checkId($id): ?array
    {
        $log = DB::table('t_log_util')
            ->where('id', '=', $id)
            ->first();

        if (!$log) {
            return null;
        }

        $salarie = DB::table('t_salarie as s')
            ->select(
                's.CodeSal',
                's.NomSal',
                's.CodeAgSal'
            )
            ->where('s.CodeSal', $log->Util)
            ->first();

        return [
            'codeSal' => $salarie->CodeSal,
            'nomSal' => $salarie->NomSal,
            'codeAgSal' => $salarie->CodeAgSal,
            'ip' => $log->IP,
            'date' => $log->DateAcces
        ];
    }

    /**
     * @throws Exception
     */
    public function checkAuth($id): bool
    {
        if (!Session::has('user')) {
            throw new Exception('disconnected');
        }

        $salarie = $this->checkId($id);
        if ($salarie === null) {
            throw new Exception('wrongID');
        }

        if (!$this->checkHours->checkHours($salarie['codeSal'])) {
            throw new Exception('wrongHours');
        }

        if (!$this->checkAgency->checkAgency($salarie['codeSal'], $salarie['codeAgSal'], 'M44N')) {
            throw new Exception('wrongAgency');
        }

        return true;
    }
}
