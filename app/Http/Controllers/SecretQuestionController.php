<?php

namespace App\Http\Controllers;

use App\Services\CheckAuthorization;
use App\Services\CheckTimeOut;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SecretQuestionController extends Controller
{
    /**
     * @throws \Exception
     */
    public function showQuestionForm(CheckAuthorization $checkAuthorization)
    {
        if (!session()->has('user')) {
            $response = response()->view('login');

            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

            return $response;
        }

        $id = session('user')->idUser;
        $codeSal = session('user')->CodeSal;

        try {
            $checkAuthorization->checkAuth($id);
        } catch (Exception $e) {
            $errorMap = [
                'disconnected' => 'Veuillez vous identifier pour accéder à cet élément.',
                'wrongID' => 'Votre identifiant ne correspond pas à votre id de session.',
                'wrongHours' => 'Plage horaire non autorisée.',
                'wrongAgency' => 'Agence non autorisée.',
            ];

            return view('login')->withErrors([$errorMap[$e->getMessage()]]);
        }

        return view('secret-question');
    }

    public function createQuestion(Request $request)
    {
        $request->validate([
            'question' => 'required|in:1,2,3',
            'reponse' => 'required|string'
        ], [
            'question.required' => 'Veuillez sélectionner une question secrète (obligatoire).',
            'question.in' => 'La question secrète sélectionnée est invalide.',
            'reponse.required' => 'Veuillez indiquer une réponse à la question secrète (obligatoire).'
        ]);

        $question = strip_tags($request->input('question'));
        $reponse = Hash::make(strip_tags($request->input('reponse')));
        $codeSal = session('user')->CodeSal;

        DB::table('t_questions_secretes')
            ->insert([
                'Code_Sal' => $codeSal,
                'ID_Question' => $question,
                'Reponse' => $reponse
            ]);

        $user = session('user');

        return view('dashboard', compact('user'))
            ->with([
                'successQuestion' => "Votre question secrète a été enregistrée avec succès."
            ]);
    }

    public function showCodeSalForm()
    {
        return view('answer-question');
    }

    public function ShowAnswerQuestionForm(Request $request, CheckTimeOut $checkTimeOut)
    {
        $request->validate([
            'codeSal' => ['required', 'size:4', 'regex:/^[A-Za-z0-9*]{4}$/'],
        ], [
            'codeSal.required' => 'Le code salarié est obligatoire.',
            'codeSal.size' => 'Le code salarié doit contenir 4 caractères.',
            'codeSal.regex' => 'Le code salarié doit contenir uniquement des caractères alphanumériques ou une astérisque.',
        ]);

        $codeSal = strip_tags($request->input('codeSal'));

        Session::put('CodeSal', $codeSal);

        if ($checkTimeOut->isUserTimedOut($codeSal)) {
            $msg = $checkTimeOut->timeOutLeftTime($codeSal);

            return view('login')
                ->withErrors(['errors' => $msg]);
        }

        $user = DB::table('t_questions_secretes')
            ->select('ID_Question')
            ->where('Code_Sal', '=', $codeSal)
            ->first();

        if (!$user) {
            return redirect('ClientInfo?id='. $codeSal . '&action=forgot-password')
                ->withErrors(['wrongLogin' => 'Aucune question secrète enregistrée pour cet utilisateur. Veuillez contacter un administrateur.'])
                ->withInput();
        }

        $questions = [
            '1' => "Nom de jeune fille de votre mère",
            '2' => "Votre ville de naissance",
            '3' => "Nom de votre premier animal domestique"
        ];

        $secretQuestion = $questions[$user->ID_Question];

        return view('answer-question')
            ->with(['secretQuestion' => $secretQuestion, 'id' => $codeSal]);
    }

    public function answerQuestion(Request $request, CheckTimeOut $checkTimeOut)
    {

        $validator = Validator::make($request->all(), [
            'reponse' => 'required',
            'email' => 'required|email',
        ], [
            'reponse.required' => 'Veuillez indiquer votre réponse secrète.',
            'email.required' => 'Veuillez indiquer une adresse email pour envoyer le lien de réinitialisation du mot de passe.',
            'email.email' => 'Veuillez indiquer une adresse email valide.',
        ]);

        if (!session()->has('CodeSal')) {
            return redirect('ClientInfo?id=&action=forgot-password')
                ->withErrors(['wrongLogin' => 'Veuillez indiquer votre code salarié.'])
                ->withInput();
        } else {
            $codeSal = session('CodeSal');
        }

        $attempts = $checkTimeOut->checkAttempts($codeSal);
        $attemptsLeft = 5 - $attempts;

        if ($attempts >= 5) {
            $checkTimeOut->timeOutUser($codeSal);

            return view('login')
                ->withErrors(['errors' => "Nombre d'essais limite atteint, veuillez patienter 5 minutes pour pouvoir réessayer."]);
        }

        $user = DB::table('t_questions_secretes')
            ->where('Code_Sal', '=', $codeSal)
            ->first();

        $questions = [
            '1' => "Nom de jeune fille de votre mère",
            '2' => "Votre ville de naissance",
            '3' => "Nom de votre premier animal domestique"
        ];

        $secretQuestion = $questions[$user->ID_Question];

        if ($validator->fails()) {
            return view('answer-question')
                ->with(['secretQuestion' => $secretQuestion, 'id' => $codeSal])
                ->withErrors($validator);
        }

        $reponse = strip_tags($request->input('reponse'));

        $userValid = Hash::check($reponse, $user->Reponse);

        if ($userValid) {
            return redirect('/ClientInfo?id='. $codeSal . '&action=send-mail');
        }

        return view('answer-question')
            ->with(['secretQuestion' => $secretQuestion, 'old' => $request->all(), 'id' => $codeSal])
            ->withErrors(['errors' => 'Réponse invalide, il vous reste ' . $attemptsLeft . ' essai' . ($attemptsLeft > 1 ? "s." : ".")]);
    }

    public function sendMail(Request $request, CheckTimeOut $checkTimeOut)
    {
        $codeSal = session('CodeSal');
        $checkTimeOut->unsetAttempts($codeSal);

        // @Todo Envoyer un email à l'utilisateur avec un lien sécurisé pour réinitialiser le mot de passe

        return view('answer-question')
            ->with('userValid', 'pouet');
    }

    public function resetPassword(Request $request)
    {
        // Récupérer le code salarié via l'url, pour éviter que la session ne soit terminée si l'utilisateur a mis du temps à ouvrir ses mails ou quoi
        // $codeSal = $request->query('id');

        $codeSal = session('CodeSal');
        $pass1 = $request->input('password1');
        $pass2 = $request->input('password2');

        if ($pass1 !== $pass2) {
            return view('answer-question')
                ->with('userValid', 'pouet')
                ->withErrors(['errors' => 'Les mots de passe ne correspondent pas']);
        }

        $hashedPassword = Hash::make($pass1);

        DB::table('t_salarie')
            ->where('CodeSal', '=', $codeSal)
            ->update(['password' => $hashedPassword, 'PassSal' => $pass1]);

        return view('login')
            ->with('success', 'Nouveau mot de passe enregistré, vous pouvez vous connecter.');
    }
}
