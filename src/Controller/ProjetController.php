<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use App\Service\AiResumeService;
use Doctrine\ORM\EntityManagerInterface;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    //  CRÉER
    // ─────────────────────────────────────────────
    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();
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

            $this->addFlash('success', '✅ Projet "' . $projet->getNom() . '" créé !');

            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('admin/Projet/ajouterProjet.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  MODIFIER
    // ─────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
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

            $this->addFlash('success', '✅ Projet modifié !');

            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('admin/Projet/modifierProjet.html.twig', [
            'projet' => $projet,
            'form'   => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  IA : ANALYSE DU CAHIER DES CHARGES (OpenAI)
    // ─────────────────────────────────────────────
    #[Route('/employee/projet/{id}/analyser-ia', name: 'app_projet_analyser_ia', methods: ['GET'])]
    public function analyserProjetExistant(Projet $projet, AiResumeService $aiService): JsonResponse
    {
        $fileName = $projet->getCahierDesCharges();

        if (!$fileName) {
            return $this->json(['error' => 'Aucun fichier trouvé pour ce projet.'], 404);
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/cahiers/' . $fileName;

        if (!file_exists($filePath)) {
            return $this->json(['error' => 'Fichier introuvable.'], 404);
        }

        try {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $contenu = '';

            // 📄 Lecture PDF
            if ($extension === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                $contenu = $pdf->getText();
            } else {
                $contenu = file_get_contents($filePath);
            }

            $contenu = trim($contenu);

            if (empty($contenu)) {
                return $this->json(['error' => 'Document vide ou illisible.'], 400);
            }

            // 🔥 appel IA
            $result = $aiService->generateSummary(mb_substr($contenu, 0, 3000));

            // nettoyage JSON
            $clean = trim($result);

            if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $clean, $matches)) {
                $clean = $matches[0];
            }

            $data = json_decode($clean, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error' => 'Erreur format JSON IA',
                    'raw' => $result
                ], 500);
            }

            return $this->json([
                'success' => true,
                'resume_court' => $data['resume_court'] ?? '',
                'resume_detaille' => $data['resume_detaille'] ?? ''
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    // ─────────────────────────────────────────────
    //  VUES EMPLOYÉ
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

    #[Route('/employee/projet/{id}/show', name: 'app_projet_employe_show', methods: ['GET'])]
    public function showEmployee(Projet $projet): Response
    {
        return $this->render('employee/employeProjetDetails.html.twig', [
            'projet' => $projet,
        ]);
    }

    // ─────────────────────────────────────────────
    //  ACTIONS
    // ─────────────────────────────────────────────
    #[Route('/{id}/archiver', name: 'app_projet_archive_action')]
    public function archiver(Projet $p, EntityManagerInterface $em): Response
    {
        $p->setIsArchived(true);
        $em->flush();

        return $this->redirectToRoute('app_projet_index');
    }

    #[Route('/{id}/chat', name: 'app_projet_chat')]
    public function chat(Projet $projet): Response
    {
        return $this->render('admin/Projet/chat.html.twig', [
            'projet' => $projet
        ]);
    }

    #[Route('/{id}/show', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('admin/Projet/detailsProjet.html.twig', [
            'projet' => $projet
        ]);
    }
}