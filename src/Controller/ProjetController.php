<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projet')]
class ProjetController extends AbstractController
{
    /**
     * Liste tous les projets actifs.
     */
    #[Route('/projet', name: 'app_projet_index', methods: ['GET'])]
    public function index(ProjetRepository $projetRepository): Response
    {
        return $this->render('admin/Projet/listeProjets.html.twig', [
            // Utilisation de la méthode personnalisée du repository
            'projets' => $projetRepository->findAllActive(),
        ]);
    }

    /**
     * Liste des archives.
     */
    #[Route('/archives', name: 'app_projet_archives', methods: ['GET'])]
    public function archives(ProjetRepository $projetRepository): Response
    {
        return $this->render('admin/Projet/listeArchives.html.twig', [
            // Utilisation de la méthode personnalisée du repository
            'projets' => $projetRepository->findAllArchived(),
        ]);
    }

    /**
     * Création d'un projet.
     */
    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        // On s'assure qu'un nouveau projet n'est pas archivé par défaut
        $projet->setIsArchived(false);
        
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();

            $this->addFlash('success', 'Le projet "' . $projet->getNom() . '" a été créé avec succès.');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('admin/Projet/ajouterProjet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un projet.
     */
    #[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le projet a été mis à jour.');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('admin/Projet/modifierProjet.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Action d'archivage.
     */
    #[Route('/{id}/archiver', name: 'app_projet_archive_action', methods: ['GET'])]
    public function archiver(Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $projet->setIsArchived(true);
        $entityManager->flush();

        $this->addFlash('warning', 'Le projet a été déplacé dans les archives.');
        return $this->redirectToRoute('app_projet_index');
    }

    /**
     * Action de désarchivage.
     */
    #[Route('/{id}/desarchiver', name: 'app_projet_unarchive_action', methods: ['GET'])]
    public function desarchiver(Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $projet->setIsArchived(false);
        $entityManager->flush();

        $this->addFlash('success', 'Le projet a été restauré dans la liste active.');
        return $this->redirectToRoute('app_projet_archives');
    }

    /**
     * Suppression définitive (Optionnel).
     */
    #[Route('/{id}/delete', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projet);
            $entityManager->flush();
            $this->addFlash('danger', 'Le projet a été supprimé définitivement.');
        }

        return $this->redirectToRoute('app_projet_index');
    }

    /**
     * Détails du projet (Placé à la fin pour éviter les conflits de routes).
     */
    #[Route('/{id}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('admin/Projet/detailsProjet.html.twig', [
            'projet' => $projet,
        ]);
    }
}