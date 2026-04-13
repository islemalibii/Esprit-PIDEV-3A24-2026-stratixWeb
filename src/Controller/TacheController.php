<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tache')]
final class TacheController extends AbstractController
{
    #[Route(name: 'app_tache_index', methods: ['GET'])]
    public function index(TacheRepository $tacheRepository): Response
    {
        $taches = $tacheRepository->findAll();
        
        // Calcul des statistiques
        $total = count($taches);
        $aFaire = 0;
        $enCours = 0;
        $terminees = 0;
        $haute = 0;
        $moyenne = 0;
        $basse = 0;
        
        foreach ($taches as $tache) {
            // Statistiques par statut
            if ($tache->getStatut() === 'A_FAIRE') $aFaire++;
            if ($tache->getStatut() === 'EN_COURS') $enCours++;
            if ($tache->getStatut() === 'TERMINEE') $terminees++;
            
            // Statistiques par priorité
            if ($tache->getPriorite() === 'HAUTE') $haute++;
            if ($tache->getPriorite() === 'MOYENNE') $moyenne++;
            if ($tache->getPriorite() === 'BASSE') $basse++;
        }
        
<<<<<<< HEAD
        return $this->render('tache/index.html.twig', [
=======
        return $this->render('admin/tache/index.html.twig', [
>>>>>>> origin/master
            'taches' => $taches,
            'total' => $total,
            'a_faire' => $aFaire,
            'en_cours' => $enCours,
            'terminees' => $terminees,
            'haute' => $haute,
            'moyenne' => $moyenne,
            'basse' => $basse,
        ]);
    }

    #[Route('/new', name: 'app_tache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tache = new Tache();
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            $this->addFlash('success', '✅ Tâche ajoutée avec succès !');
            return $this->redirectToRoute('app_tache_index', [], Response::HTTP_SEE_OTHER);
        }

<<<<<<< HEAD
        return $this->render('tache/new.html.twig', [
=======
        return $this->render('admin/tache/new.html.twig', [
>>>>>>> origin/master
            'tache' => $tache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tache_show', methods: ['GET'])]
    public function show(Tache $tache): Response
    {
<<<<<<< HEAD
        return $this->render('tache/show.html.twig', [
=======
        return $this->render('admin/tache/show.html.twig', [
>>>>>>> origin/master
            'tache' => $tache,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TacheType::class, $tache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', '✅ Tâche modifiée avec succès !');
            return $this->redirectToRoute('app_tache_index', [], Response::HTTP_SEE_OTHER);
        }

<<<<<<< HEAD
        return $this->render('tache/edit.html.twig', [
=======
        return $this->render('admin/tache/edit.html.twig', [
>>>>>>> origin/master
            'tache' => $tache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tache_delete', methods: ['POST'])]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
<<<<<<< HEAD
=======
        // Vérification du token CSRF
>>>>>>> origin/master
        if ($this->isCsrfTokenValid('delete'.$tache->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tache);
            $entityManager->flush();
            
            $this->addFlash('success', '✅ Tâche supprimée avec succès !');
        } else {
            $this->addFlash('danger', '❌ Erreur lors de la suppression !');
        }

<<<<<<< HEAD
=======
        // CORRECTION : On retire le "admin/" qui n'a rien à faire dans un nom de route
>>>>>>> origin/master
        return $this->redirectToRoute('app_tache_index', [], Response::HTTP_SEE_OTHER);
    }
    
    // API pour les statistiques (AJAX)
    #[Route('/stats/data', name: 'app_tache_stats', methods: ['GET'])]
    public function stats(TacheRepository $tacheRepository): Response
    {
        $taches = $tacheRepository->findAll();
        
        $aFaire = 0;
        $enCours = 0;
        $terminees = 0;
        
        foreach ($taches as $tache) {
            if ($tache->getStatut() === 'A_FAIRE') $aFaire++;
            if ($tache->getStatut() === 'EN_COURS') $enCours++;
            if ($tache->getStatut() === 'TERMINEE') $terminees++;
        }
        
        return $this->json([
            'a_faire' => $aFaire,
            'en_cours' => $enCours,
            'terminees' => $terminees,
            'total' => count($taches),
        ]);
    }
}