<div class="info-bar">
    <h1>Dossier #{{ $intervention->NumInt }}</h1>

    <form action="/ClientInfo?id={{ session('user')->idUser }}&action=suivi-dossiers"
          method="post"
          class="back-form">
        @csrf

        <button>Retour</button>
    </form>
</div>
