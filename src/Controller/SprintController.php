<?php

namespace App\Controller;

use App\Entity\Sprint;
use App\Entity\Projet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/sprint')]
class SprintController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_sprint_new', methods: ['POST'])]
    public function new(Projet $projet, Request $request, EntityManagerInterface $em): Response
    {
        $sprint = new Sprint();
        $sprint->setNom($request->request->get('nom'));
        $sprint->setDateDebut(new \DateTime($request->request->get('dateDebut')));
        $sprint->setDateFin(new \DateTime($request->request->get('dateFin')));
        $sprint->setObjectif($request->request->get('objectif'));
        $sprint->setStatut('En attente');
        $sprint->setProjet($projet);

        $em->persist($sprint);
        $em->flush();

        $this->addFlash('success', 'Sprint ajouté avec succès !');
        return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
    }

    #[Route('/edit/{id}', name: 'app_sprint_edit', methods: ['POST'])]
    public function edit(Sprint $sprint, Request $request, EntityManagerInterface $em): Response
    {
        $sprint->setNom($request->request->get('nom'));
        $sprint->setDateDebut(new \DateTime($request->request->get('dateDebut')));
        $sprint->setDateFin(new \DateTime($request->request->get('dateFin')));
        $sprint->setObjectif($request->request->get('objectif'));
        
        $em->flush();

        $this->addFlash('success', 'Sprint mis à jour.');
        return $this->redirectToRoute('app_projet_show', ['id' => $sprint->getProjet()->getId()]);
    }

    #[Route('/delete/{id}', name: 'app_sprint_delete', methods: ['POST'])]
    public function delete(Sprint $sprint, Request $request, EntityManagerInterface $em): Response
    {
        $projetId = $sprint->getProjet()->getId();
        
        if ($this->isCsrfTokenValid('delete' . $sprint->getId(), $request->request->get('_token'))) {
            $em->remove($sprint);
            $em->flush();
            $this->addFlash('success', 'Sprint supprimé.');
        }

        return $this->redirectToRoute('app_projet_show', ['id' => $projetId]);
    }
}