<article>
    <h2>Planifier un rendez-vous</h2>

    <div class="content planning">
        @if ($intervention->rdv)
            @php
                [$year, $month, $day] = explode('-', $intervention->rdv->Date_RDV);
                $date = implode('/', [$day, $month, $year]);
            @endphp

            <div
                id="existing-rdv"
                data-date="{{ $date }}"
                data-heure="{{ substr($intervention->rdv->Heure_RDV, 0, 5) }}"
                data-tech="{{ $intervention->rdv->Tech_RDV ?? '/ Technicien non affecté /' }}"
                data-valid="{{ $intervention->rdv->Valide === 'O' ? '' : 'non ' }}validé"
            >
                <p>
                    Rendez-vous <strong>{{ $intervention->rdv->Valide === 'O' ? '' : 'non ' }}validé</strong>
                    le <strong>{{ $date }}</strong>
                    à <strong>{{ substr($intervention->rdv->Heure_RDV, 0, 5) }}</strong>
                    pour <strong>{{ $intervention->rdv->Tech_RDV ?? '/ Technicien non affecté /' }}</strong>.
                </p>
            </div>
        @endif

        <div class="detail">
            <input type="date" name="dateRDV" id="dateRDV">

            <select name="timeRDV" id="timeRDV">
                <option value="">-- : --</option>
                @for($i = 9; $i < 18; $i++)
                    @for($j = 0; $j < 2; $j++)
                        <option value="{{ $i == 9 ? '0'.$i : $i }}:{{ $j == 0 ? '00' : '30' }}">
                            {{ $i == 9 ? '0'.$i : $i }} :{{ $j == 0 ? '00' : '30' }}
                        </option>
                    @endfor
                @endfor
            </select>

            <select name="tech-rdv" id="tech-rdv">
                <option value="">-- Tech --</option>
                <optgroup label="Agence courante">
                    <option value="{{ $intervention->codeAgence }}">{{ $intervention->codeAgence }}</option>
                </optgroup>
                @foreach ($intervention->salaries as $group => $names)
                    <optgroup label="{{ $group }}">
                        @foreach ($names as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            <div class="checkbox">
                <input type="checkbox" name="validRDV" id="validRDV" value="O">
                <label for="validRDV">Validé ?</label>
            </div>
        </div>
    </div>
</article>

<article>
    <h2>Planning</h2>

    <div class="content" id="day-info">
        <p>Sélectionnez un jour pour voir les informations.</p>
    </div>
</article>
