<h3>À venir</h3>

<div class="pagination-controls">
    <form action="/ClientInfo?id={{ session('user')->idUser }}&action=detail-dossier&numInt=" method="post"
          class="dossier-search" id="numIntForm">
        @csrf

        <input list="liste-dossiers" name="dossier-list" id="dossier-list"
               placeholder="Choisissez ou entrez un numéro de dossier">
        <datalist id="liste-dossiers">
            @foreach($dossiers as $dossier)
                <option value="{{ $dossier->NumInt }}"></option>
            @endforeach
        </datalist>

        <button>Rechercher</button>

        <input type="hidden" id="id" value="{{ session('user')->idUser }}">
    </form>

    <p>Dossiers par page</p>

    <button class="page-btn" data-size="5">5</button>
    <button class="page-btn" data-size="10">10</button>
    <button class="page-btn" data-size="20">20</button>
    <button class="page-btn" data-size="50">50</button>
</div>

<div id="dossiers-container"
     data-url="{{ route('ajax.suivi-dossiers', ['id' => session('user')->idUser, 'action' => 'suivi-dossiers']) }}">
    @include('partials.dossiers-table', ['paged' => $actions, 'perPage' => $perPage])
</div>
