<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Repository\ImportLogRepository;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PdfService;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
     * ANALYSE IA : Importation d'un CSV spécifique et calcul par Python
     */
    #[Route('/ressource/{id}/analyser', name: 'app_ressource_analyser')]
    public function analyser(Ressource $ressource, Request $request): Response
    {
        // Si on reçoit le fichier CSV via POST
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');
            
            if ($file) {
                $dataForAi = [];
                if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
                    fgetcsv($handle); // Sauter l'entête
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // On filtre pour ne garder que les lignes qui concernent cette ressource
                        if (isset($data[0]) && strtolower($data[0]) === strtolower($ressource->getNom())) {
                            $dataForAi[] = [
                                'fournisseur' => "Source Importée", 
                                'prix' => (float)$data[1],
                                'delai' => (int)$data[2]
                            ];
                        }
                    }
                    fclose($handle);
                }

                if (empty($dataForAi)) {
                    $this->addFlash('error', "Aucune donnée pour '" . $ressource->getNom() . "' trouvée dans ce fichier.");
                    return $this->redirectToRoute('ressource_index');
                }

                // --- APPEL AU SCRIPT PYTHON ---
                $projectDir = $this->getParameter('kernel.project_dir');
                // Note : Assure-toi que python est dans ton PATH Windows
                $process = new Process(['python', $projectDir . '/scripts/analyse_ia.py']);
                $process->setInput(json_encode($dataForAi));
                $process->run();

                if (!$process->isSuccessful()) {
                    $this->addFlash('error', "L'IA n'a pas pu répondre. Utilisation du tri par défaut.");
                    usort($dataForAi, fn($a, $b) => $a['prix'] <=> $b['prix']);
                    $resultatsIA = $dataForAi;
                } else {
                    $resultatsIA = json_decode($process->getOutput(), true);
                }

                return $this->render('admin/ressource/resultat_ia.html.twig', [
                    'ressource' => $ressource,
                    'resultats' => $resultatsIA
                ]);
            }
        }

        // Si on arrive en GET, on affiche simplement le formulaire d'upload
        return $this->render('admin/ressource/import_analyse.html.twig', [
            'ressource' => $ressource
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

            $this->addFlash('success', 'Ressource enregistrée !');
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