<?php
namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RessourceController extends AbstractController
{
    /**
     * Affiche la liste des ressources (Equivalent de ton index FXML)
     */
    #[Route('/ressource', name: 'ressource_index', methods: ['GET'])]
    public function index(RessourceRepository $repository, Request $request): Response
    {
        // Récupération du terme de recherche (searchField)
        $searchTerm = $request->query->get('q');
        
        if ($searchTerm) {
            // Tu devras créer cette méthode "findBySearch" dans ton RessourceRepository
            $ressources = $repository->findBySearch($searchTerm);
        } else {
            $ressources = $repository->findAll();
        }

        // Calcul des statistiques (Equivalent de tes labels statsTotal, statsQuantiteTotale, etc.)
        $quantiteTotale = 0;
        $typesUniques = [];
        foreach ($ressources as $r) {
            $quantiteTotale += $r->getQuantite();
            $typesUniques[] = $r->getTypeRessource();
        }
        $nombreTypes = count(array_unique($typesUniques));

        return $this->render('admin/ressource/index.html.twig', [
            'ressources' => $ressources,
            'searchTerm' => $searchTerm,
            'quantiteTotale' => $quantiteTotale,
            'nombreTypes' => $nombreTypes,
        ]);
    }

    /**
     * Formulaire d'Ajout et de Modification (Equivalent de FormulaireRessourceController)
     */
    #[Route('/ressource/form/{id?}', name: 'ressource_form')]
    public function form(Ressource $ressource = null, Request $request, EntityManagerInterface $em): Response
    {
        // Mode Ajout : Si l'ID est vide dans l'URL, on crée un nouvel objet
        if (!$ressource) {
            $ressource = new Ressource();
        }

        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Logique "Autre Type" : Si "Autre" est sélectionné dans la ComboBox
            // On récupère la valeur du champ texte personnalisé envoyé par le formulaire
            $typePerso = $request->request->get('autre_type_field');
            if ($form->get('type_ressource')->getData() === 'Autre' && !empty($typePerso)) {
                $ressource->setTypeRessource(trim($typePerso));
            }

            // Enregistrement (Equivalent de serviceRessource.add/update)
            $em->persist($ressource);
            $em->flush();

            $this->addFlash('success', 'La ressource a été enregistrée avec succès !');
            return $this->redirectToRoute('ressource_index');
        }

        return $this->render('admin/ressource/form.html.twig', [
            'form' => $form->createView(),
            'editMode' => $ressource->getId() !== null,
            'ressource' => $ressource
        ]);
    }

    /**
     * Suppression d'une ressource (Equivalent de serviceRessource.delete)
     */
    #[Route('/ressource/delete/{id}', name: 'ressource_delete', methods: ['POST'])]
    public function delete(Ressource $ressource, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification du jeton CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->request->get('_token'))) {
            $em->remove($ressource);
            $em->flush();
            $this->addFlash('success', 'Ressource supprimée.');
        }

        return $this->redirectToRoute('ressource_index');
    }
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