@extends('layouts.app')

@section('title', 'Mot de passe oublié')

@section('content')
    <main>
        <h1>Demande de réinitialisation de mot de passe</h1>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (isset($userValid))
            <div class="success-container">
                <p>Un email vient de vous être envoyé, cliquez sur le lien qu'il contient pour réinitialiser votre mot
                    de passe.</p>
            </div>

            <!-- Uniquement pour le test, à retirer dès que la gestion d'envoi de mails est faite -->
            <form action="/ClientInfo?id={{ $id }}&action=reset-password" method="post" class="login">
                @csrf

                <div>
                    <label for="password1">Votre nouveau mot de passe : </label>
                    <input type="password" name="password1" id="password1"
                           placeholder="Entrez votre nouveau mot de passe" required>
                </div>

                <div>
                    <label for="password2">Vérification du nouveau mot de passe : </label>
                    <input type="password" name="password2" id="password2"
                           placeholder="Vérifiez votre nouveau mot de passe" required>
                </div>

                <button>Valider</button>
            </form>
        @elseif (isset($secretQuestion))
            <form action="/ClientInfo?id={{ $id }}&action=answer-question" method="post" class="login">
                @csrf

                <p>Veuillez répondre à votre question secrète :</p>
                <p class="errors">{{ $secretQuestion }} ?</p>

                <div>
                    <label for="reponse">Votre réponse : </label>
                    <input type="text" name="reponse" id="reponse" value="{{ $old['reponse'] ?? '' }}"
                           placeholder="Entrez votre réponse secrète" required>
                </div>

                <div>
                    <label for="email">Votre email : </label>
                    <input type="email" name="email" id="email" value="{{ $old['email'] ?? '' }}"
                           placeholder="Entrez votre adresse mail" required>
                </div>

                <button>Valider</button>
            </form>
        @else
            <form action="/ClientInfo?id=&action=reset-pass-code-sal" method="post" class="login">
                @csrf

                <p>
                    Pour vérifier votre identité, veuillez indiquer votre code salarié.
                </p>

                <div>
                    <label for="codeSal">Votre code salarié : </label>
                    <input type="text" name="codeSal" id="codeSal" placeholder="Entrez votre code salarié" required>
                </div>

                <button>Valider</button>
            </form>
        @endif
    </main>
@endsection
