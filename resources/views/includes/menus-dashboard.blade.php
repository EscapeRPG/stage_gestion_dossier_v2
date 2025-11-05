<div class="dashboard-container">
    @if ($errors->any())
        @php
            $otherErrors = [];
            foreach ($errors->messages() as $key => $messages) {
                if ($key !== 'noSecretQuestion') {
                    foreach ($messages as $msg) {
                        $otherErrors[] = $msg;
                    }
                }
            }
        @endphp

        @if(!empty($otherErrors))
            <div class="errors">
                @foreach ($otherErrors as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif
    @endif

    @if (isset($success))
        <div class="success">
            <p>{{ $success }}</p>
        </div>
    @endif

    <form action="/ClientInfo?id={{ session('user')->idUser }}&action=menu" method="post">
        @csrf

        @for ($i = 1; $i <= 12; $i++)
            <h2>
                {{ $i == 9 ? 'Contrats' : 'Automenu' . $i }}
            </h2>
            <div class="applications">
                @for ($j = 0; $j < strlen(session('user')->{'automenu' . $i}) - 1; $j++)
                    @if ($i === 9 && $j === 16)
                        <button name="menu" value="<?= 'automenu' . $i . "." . $j ?>"
                                class="{{ substr(session('user')->{'automenu' . $i}, $j, 1) == 1 ? 'blue' : 'red' }}">
                            Gestion des contrats
                        </button>
                    @else
                        <button name="menu" value="<?= 'automenu' . $i . "." . $j ?>"
                                class="{{ substr(session('user')->{'automenu' . $i}, $j, 1) == 1 ? 'green' : 'red' }}">
                            Appli {{ $i }}.{{ $j }}
                        </button>
                    @endif
                @endfor
            </div>
        @endfor
    </form>
</div>
