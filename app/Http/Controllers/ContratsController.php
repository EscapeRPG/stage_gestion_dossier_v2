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
        $perPage = $request->input('perPage', 10);
        $dossiers = $dossierDashboard->getDossiersDashboard();
        $timelineToday = $dossierDashboard->getTodayCalendar();
        $actions = $dossierDashboard->getToDoNext($dossiers);
        $page = $request->input('page', 1);
        $paged = $dossierDashboard->paginateCollection($actions, $perPage, $page, [
            'path' => url()->current(),
            'query' => $request->query(),
        ]);

        if ($request->ajax()) {
            return view('partials.dossiers-table', compact('paged', 'perPage'))->render();
        }

        return view('suivi-dossiers', [
            'dossiers' => $dossiers,
            'timelineToday' => $timelineToday,
            'actions' => $paged,
            'perPage' => $perPage
        ]);
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
