<?php

namespace App\Http\Controllers;

use App\Services\checkAuthorization;
use App\Services\CheckSecretQuestion;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @throws Exception
     */
    public function dashboard(Request $request,CheckAuthorization $checkAuthorization, CheckSecretQuestion $checkSecretQuestion)
    {
        $id = $request->query('id');
        $codeSal = session('user')->CodeSal ?? '';
        $user = session('user') ?? null;

        if (!$checkSecretQuestion->checkSecretQuestion($codeSal)) {
            return view('dashboard', compact('user'))
                ->withErrors([
                    'noSecretQuestion' => "Vous n'avez toujours pas déterminé de question secrète.<br><br>Veuillez suivre <a href='" . route('client.info', ['id' => $id, 'action' => 'question-show']) . "'>ce lien</a> pour en créer une."
                ]);
        }

        return view('dashboard', compact('user'));
    }

    public function checkAutoMenu(Request $request, CheckSecretQuestion $checkSecretQuestion)
    {
        $menu = $request->input('menu');
        [$automenu, $index] = explode(".", $menu);
        $user = session('user') ?? null;
        $menuStr = session('user')->$automenu;
        $codeSal = session('user')->CodeSal ?? '';

        if (!$checkSecretQuestion->checkSecretQuestion($codeSal)) {
            $errors['noSecretQuestion'] = "Vous n'avez toujours pas déterminé de question secrète.<br><br>Veuillez suivre <a href='" . route('client.info', ['id' => $user->idUser, 'action' => 'question-show']) . "'>ce lien</a> pour en créer une.";
        }

        if (substr($menuStr, $index, 1)) {
            if ($automenu == 'automenu9' && $index == 16) {
                return redirect()->route('client.info', ['id' => $user->idUser, 'action' => 'suivi-dossiers']);
            }

            $successMessage = "L'accès à cette application est autorisé.";
        } else {
            $errors['auth'] = "L'accès à cette application n'est pas autorisé.";
        }

        $view = view('dashboard', compact('user'));

        if (!empty($errors)) {
            $view = $view->withErrors($errors);
        }

        if (!empty($successMessage)) {
            $view = $view->with('success', $successMessage);
        }

        return $view;
    }
}
