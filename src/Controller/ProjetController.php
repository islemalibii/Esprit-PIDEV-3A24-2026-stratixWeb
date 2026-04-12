<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projet')]
class ProjetController extends AbstractController
{
    // ─────────────────────────────────────────────
    //  LISTE (admin)
    // ─────────────────────────────────────────────
    #[Route('/', name: 'app_projet_index', methods: ['GET'])]
    public function index(Request $request, ProjetRepository $repo): Response
    {
        $search = $request->query->get('search');
        $statut = $request->query->get('statut');

        return $this->render('admin/Projet/listeProjets.html.twig', [
            'projets'       => $repo->findActiveWithFilters($search, $statut),
            'currentSearch' => $search,
            'currentStatut' => $statut,
        ]);
    }

    // ─────────────────────────────────────────────
    //  ARCHIVES
    // ─────────────────────────────────────────────
    #[Route('/archives', name: 'app_projet_archives', methods: ['GET'])]
    public function archives(ProjetRepository $repo): Response
    {
        return $this->render('admin/Projet/listeArchives.html.twig', [
            'projets' => $repo->findAllArchived(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  CRÉER (Contrainte date future activée)
    // ─────────────────────────────────────────────
    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();
        
        // CORRECTION : Ajout du groupe 'registration' pour valider la date de début au futur
        $form = $this->createForm(ProjetType::class, $projet, [
            'validation_groups' => ['Default', 'registration'],
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $file */
            $file = $form->get('cahierDesChargesFile')->getData();
            if ($file) {
                $uploadDir   = $this->getParameter('kernel.project_dir') . '/public/uploads/cahiers';
                $newFilename = uniqid('cdc_') . '_' . time() . '.' . $file->guessExtension();
                $file->move($uploadDir, $newFilename);
                $projet->setCahierDesCharges($newFilename);
            }

            if (!$projet->getStatut()) {
                $projet->setStatut('Planifié');
            }
            $projet->setIsArchived(false);

            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', '✅ Projet "' . $projet->getNom() . '" créé avec succès !');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('admin/Projet/ajouterProjet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  MODIFIER (Aucune contrainte sur la date passée)
    // ─────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
        // CORRECTION : Utilisation de 'Default' uniquement pour ignorer 'registration'
        $form = $this->createForm(ProjetType::class, $projet, [
            'validation_groups' => ['Default'],
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $file */
            $file = $form->get('cahierDesChargesFile')->getData();
            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/cahiers';

                if ($projet->getCahierDesCharges()) {
                    $oldPath = $uploadDir . '/' . $projet->getCahierDesCharges();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $newFilename = uniqid('cdc_') . '_' . time() . '.' . $file->guessExtension();
                $file->move($uploadDir, $newFilename);
                $projet->setCahierDesCharges($newFilename);
            }

            $em->flush();

            $this->addFlash('success', '✅ Projet "' . $projet->getNom() . '" modifié avec succès !');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('admin/Projet/modifierProjet.html.twig', [
            'projet' => $projet,
            'form'   => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  ARCHIVER / DÉSARCHIVER
    // ─────────────────────────────────────────────
    #[Route('/{id}/archiver', name: 'app_projet_archive_action')]
    public function archiver(Projet $p, EntityManagerInterface $em): Response
    {
        $p->setIsArchived(true);
        $em->flush();
        $this->addFlash('info', '📦 Projet archivé.');
        return $this->redirectToRoute('app_projet_index');
    }

    #[Route('/{id}/desarchiver', name: 'app_projet_unarchive_action')]
    public function desarchiver(Projet $p, EntityManagerInterface $em): Response
    {
        $p->setIsArchived(false);
        $em->flush();
        $this->addFlash('success', '✅ Projet restauré avec succès.');
        return $this->redirectToRoute('app_projet_archives');
    }

    #[Route('/{id}/chat', name: 'app_projet_chat')]
    public function chat(Projet $projet): Response
    {
        return $this->render('admin/Projet/chat.html.twig', [
            'projet' => $projet,
        ]);
    }

    // ─────────────────────────────────────────────
    //  VUE EMPLOYÉ
    // ─────────────────────────────────────────────
    #[Route('/employee/mes-projets', name: 'app_projet_employee_index')]
    public function indexEmployee(ProjetRepository $repo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('employee/projetEmploye.html.twig', [
            'projets' => $repo->findProjetsPourEmploye($user),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DÉTAILS
    // ─────────────────────────────────────────────
    #[Route('/{id}/show', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('admin/Projet/detailsProjet.html.twig', [
            'projet' => $projet,
        ]);
    }

    // ─────────────────────────────────────────────
    //  DÉTAILS VUE EMPLOYÉ
    // ─────────────────────────────────────────────
    #[Route('/employee/projet/{id}/show', name: 'app_projet_employe_show', methods: ['GET'])]
    public function showEmployee(Projet $projet): Response
    {
        return $this->render('employee/employeProjetDetails.html.twig', [
            'projet' => $projet,
        ]);
    }
}