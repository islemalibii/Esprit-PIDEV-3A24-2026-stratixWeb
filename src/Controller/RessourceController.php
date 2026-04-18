<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Repository\ImportLogRepository;
use App\Repository\OffreRepository; // AJOUTÉ
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PdfService;
use Symfony\Component\Process\Process; // AJOUTÉ
use Symfony\Component\Process\Exception\ProcessFailedException; // AJOUTÉ

class RessourceController extends AbstractController
{
    /**
     * Affiche la liste des ressources et l'historique des imports
     */
    #[Route('/ressource', name: 'ressource_index', methods: ['GET'])]
    public function index(
        RessourceRepository $repository, 
        ImportLogRepository $importLogRepo, 
        Request $request
    ): Response {
        $searchTerm = $request->query->get('q');
        if ($searchTerm) {
            $ressources = $repository->findBySearch($searchTerm);
        } else {
            $ressources = $repository->findAll();
        }

        $quantiteTotale = 0;
        $typesUniques = [];
        foreach ($ressources as $r) {
            $quantiteTotale += $r->getQuantite();
            $typesUniques[] = $r->getTypeRessource();
        }
        $nombreTypes = count(array_unique($typesUniques));

        $imports = $importLogRepo->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('admin/ressource/index.html.twig', [
            'ressources' => $ressources,
            'searchTerm' => $searchTerm,
            'quantiteTotale' => $quantiteTotale,
            'nombreTypes' => $nombreTypes,
            'imports' => $imports,
        ]);
    }

    /**
     * ANALYSE IA : Appelle le script Python pour comparer les offres
     */
    #[Route('/ressource/{id}/analyser', name: 'app_ressource_analyser')]
    public function analyser(Ressource $ressource, OffreRepository $offreRepo): Response
    {
        // 1. Récupérer les offres concurrentes pour cette ressource
        $offres = $offreRepo->findBy(['ressource' => $ressource]);

        if (count($offres) < 2) {
            $this->addFlash('warning', "Il faut au moins deux offres pour que l'IA puisse comparer.");
            return $this->redirectToRoute('ressource_index');
        }

        // 2. Préparer les données pour Python
        $dataForAi = [];
        foreach ($offres as $o) {
            $dataForAi[] = [
                'fournisseur' => $o->getFournisseur()->getNom(),
                'prix' => (float)$o->getPrix(),
                'delai' => (int)$o->getDelaiTransportJours()
            ];
        }

        // 3. Exécuter le script Python (situé dans /scripts/analyse_ia.py)
        $projectDir = $this->getParameter('kernel.project_dir');
        $process = new Process(['python', $projectDir . '/scripts/analyse_ia.py']);
        $process->setInput(json_encode($dataForAi));
        $process->run();

        // Gestion d'erreur si Python ne répond pas
        if (!$process->isSuccessful()) {
            // Option de secours : Si Python échoue, on fait un tri PHP simple
            $this->addFlash('error', "Le moteur IA n'a pas pu être lancé. Affichage d'un tri standard.");
            usort($dataForAi, fn($a, $b) => $a['prix'] <=> $b['prix']);
            $resultatsIA = $dataForAi;
        } else {
            $resultatsIA = json_decode($process->getOutput(), true);
        }

        return $this->render('admin/ressource/analyse.html.twig', [
            'ressource' => $ressource,
            'resultats' => $resultatsIA,
        ]);
    }

    /**
     * Formulaire d'Ajout et de Modification
     */
    #[Route('/ressource/form/{id?}', name: 'ressource_form')]
    public function form(Ressource $ressource = null, Request $request, EntityManagerInterface $em): Response
    {
        if (!$ressource) {
            $ressource = new Ressource();
        }

        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typePerso = $request->request->get('autre_type_field');
            if ($form->get('type_ressource')->getData() === 'Autre' && !empty($typePerso)) {
                $ressource->setTypeRessource(trim($typePerso));
            }

            $em->persist($ressource);
            $em->flush();

            $this->addFlash('success', 'La ressource a été enregistrée avec succès !');
            return $this->redirectToRoute('ressource_index');
        }

        return $this->render('admin/ressource/form.html.twig', [
            'form' => $form->createView(),
            'editMode' => $ressource->getId() !== null,
            'ressource' => $ressource
        ]);
    }

    /**
     * Suppression
     */
    #[Route('/ressource/delete/{id}', name: 'ressource_delete', methods: ['POST'])]
    public function delete(Ressource $ressource, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->request->get('_token'))) {
            $em->remove($ressource);
            $em->flush();
            $this->addFlash('success', 'Ressource supprimée.');
        }

        return $this->redirectToRoute('ressource_index');
    }

    /**
     * Export PDF
     */
    #[Route('/ressource/pdf', name: 'ressource_pdf')]
    public function generatePdfRessources(RessourceRepository $repository, PdfService $pdf): void
    {
        $ressources = $repository->findAll();
        $html = $this->renderView('admin/ressource/pdf.html.twig', [
            'ressources' => $ressources
        ]);
        $pdf->showPdfFile($html, 'Liste_Ressources_Stratix');
    }
}