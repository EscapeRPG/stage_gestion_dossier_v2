@extends('layouts.app')

@section('title', 'Question secrète')

@section('content')
    <main>
        <h1>Créez votre question secrète</h1>

        <form action="/ClientInfo?id={{ session('user')->idUser }}&action=create-question" method="post" class="login">
            @csrf

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p>
                Veuillez sélectionner et répondre à l'une de ces questions secrètes.
                Cela vous servira pour récupérer votre mot de passe en cas d'oubli.
            </p>
            <div>

                <label for="question">Votre question secrète : </label>
                <select name="question" id="question" required>
                    <option value="">-- Veuillez sélectionner une question --</option>
                    <option value="1">Nom de jeune fille de votre mère</option>
                    <option value="2">Votre ville de naissance</option>
                    <option value="3">Nom de votre premier animal domestique</option>
                </select>
            </div>

            <div>
                <label for="reponse">Votre réponse : </label>
                <input type="text" name="reponse" id="reponse" placeholder="Entrez la réponse à votre question secrète"
                       required>
            </div>

            <button>Valider</button>
        </form>
    </main>
@endsection
