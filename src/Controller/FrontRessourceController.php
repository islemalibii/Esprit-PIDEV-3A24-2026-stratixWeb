<?php

namespace App\Controller;

use App\Repository\RessourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontRessourceController extends AbstractController
{
    #[Route('/catalogue-ressources', name: 'front_ressource_index')]
    public function index(RessourceRepository $repository, Request $request): Response
    {
        $searchTerm = $request->query->get('q', '');

        if ($searchTerm) {
            // Assure-toi d'avoir findBySearch dans ton RessourceRepository
            $ressources = $repository->findBySearch($searchTerm);
        } else {
            $ressources = $repository->findAll();
        }

        // Calcul des stats (Remplacement de mettreAJourStatistiques)
        $total = count($ressources);
        $quantiteTotale = 0;
        foreach ($ressources as $r) {
            $quantiteTotale += $r->getQuantite();
        }

        return $this->render('employee/ressource/index.html.twig', [
            'ressources' => $ressources,
            'searchTerm' => $searchTerm,
            'stats' => [
                'total' => $total,
                'quantiteTotale' => $quantiteTotale
            ]
        ]);
    }
}