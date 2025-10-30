<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckTimeOut
{
    public function checkAttempts($codeSal): int
    {
        $userAttempts = DB::table('t_login_attempts')
            ->where('CodeSal', '=', $codeSal)
            ->first();

        if (!$userAttempts) {
            DB::table('t_login_attempts')
                ->insert([
                    'CodeSal' => $codeSal,
                    'Essais' => 1,
                    'DernierEssai' => date('Y-m-d H:i:s')
                ]);

            return 1;
        } else {
            $dernierEssai = Carbon::parse($userAttempts->DernierEssai);

            if ($dernierEssai->lt(now()->subMinute(5))) {
                DB::table('t_login_attempts')
                    ->where('CodeSal', '=', $codeSal)
                    ->update([
                        'Essais' => 1,
                        'DernierEssai' => date('Y-m-d H:i:s')
                    ]);
            } else {
                DB::table('t_login_attempts')
                    ->where('CodeSal', '=', $codeSal)
                    ->update([
                        'Essais' => $userAttempts->Essais + 1,
                        'DernierEssai' => date('Y-m-d H:i:s')
                    ]);
            }
        }

        return $userAttempts->Essais + 1;
    }

    public function isUserTimedOut($codeSal):bool
    {
        $isUserTimedOut = DB::table('t_timeout')
            ->where('CodeSal', '=', $codeSal)
            ->first();

        if ($isUserTimedOut) {
            $dateTimeOut = Carbon::parse($isUserTimedOut->DateTimeout);

            if ($dateTimeOut->lt(now()->subMinutes(5))) {
                $this->untimeOutUser($codeSal);

                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function timeOutLeftTime($codeSal): string
    {
        $dateTimeOut = DB::table('t_timeout')
            ->select('DateTimeout')
            ->where('CodeSal', '=', $codeSal)
            ->first();

        $timeOutStart = Carbon::parse($dateTimeOut->DateTimeout);
        $timeOutEnd = $timeOutStart->addMinutes(5);

        $waitingTimeLeft = now()->diffInSeconds($timeOutEnd, false);

        if ($waitingTimeLeft > 0) {
            $minutes = floor($waitingTimeLeft / 60);
            $seconds = $waitingTimeLeft % 60;

            $msg = "Nombre d'essais limite atteint, veuillez patienter {$minutes}m {$seconds}s pour pouvoir réessayer.";
        } else {
            $msg = "Vous pouvez réessayer.";
        }

        return $msg;
    }

    public function timeOutUser($codeSal): void
    {
        $this->unsetAttempts($codeSal);

        DB::table('t_timeout')
            ->insert([
                'CodeSal' => $codeSal,
                'DateTimeout' => date('Y-m-d H:i:s')
            ]);
    }

    public function untimeOutUser($codeSal): void
    {
        DB::table('t_timeout')
            ->where('CodeSal', '=', $codeSal)
            ->delete();
    }

    public function unsetAttempts($codeSal)
    {
        DB::table('t_login_attempts')
            ->where('CodeSal', '=', $codeSal)
            ->delete();
    }
}
