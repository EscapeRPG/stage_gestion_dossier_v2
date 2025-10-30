<?php

namespace App\Http\Middleware;

use App\Services\CheckAuthorization;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthorizationMiddleware
{
    protected CheckAuthorization $checkAuthorization;

    public function __construct(CheckAuthorization $checkAuthorization)
    {
        $this->checkAuthorization = $checkAuthorization;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->query('id') ?? '';
        $action = $request->query('action') ?? '';
        $numInt = $request->query('numInt') ?? '';

        $publicActions = [
            'logging-in',
            'forgot-password',
            'reset-pass-code-sal',
            'answer-question',
            'send-mail',
            'reset-password',
            'login',
        ];

        if (in_array($action, $publicActions)) {
            return $next($request);
        }

        try {
            $this->checkAuthorization->checkAuth($id);
        } catch (Exception $e) {
            Session::flush();

            $errorMap = [
                'disconnected' => 'Veuillez vous identifier pour accéder à cet élément.',
                'wrongID' => 'Votre identifiant ne correspond pas à votre session.',
                'wrongHours' => 'Plage horaire non autorisée.',
                'wrongAgency' => 'Agence non autorisée.',
            ];

            $message = $errorMap[$e->getMessage()] ?? 'Erreur inconnue.';

            return redirect()->route('login')->withErrors([$message]);
        }

        return $next($request);
    }
}
