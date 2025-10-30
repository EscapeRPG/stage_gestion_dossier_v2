<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientInfoController extends Controller
{
    public function handle(Request $request)
    {
        $id = $request->query('id');
        $action = $request->query('action');
        $numInt = $request->query('numInt');

        if ($numInt && $action !== 'save-dossier') {
            $controller = app(ContratsController::class);
            return app()->call([$controller, 'detailDossier'], ['request' => $request]);
        }

        $publicActions = [
            'logging-in',
            'forgot-password',
            'reset-pass-code-sal',
            'answer-question',
            'send-mail',
            'reset-password',
            'login',
        ];

        if (!session()->has('user') && !in_array($action, $publicActions)) {
            $controller = app(LoginController::class);
            return app()->call([$controller, 'showLoginForm'], ['request' => $request]);
        }

        if (session()->has('user') && in_array($action, $publicActions)) {
            $controller = app(UserController::class);
            return app()->call([$controller, 'dashboard'], ['request' => $request]);
        }

        switch ($action) {
            case '':
            case 'dashboard':
                $controller = app(UserController::class);
                return app()->call([$controller, 'dashboard'], ['request' => $request]);

            case 'logging-in':
                $controller = app(LoginController::class);
                return app()->call([$controller, 'login']);

            case 'logout':
                $controller = app(LoginController::class);
                return app()->call([$controller, 'logout'], ['request' => $request]);

            case 'question-show':
                $controller = app(SecretQuestionController::class);
                return app()->call([$controller, 'showQuestionForm'], ['request' => $request]);

            case 'create-question':
                $controller = app(SecretQuestionController::class);
                return app()->call([$controller, 'createQuestion'], ['request' => $request]);

            case 'forgot-password':
                $controller = app(SecretQuestionController::class);
                return app()->call([$controller, 'showCodeSalForm'], ['request' => $request]);

            case 'reset-pass-code-sal':
                $controller = app(SecretQuestionController::class);
                if ($request->isMethod('post')) {
                    return app()->call([$controller, 'ShowAnswerQuestionForm'], ['request' => $request]);
                } else {
                    return app()->call([$controller, 'showCodeSalForm'], ['request' => $request]);
                }

            case 'answer-question':
                $controller = app(SecretQuestionController::class);
                if ($request->isMethod('post')) {
                    return app()->call([$controller, 'answerQuestion'], ['request' => $request]);
                } else {
                    return app()->call([$controller, 'ShowAnswerQuestionForm'], ['request' => $request]);
                }

            case 'send-mail':
                $controller = app(SecretQuestionController::class);
                return app()->call([$controller, 'sendMail'], ['request' => $request]);

            case 'reset-password':
                $controller = app(SecretQuestionController::class);
                if ($request->isMethod('post')) {
                    return app()->call([$controller, 'resetPassword'], ['request' => $request]);
                } else {
                    return app()->call([$controller, 'sendMail'], ['request' => $request]);
                }

            case 'menu':
                $controller = app(UserController::class);
                return app()->call([$controller, 'checkAutoMenu'], ['request' => $request]);

            case 'suivi-dossiers':
                $controller = app(ContratsController::class);
                return app()->call([$controller, 'suiviDossiers'], ['request' => $request]);

            case 'detail-dossier':
                $controller = app(ContratsController::class);
                return app()->call([$controller, 'detailDossier'], ['request' => $request]);

            case 'save-dossier':
                $controller = app(ContratsController::class);
                return app()->call([$controller, 'saveDossier'], ['request' => $request]);

            default:
                $controller = app(LoginController::class);
                return app()->call([$controller, 'showLoginForm'], ['request' => $request]);
        }
    }
}
