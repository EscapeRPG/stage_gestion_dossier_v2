<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GeocodeInterventions extends Command
{
    /**
     * Le nom et la signature de la commande artisan.
     *
     * Exemple d'utilisation : php artisan geocode:interventions
     */
    protected $signature = 'geocode:interventions';

    /**
     * Description de la commande.
     */
    protected $description = 'Ajoute les coordonnées (latitude/longitude) aux interventions sans géolocalisation.';

    /**
     * Exécution de la commande.
     */
    public function handle()
    {
        $this->info('🔍 Recherche des interventions sans coordonnées...');

        // Récupère toutes les interventions sans lat/lon
        $interventions = DB::table('t_interventions')
            ->whereNull('Lat_Cli')
            ->orWhere('Lat_Cli', '')
            ->get();

        if ($interventions->isEmpty()) {
            $this->info('✅ Toutes les interventions ont déjà des coordonnées.');
            return 0;
        }

        $this->info("⏳ {$interventions->count()} interventions à géocoder...\n");

        foreach ($interventions as $int) {
            // Construction de l'adresse complète
            $adresse = trim("{$int->Adresse_Cli}, {$int->CP_Cli} {$int->Ville_Cli}");
            if (!$adresse) continue;

            $this->line("📍 Géocodage : {$int->NumInt} → {$adresse}");

            try {
                // Requête à Nominatim (OpenStreetMap)
                $response = Http::withHeaders([
                    'User-Agent' => 'Laravel-Geocode-Script'
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $adresse,
                    'format' => 'json',
                    'limit' => 1
                ]);

                if ($response->successful() && count($response->json()) > 0) {
                    $data = $response->json()[0];
                    $lat = $data['lat'];
                    $lon = $data['lon'];

                    DB::table('t_interventions')
                        ->where('NumInt', $int->NumInt)
                        ->update([
                            'Lat_Cli' => $lat,
                            'Lon_Cli' => $lon
                        ]);

                    $this->info("✅ Coordonnées : $lat, $lon");
                } else {
                    $this->warn("⚠️  Aucune coordonnée trouvée pour : $adresse");
                }
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour {$int->NumInt} : {$e->getMessage()}");
            }

            // Pause d'une seconde entre les requêtes (important pour Nominatim)
            sleep(1);
        }

        $this->info("\n🎉 Géocodage terminé !");
        return 0;
    }
}
