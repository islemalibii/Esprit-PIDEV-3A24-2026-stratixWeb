<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Repository\ImportLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PdfService;
use Symfony\Component\Process\Process;

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

        // Correction du chemin : admin/Ressource/
        return $this->render('admin/Ressource/index.html.twig', [
            'ressources' => $ressources,
            'searchTerm' => $searchTerm,
            'quantiteTotale' => $quantiteTotale,
            'nombreTypes' => $nombreTypes,
            'imports' => $imports,
        ]);
    }

    /**
     * ANALYSE IA : Importation de PLUSIEURS fichiers CSV et calcul par Python
     */
    #[Route('/ressource/{id}/analyser', name: 'app_ressource_analyser')]
    public function analyser(Ressource $ressource, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $files = $request->files->get('csv_files');
            
            if ($files && is_array($files)) {
                $dataForAi = [];

                foreach ($files as $file) {
                    if ($file && ($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
                        fgetcsv($handle); // Sauter l'entête
                        
                        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if (isset($data[0]) && strtolower(trim($data[0])) === strtolower(trim($ressource->getNom()))) {
                                $dataForAi[] = [
                                    'fournisseur' => $data[3] ?? $fileName, 
                                    'prix' => (float)$data[1],
                                    'delai' => (int)$data[2]
                                ];
                            }
                        }
                        fclose($handle);
                    }
                }

                if (empty($dataForAi)) {
                    $this->addFlash('warning', "Aucune offre trouvée pour '" . $ressource->getNom() . "'.");
                    return $this->redirectToRoute('ressource_index');
                }

                $projectDir = $this->getParameter('kernel.project_dir');
                $process = new Process(['python', $projectDir . '/scripts/analyse_ia.py']);
                $process->setInput(json_encode($dataForAi));
                $process->run();

                if (!$process->isSuccessful()) {
                    $this->addFlash('error', "L'IA Stratix est indisponible. Tri manuel effectué.");
                    usort($dataForAi, fn($a, $b) => $a['prix'] <=> $b['prix']);
                    $resultatsIA = $dataForAi;
                } else {
                    $resultatsIA = json_decode($process->getOutput(), true);
                }

                // Correction du chemin : admin/Ressource/
                return $this->render('admin/Ressource/resultat_ia.html.twig', [
                    'ressource' => $ressource,
                    'resultats' => $resultatsIA
                ]);
            }
            $this->addFlash('error', "Veuillez sélectionner au moins un fichier CSV.");
        }

        // Correction du chemin : admin/Ressource/
        return $this->render('admin/Ressource/import_analyse.html.twig', [
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

            $this->addFlash('success', 'La ressource a été enregistrée avec succès !');
            return $this->redirectToRoute('ressource_index');
        }

        // Correction du chemin : admin/Ressource/
        return $this->render('admin/Ressource/form.html.twig', [
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
        
        // Correction du chemin : admin/Ressource/
        $html = $this->renderView('admin/Ressource/pdf.html.twig', [
            'ressources' => $ressources
        ]);
        $pdf->showPdfFile($html, 'Liste_Ressources_Stratix');
    }
}