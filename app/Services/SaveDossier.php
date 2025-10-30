<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaveDossier
{
    public function saveDossier($request): void
    {
        $validated = $request->validate([
            'numInt' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]{4}-\d{4}-\d{5}$/i'],
            'nomCli' => ['required', 'string', 'max:50'],
            'commentaire-histo' => ['nullable', 'string', 'max:60'],
            'commentaire-detail-histo' => ['nullable', 'string', 'max:250'],
            'reaffectation-dossier' => ['nullable', 'string', 'max:5'],
            'date-a-faire' => ['nullable', 'date'],
            'heure-a-faire' => ['nullable', 'date_format:H:i'],
            'urgent' => ['nullable', Rule::in(['O', 'N'])],
            'dateRDV' => ['nullable', 'date'],
            'timeRDV' => ['nullable', 'date_format:H:i'],
            'validRDV' => ['nullable', Rule::in(['O', 'N'])],
            'tech-rdv' => ['nullable', 'string', 'max:5'],
            'etat' => ['nullable', 'array'],
            'a-faire' => ['nullable', 'array'],
        ]);

        $numInt = $validated['numInt'];
        $codeSal = session('user')->CodeSal;
        $agence = substr($numInt, 0, 4);
        $date = now()->toDateString();
        $heure = now()->format('H:i:s');

        DB::transaction(function () use ($validated, $codeSal, $agence, $date, $heure, $numInt) {
            DB::table('t_histoappels')->insert([
                'Num_Int' => $numInt,
                'Code_Sal' => $codeSal,
                'Agence' => $agence,
                'Nom_Cli' => $validated['nomCli'],
                'Comm' => strip_tags($validated['commentaire-histo'] ?? ''),
                'Comm_Detail' => strip_tags($validated['commentaire-detail-histo'] ?? ''),
                'Date_MAJ' => $date,
                'Heure_MAJ' => $heure,
                'Tech_Affecte' => $validated['reaffectation-dossier'] ?? null,
                'AFaire_Date' => $validated['date-a-faire'] ?? null,
                'AFAire_Heure' => $validated['heure-a-faire'] ?? null,
                'prio' => $validated['urgent'] ?? 'N',
                'Date_RDV' => ($validated['dateRDV'] && $validated['timeRDV']) ? $validated['dateRDV'] : null,
                'Heure_RDV' => ($validated['dateRDV'] && $validated['timeRDV']) ? $validated['timeRDV'] : null,
                'Valid_RDV' => $validated['validRDV'] ?? 'N',
                'Tech_RDV' => $validated['tech-rdv'] ?? null,
            ]);

            foreach (['etat', 'a-faire'] as $champ) {
                if (!empty($validated[$champ])) {
                    foreach ($validated[$champ] as $valeur) {
                        DB::table('t_reponsesappels')->insert([
                            'NumInt' => $numInt,
                            'Date' => $date,
                            'Heure' => $heure,
                            'Question' => $valeur,
                            'Type' => $champ === 'etat' ? 'État' : 'À Faire',
                            'AFaire_Date' => $champ === 'a-faire' ? $validated['date-a-faire'] ?? null : null,
                            'AFaire_Heure' => $champ === 'a-faire' ? $validated['heure-a-faire'] ?? null : null,
                            'AFaire_Tech' => $champ === 'a-faire' ? $validated['reaffectation-dossier'] ?? null : null,
                        ]);
                    }
                }
            }

            if (!empty($validated['dateRDV']) && !empty($validated['timeRDV']) && !empty($validated['tech-rdv'])) {
                $client = DB::table('t_interventions')
                    ->select('Nom_Cli', 'Adresse_Cli', 'CP_Cli', 'Ville_Cli', 'Num_Tel_Cli', 'Mail_Cli')
                    ->where('NumInt', $numInt)
                    ->first();

                if ($client) {
                    DB::table('t_planning')->insert([
                        'Code_Sal' => $codeSal,
                        'Num_Int' => $numInt,
                        'Heure_Enrg' => $heure,
                        'Date_RDV' => $validated['dateRDV'],
                        'Heure_RDV' => $validated['timeRDV'],
                        'Tech_RDV' => $validated['tech-rdv'],
                        'Valide' => $validated['validRDV'] ?? 'N',
                        'Agence' => $agence,
                        'Nom_Cli' => $validated['nomCli'],
                        'Adresse_Cli' => is_resource($client->Adresse_Cli)
                            ? stream_get_contents($client->Adresse_Cli)
                            : $client->Adresse_Cli,
                        'CP_Cli' => $client->CP_Cli,
                        'Ville_Cli' => $client->Ville_Cli,
                        'Num_Tel_Cli' => $client->Num_Tel_Cli,
                        'Mail_Cli' => $client->Mail_Cli,
                    ]);
                }
            }
        });
    }
}
