<article>
    <h2 id="traitement-btn" class="h2-hide">
        #{{ $intervention->NumInt }}
    </h2>

    <div class="content traitement show" id="traitement">
        <article>
            <h2 id="etat-btn" class="h2-hide">
                État
            </h2>

            <div class="traitement-content show" id="etat">
                <fieldset class="client">
                    <div class="detail-etat">
                        <label for="nomCli">Client</label>
                        <input type="text" name="nomCli" id="nomCli" value="{{ $intervention->Nom_Cli }}">
                    </div>

                    <div class="detail-etat">
                        <label for="marque">Marque</label>
                        <input type="text" name="marque" id="marque" placeholder="{{ $intervention->Marque }}">
                    </div>

                    <div class="detail-etat">
                        <label for="appareil">Type</label>
                        <input type="text" name="appareil" id="appareil" placeholder="{{ $intervention->Type_App }}">
                    </div>
                </fieldset>

                <fieldset>
                    @foreach($intervention->questions as $question)
                        @if($question->panneau === 'G')
                            <div class="checkBtn">
                                <input type="checkbox"
                                       name="etat[]"
                                       value="{{ $question->question }}"
                                       id="checkG{{ $question->ordre }}"
                                >
                                <label for="checkG{{ $question->ordre }}">
                                    {{ $question->question }}
                                    @if ($question->span)
                                        <span class="important">{{ $question->span }}</span>
                                    @endif
                                </label>
                            </div>
                        @endif
                    @endforeach
                </fieldset>
            </div>
        </article>

        <article>
            <h2 id="a-faire-btn" class="h2-hide">
                À faire
            </h2>

            <div class="traitement-content show" id="a-faire">
                <div class="detail-etat">
                    <input type="date" name="date-a-faire" id="date-a-faire">

                    <select name="heure-a-faire" id="heure-a-faire">
                        <option value="">-- : --</option>
                        @for($i = 9; $i < 18; $i++)
                            @for($j = 0; $j < 2; $j++)
                                <option value="{{ $i == 9 ? '0'.$i : $i }}:{{ $j == 0 ? '00' : '30' }}">
                                    {{ $i == 9 ? '0'.$i : $i }} :{{ $j == 0 ? '00' : '30' }}
                                </option>
                            @endfor
                        @endfor
                    </select>

                    <select name="reaffectation-dossier" id="reaffectation">
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

                    <div class="detail prioritaire">
                        <input type="checkbox" name="urgent" id="urgent" value="O">
                        <label for="urgent">Prioritaire ?</label>
                    </div>
                </div>

                <fieldset>
                    @foreach($intervention->questions as $question)
                        @if($question->panneau === 'D')
                            <div class="checkBtn">
                                <input type="checkbox"
                                       name="a-faire[]"
                                       value="{{ $question->question }}"
                                       id="checkD{{ $question->ordre }}"
                                >
                                <label for="checkD{{ $question->ordre }}">
                                    {{ $question->question }}
                                    @if ($question->span)
                                        <span class="important">{{ $question->span }}</span>
                                    @endif
                                </label>
                            </div>
                        @endif
                    @endforeach
                </fieldset>
            </div>
        </article>
    </div>
</article>
