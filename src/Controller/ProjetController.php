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
     * Le filtrage "temps réel" est géré côté client (Navigateur) via JS.
     */
    #[Route('/', name: 'app_projet_index', methods: ['GET'])]
    public function index(ProjetRepository $projetRepository): Response
    {
        return $this->render('projet/listeProjets.html.twig', [
            // On récupère tous les projets non archivés, triés par les plus récents
            'projets' => $projetRepository->findBy(['isArchived' => false], ['id' => 'DESC']),
        ]);
    }

    /**
     * Liste des archives
     */
    #[Route('/archives', name: 'app_projet_archives', methods: ['GET'])]
    public function archives(ProjetRepository $projetRepository): Response
    {
        return $this->render('projet/listeArchives.html.twig', [
            'projets' => $projetRepository->findBy(['isArchived' => true], ['id' => 'DESC']),
        ]);
    }

    /**
     * Création d'un projet.
     * Le statut est "Planifié" par défaut et bloqué dans le formulaire.
     */
    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();

            $this->addFlash('success', 'Projet créé avec succès.');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('projet/ajouterProjet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un projet.
     * Le statut devient modifiable ici.
     */
    #[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Projet mis à jour.');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('projet/modifierProjet.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Détails du projet
     */
    #[Route('/{id}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('projet/detailsProjet.html.twig', [
            'projet' => $projet,
        ]);
    }

    /**
     * Action d'archivage
     */
    #[Route('/{id}/archiver', name: 'app_projet_archive_action', methods: ['GET'])]
    public function archiver(Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $projet->setIsArchived(true);
        $entityManager->flush();

        $this->addFlash('warning', 'Projet déplacé dans les archives.');
        return $this->redirectToRoute('app_projet_index');
    }

    /**
     * Action de désarchivage
     */
    #[Route('/{id}/desarchiver', name: 'app_projet_unarchive_action', methods: ['GET'])]
    public function desarchiver(Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $projet->setIsArchived(false);
        $entityManager->flush();

        $this->addFlash('success', 'Projet restauré avec succès.');
        return $this->redirectToRoute('app_projet_archives');
    }
}