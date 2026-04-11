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
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/projet')]
class ProjetController extends AbstractController
{
    #[Route('/', name: 'app_projet_index', methods: ['GET'])]
public function index(Request $request, ProjetRepository $repo): Response
{
    // On récupère les valeurs des inputs HTML (name="search" et name="statut")
    $search = $request->query->get('search');
    $statut = $request->query->get('statut');

    // On utilise notre nouvelle méthode de filtrage
    $projets = $repo->findActiveWithFilters($search, $statut);

    return $this->render('admin/Projet/listeProjets.html.twig', [
        'projets' => $projets,
        'currentSearch' => $search, // Utile pour garder la valeur dans l'input après validation
        'currentStatut' => $statut,
    ]);
}

    #[Route('/archives', name: 'app_projet_archives', methods: ['GET'])]
    public function archives(ProjetRepository $repo): Response
    {
        return $this->render('admin/Projet/listeArchives.html.twig', [
            'projets' => $repo->findAllArchived(),
        ]);
    }

   

#[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $projet = new Projet();
    $form = $this->createForm(ProjetType::class, $projet);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        
        // --- Gestion de l'upload du fichier ---
        /** @var UploadedFile|null $file */
        $file = $form->get('cahierDesChargesFile')->getData();
        if ($file) {
            // Génère un nom unique pour éviter les conflits
            $newFilename = uniqid('cdc_') . '_' . time() . '.' . $file->guessExtension();
            
            // Déplace le fichier dans le dossier public/uploads/cahiers
            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/cahiers',
                $newFilename
            );
            $projet->setCahierDesCharges($newFilename);
        }

        $projet->setStatut($projet->getStatut() ?? 'Planifié');
        $projet->setIsArchived(false);
        $em->persist($projet);
        $em->flush();

        $this->addFlash('success', '✅ Projet "' . $projet->getNom() . '" créé avec succès !');
        return $this->redirectToRoute('app_projet_index');
    }

    return $this->render('admin/projet/ajouterProjet.html.twig', ['form' => $form]);
}

#[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Projet $projet, EntityManagerInterface $em): Response
{
    $form = $this->createForm(ProjetType::class, $projet);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // --- Gestion du nouveau fichier (si l'utilisateur en uploade un) ---
        /** @var UploadedFile|null $file */
        $file = $form->get('cahierDesChargesFile')->getData();
        if ($file) {
            // Supprime l'ancien fichier s'il existe
            $oldFile = $this->getParameter('kernel.project_dir') . '/public/uploads/cahiers/' . $projet->getCahierDesCharges();
            if ($projet->getCahierDesCharges() && file_exists($oldFile)) {
                unlink($oldFile);
            }

            $newFilename = uniqid('cdc_') . '_' . time() . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/cahiers',
                $newFilename
            );
            $projet->setCahierDesCharges($newFilename);
        }

        $em->flush();
        $this->addFlash('success', '✅ Projet modifié avec succès !');
        return $this->redirectToRoute('app_projet_index');
    }

    return $this->render('admin/Projet/modifierProjet.html.twig', [
        'projet' => $projet,
        'form'   => $form->createView(),
    ]);
}

    #[Route('/{id}/archiver', name: 'app_projet_archive_action')]
    public function archiver(Projet $p, EntityManagerInterface $em): Response
    {
        $p->setIsArchived(true);
        $em->flush();
        return $this->redirectToRoute('app_projet_index');
    }

    
    #[Route('/{id}/desarchiver', name: 'app_projet_unarchive_action')]
    public function desarchiver(Projet $p, EntityManagerInterface $em): Response
    {
        $p->setIsArchived(false);
        $em->flush();
        return $this->redirectToRoute('app_projet_archives');
    }

    #[Route('/employee/mes-projets', name: 'app_projet_employee_index')]
    public function indexEmployee(ProjetRepository $repo): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        return $this->render('employee/projetEmploye.html.twig', [
            'projets' => $repo->findProjetsPourEmploye($user),
        ]);
    }

    #[Route('/{id}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('admin/Projet/detailsProjet.html.twig', ['projet' => $projet]);
    }
}