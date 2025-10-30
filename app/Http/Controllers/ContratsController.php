<?php

namespace App\Http\Controllers;

use App\Services\DossiersDashboard;
use App\Services\GetDayInfo;
use App\Services\GetHistoByNumInt;
use App\Services\GetInterventionDetail;
use App\Services\SaveDossier;
use App\Services\UpdateHeureIntervention;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContratsController extends Controller
{
    public function suiviDossiers(Request $request, DossiersDashboard $dossierDashboard)
    {
        $codeSal = session('user')->CodeSal;
        $result = $dossierDashboard->getDossiersDashboard($codeSal);
        $dossiers = $result['dossiers'];
        $timelineToday = $result['timelineToday'];
        $timeline = $result['timeline'];

        return view('suivi-dossiers', compact('dossiers', 'timeline', 'timelineToday'));
    }

    public function detailDossier(Request $request, GetInterventionDetail $getInterventionDetail)
    {
        if ($request->query('numInt')) {
            $numInt = htmlspecialchars($request->query('numInt'));
        } else {
            $numInt = htmlspecialchars(trim($request->input('dossier-list')) ?? '');
        }

        $intervention = $getInterventionDetail->getInterventionDetail($numInt);

        if (!$intervention->success) {
            return redirect('/ClientInfo?id=' . session('user')->idUser . '&action=suivi-dossiers')
                ->withErrors(['erreur' => $intervention->message]);
        }

        return view('dossier-detail', [
            'intervention' => $intervention->data
        ]);
    }

    public function getDayInfo($date, GetDayInfo $getDayInfo): JsonResponse
    {
        $result = $getDayInfo->getDayInfo($date);

        return response()->json($result);
    }

    public function updateHeure(Request $request, UpdateHeureIntervention $updateHeureIntervention): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:rdv,tache',
            'numInt' => 'required|string',
            'heure' => 'required',
            'prevHeure' => 'required',
            'date' => 'required',
            'technicien' => 'nullable|string'
        ]);

        return $updateHeureIntervention->updateHeureIntervention($validated);
    }

    public function getHistoByNumInt($numInt, GetHistoByNumInt $getHistoByNumInt): JsonResponse
    {
        return $getHistoByNumInt->getHistoByNumInt($numInt);
    }

    public function saveDossier(Request $request, SaveDossier $saveDossier)
    {
        $saveDossier->saveDossier($request);
        $numInt = $request->input('numInt');

        return redirect('/ClientInfo?id=' . session('user')->idUser . '&action=dossier-detail&numInt=' . $numInt);
    }
}
