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

        // Tâches récentes (5 dernières)
        $tachesRecentes = array_slice(array_reverse($taches), 0, 5);

        // Prochain planning
        $prochainPlanning = null;
        $today = new \DateTime();
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
            ["text" => "La productivité n'est jamais un accident. C'est toujours le résultat d'un engagement envers l'excellence.", "author" => "Paul J. Meyer"],
            ["text" => "Commencez par faire ce qui est nécessaire, puis ce qui est possible.", "author" => "François d'Assise"],
            ["text" => "Le temps est ce que nous voulons le plus, mais ce que nous utilisons le plus mal.", "author" => "William Penn"],
            ["text" => "Une heure de planification peut vous faire gagner 10 heures de travail.", "author" => "Dale Carnegie"],
        ];
        $quote = $quotes[array_rand($quotes)];

        return $this->render('dashboard/index.html.twig', [
            'total'          => count($taches),
            'aFaire'         => $aFaire,
            'enCours'        => $enCours,
            'terminees'      => $terminees,
            'haute'          => $haute,
            'moyenne'        => $moyenne,
            'basse'          => $basse,
            'tachesRecentes' => $tachesRecentes,
            'totalPlannings' => count($plannings),
            'employes'       => $employes,
            'quote'          => $quote,
            'taches'         => $taches,
        ]);
    }
}