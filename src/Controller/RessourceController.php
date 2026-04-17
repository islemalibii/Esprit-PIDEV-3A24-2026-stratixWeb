<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Repository\ImportLogRepository; // AJOUT : Import du repository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PdfService; // Assure-toi que l'import est correct

class RessourceController extends AbstractController
{
    /**
     * Affiche la liste des ressources et l'historique des imports par email
     */
    #[Route('/ressource', name: 'ressource_index', methods: ['GET'])]
    public function index(
        RessourceRepository $repository, 
        ImportLogRepository $importLogRepo, // AJOUT : On injecte le repository des logs
        Request $request
    ): Response {
        // 1. Gestion de la Recherche
        $searchTerm = $request->query->get('q');
        if ($searchTerm) {
            $ressources = $repository->findBySearch($searchTerm);
        } else {
            $ressources = $repository->findAll();
        }

        // 2. Calcul des statistiques
        $quantiteTotale = 0;
        $typesUniques = [];
        foreach ($ressources as $r) {
            $quantiteTotale += $r->getQuantite();
            $typesUniques[] = $r->getTypeRessource();
        }
        $nombreTypes = count(array_unique($typesUniques));

        // 3. RÉCUPÉRATION DES IMPORTS RÉCENTS (Pour le deuxième onglet)
        // On récupère les 10 derniers fichiers récupérés par email
        $imports = $importLogRepo->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('admin/ressource/index.html.twig', [
            'ressources' => $ressources,
            'searchTerm' => $searchTerm,
            'quantiteTotale' => $quantiteTotale,
            'nombreTypes' => $nombreTypes,
            'imports' => $imports, // ON ENVOIE LES IMPORTS À LA VUE
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
     * Suppression d'une ressource
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