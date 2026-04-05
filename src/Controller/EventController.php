<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    //back office
    #[Route('/responsable/evenement', name: 'resp_event_index')]
    public function responsableIndex(Request $request, EvenementRepository $repo): Response
    {
        $search = $request->query->get('search');
        $type_event = $request->query->get('type_event');

        if ($search) {
            $events = $repo->searchByTitle($search);
        } elseif ($type_event) {
            $events = $repo->filterByType($type_event);
        } else {
            $events = $repo->findByArchiveStatus(false);
        }

        return $this->render('admin/events/responsableEvent.html.twig', [
            'evenements' => $events,
            'archived' => $repo->findByArchiveStatus(true)
        ]);
    }

    #[Route('/responsable/evenement/archives', name: 'resp_event_archives')]
    public function archives(EvenementRepository $repo): Response
    {
        return $this->render('admin/events/archivedEvents.html.twig', [
            'archived' => $repo->findByArchiveStatus(true)
        ]);
    }
    #[Route('/responsable/evenement/new', name: 'resp_event_new')]
    #[Route('/responsable/evenement/edit/{id}', name: 'resp_event_edit')]
    public function save(Evenement $evenement = null, Request $request, EntityManagerInterface $em): Response
    {
        if (!$evenement) $evenement = new Evenement();

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($evenement->isArchived() === null) {
                $evenement->setIsArchived(false);
            }
            
            $em->persist($evenement);
            $em->flush();
            return $this->redirectToRoute('resp_event_index');
        }

        return $this->render('admin/events/formEvent.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement
        ]);
    }

    
    #[Route('/responsable/evenement/{id}', name: 'resp_event_show')]
    public function show(Evenement $evenement): Response
    {
        return $this->render('admin/events/showEvent.html.twig', [
            'evenement' => $evenement
        ]);
    }

    #[Route('/responsable/evenement/archive/{id}', name: 'resp_event_archive')]
    public function archive(Evenement $evenement, EntityManagerInterface $em): Response
    {
        $evenement->setIsArchived(!$evenement->isIsArchived());
        $em->flush();
        return $this->redirectToRoute('resp_event_index');
    }

    #[Route('/admin/evenement/delete/{id}', name: 'admin_event_delete', methods: ['POST'])]
    public function delete(Evenement $evenement, EntityManagerInterface $em): Response
    {
        $em->remove($evenement);
        $em->flush();
        return $this->redirectToRoute('resp_event_index');
    }

    
    // front office
    #[Route('/employee/events', name: 'emp_event_list')]
    public function employeeIndex(Request $request, EvenementRepository $repo): Response
    {
        $type = $request->query->get('type');
        $search = $request->query->get('search'); 

        if ($search) {
            $events = $repo->searchPlanifierByTitle($search);
        } elseif ($type) {
            $events = $repo->filterByTypeForEmployee($type);
        } else {
            $events = $repo->findVisibleForEmployees(); 
        }

        return $this->render('employee/list.html.twig', [
            'events' => $events
        ]);
    }
}