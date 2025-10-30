@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <main>
        <h1>Connexion</h1>

        @if (isset($success))
            <div class="success-container">
                <p>{{ $success }}</p>
            </div>
        @endif

        <form action="/ClientInfo?id=&action=logging-in" method="post" class="login">
            @csrf

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <label for="codeSal">Code salarié : </label>
                <input type="text" name="codeSal" id="codeSal" value="{{ old('codeSal') }}"
                       placeholder="Entrez votre code salarié" required>
            </div>

            <div>
                <label for="password">Mot de passe : </label>
                <input type="password" name="password" id="password" placeholder="Entrez votre mot de passe" required>
            </div>

            <button>Valider</button>

            <a href="{{ route('client.info', ['id' => '', 'action' => 'forgot-password']) }}">Mot de passe oublié ?</a>
        </form>
    </main>
@endsection
