<?php

namespace App\Http\Controllers;

use App\Services\CheckHours;
use App\Services\CheckTimeOut;
use App\Services\generateId;
use App\Services\LogUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (session()->has('user')) {
            $id = session('user')['idUser'];
            return redirect('/ClientInfo?id=' . $id . '&action=dashboard');
        } elseif ($request->query('action') && $request->query('action') !== 'login') {
            return view('login')
                ->withErrors([
                    'wrongLogin' => 'Veuillez vous identifier pour accéder à cet élément.'
                ]);
        }

        $response = response()->view('login');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }

    public function login(Request $request, CheckHours $checkHours, GenerateId $generateID, LogUser $logUser, CheckTimeOut $checkTimeOut)
    {
        $request->validate([
            'codeSal' => ['required', 'size:4', 'regex:/^[A-Za-z0-9*]{4}$/'],
            'password' => 'required|string'
        ], [
            'codeSal.required' => 'Le code salarié est obligatoire.',
            'codeSal.size' => 'Le code salarié doit contenir 4 caractères.',
            'codeSal.regex' => 'Le code salarié doit contenir uniquement des caractères alphanumériques ou une astérisque.',
            'password.required' => 'Le mot de passe est obligatoire.'
        ]);

        $codeSal = strip_tags(strtoupper($request->input('codeSal')));
        $password = strip_tags($request->input('password'));
        $id = $generateID->generateId();

        if ($checkTimeOut->isUserTimedOut($codeSal)) {
            $msg = $checkTimeOut->timeOutLeftTime($codeSal);

            return redirect('/')
                ->withErrors(['errors' => $msg])
                ->withInput();
        }

        $attempts = $checkTimeOut->checkAttempts($codeSal);
        $attemptsLeft = 5 - $attempts;

        if ($attempts >= 5) {
            $checkTimeOut->timeOutUser($codeSal);

            return redirect('/')
                ->withErrors(['errors' => "Nombre d'essais limite atteint, veuillez patienter 5 minutes."])
                ->withInput();
        }

        $user = DB::table('t_salarie')
            ->where('CodeSal', $codeSal)
            ->first();

        if (!$user) {
            return redirect('/')
                ->withErrors([
                    'wrongLogin' => 'Identifiants incorrects.',
                    'errors' => "Il vous reste {$attemptsLeft} essai" . ($attemptsLeft > 1 ? 's.' : '.')
                ])
                ->withInput();
        }

        if (!Hash::check($password, $user->password)) {
            return redirect('/')
                ->withErrors([
                    'wrongLogin' => 'Identifiants incorrects.',
                    'errors' => "Il vous reste {$attemptsLeft} essai" . ($attemptsLeft > 1 ? 's.' : '.')
                ])
                ->withInput();
        }

        if (!$checkHours->checkHours($codeSal)) {
            return redirect('/')
                ->withErrors(['wrongHours' => 'Plage horaire non autorisée.'])
                ->withInput();
        }

        $logUser->logUser($id, $request->ip(), $codeSal, $user->CodeAgSal);

        $user->idUser = $id;
        unset($user->password, $user->PassSal);
        Session::put('user', $user);

        $checkTimeOut->unsetAttempts($codeSal);

        return redirect('/ClientInfo?id=' . $id . '&action=dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/');
    }
}
