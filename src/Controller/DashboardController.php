<?php

namespace App\Controller;

use App\Repository\TacheRepository;
use App\Repository\PlanningRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        TacheRepository $tacheRepository,
        PlanningRepository $planningRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $taches = $tacheRepository->findAll();
        $plannings = $planningRepository->findAll();

        $aFaire = 0; $enCours = 0; $terminees = 0;
        $haute = 0; $moyenne = 0; $basse = 0;

        foreach ($taches as $t) {
            if ($t->getStatut() === 'A_FAIRE')   $aFaire++;
            if ($t->getStatut() === 'EN_COURS')  $enCours++;
            if ($t->getStatut() === 'TERMINEE')  $terminees++;
            if ($t->getPriorite() === 'HAUTE')   $haute++;
            if ($t->getPriorite() === 'MOYENNE') $moyenne++;
            if ($t->getPriorite() === 'BASSE')   $basse++;
        }

        $tachesRecentes = array_slice(array_reverse($taches), 0, 5);

        $today = new \DateTime();
        $tachesImminentes = [];
        foreach ($taches as $t) {
            if ($t->getDeadline() && $t->getDeadline() >= $today && $t->getStatut() !== 'TERMINEE') {
                $diff = $today->diff($t->getDeadline())->days;
                if ($diff <= 3) {
                    $tachesImminentes[] = $t;
                }
            }
        }
        $tachesImminentes = array_slice($tachesImminentes, 0, 5);

        $planningsAVenir = [];
        foreach ($plannings as $p) {
            if ($p->getDate() >= $today) {
                $planningsAVenir[] = $p;
            }
        }
        $planningsAVenir = array_slice($planningsAVenir, 0, 5);

        $prochainPlanning = null;
        foreach ($plannings as $p) {
            if ($p->getDate() >= $today) {
                $prochainPlanning = $p;
                break;
            }
        }

        $employes = [];
        foreach ($utilisateurRepository->findAll() as $u) {
            $employes[$u->getId()] = $u->getPrenom() . ' ' . $u->getNom();
        }

        $quotes = [
            ["text" => "Le succès c'est tomber sept fois et se relever huit.", "author" => "Proverbe japonais"],
            ["text" => "La productivité n'est jamais un accident.", "author" => "Paul J. Meyer"],
            ["text" => "Une heure de planification peut vous faire gagner 10 heures de travail.", "author" => "Dale Carnegie"],
        ];
        $quote = $quotes[array_rand($quotes)];

        return $this->render('dashboard/index.html.twig', [
            'total'            => count($taches),
            'aFaire'           => $aFaire,
            'enCours'          => $enCours,
            'terminees'        => $terminees,
            'haute'            => $haute,
            'moyenne'          => $moyenne,
            'basse'            => $basse,
            'tachesRecentes'   => $tachesRecentes,
            'tachesImminentes' => $tachesImminentes,
            'planningsAVenir'  => $planningsAVenir,
            'totalPlannings'   => count($plannings),
            'prochainPlanning' => $prochainPlanning,
            'employes'         => $employes,
            'quote'            => $quote,
            'taches'           => $taches,
            'plannings'        => $plannings,
        ]);
    }
}