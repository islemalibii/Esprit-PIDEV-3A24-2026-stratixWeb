<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\PdfService; // Importation du service PDF
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    /**
     * Affiche la liste des produits avec recherche, tri et statistiques.
     */
    #[Route('/produit', name: 'produit_index')]
    public function index(ProduitRepository $repository, Request $request): Response
    {
        // 1. Récupération des paramètres de recherche et de tri
        $searchTerm = $request->query->get('q', '');
        $sortBy = $request->query->get('sort', 'nom');
        $direction = $request->query->get('direction', 'asc');

        if ($searchTerm) {
            // Assure-toi d'avoir implémenté findBySearch dans ProduitRepository
            $produits = $repository->findBySearch($searchTerm);
        } else {
            $produits = $repository->findBy([], [$sortBy => $direction]);
        }

        // 2. Calcul des statistiques pour le tableau de bord Stratix
        $stats = [
            'total' => count($produits),
            'stockFaible' => 0,
            'valeurStock' => 0,
        ];

        foreach ($produits as $p) {
            if ($p->getStockActuel() <= $p->getStockMin()) {
                $stats['stockFaible']++;
            }
            $stats['valeurStock'] += ($p->getPrix() * $p->getStockActuel());
        }

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'searchTerm' => $searchTerm,
            'stats' => $stats
        ]);
    }

    /**
     * Formulaire combiné pour la création et l'édition d'un produit.
     */
    #[Route('/produit/new', name: 'produit_new')]
    #[Route('/produit/edit/{id}', name: 'produit_edit')]
    public function form(?Produit $produit = null, Request $request, EntityManagerInterface $em): Response
    {
        $editMode = ($produit !== null);

        if (!$produit) {
            $produit = new Produit();
            $produit->setDateCreation(new \DateTime()); 
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 3. Gestion de l'upload de l'image
            $imageFile = $form->get('image_file')->getData();
            if ($imageFile) {
                $newFilename = 'produit_'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('produits_images_directory'), 
                        $newFilename
                    );
                    $produit->setImagePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'enregistrement de l'image.");
                }
            }

            // 4. Validation logique des dates (Métier avancé)
            if ($produit->getDatePeremption() && $produit->getDateFabrication() && 
                $produit->getDatePeremption() < $produit->getDateFabrication()) {
                $this->addFlash('error', 'La date de péremption ne peut pas être antérieure à la fabrication.');
            } else {
                $em->persist($produit);
                $em->flush();
                
                $this->addFlash('success', $editMode ? 'Produit mis à jour !' : 'Produit ajouté avec succès !');
                return $this->redirectToRoute('produit_index');
            }
        }

        return $this->render('produit/formulaire.html.twig', [
            'form' => $form->createView(),
            'editMode' => $editMode,
            'produit' => $produit // Nécessaire pour l'affichage de l'image actuelle
        ]);
    }

    /**
     * Exportation de la liste des produits en format PDF.
     */
    #[Route('/produit/pdf', name: 'produit_pdf')]
    public function generatePdf(ProduitRepository $repository, PdfService $pdf): void
    {
        $produits = $repository->findAll();
        $html = $this->renderView('produit/pdf.html.twig', [
            'produits' => $produits
        ]);
        
        $pdf->showPdfFile($html, 'Rapport_Produits_Stratix_' . date('Y-m-d'));
    }

    /**
     * Suppression sécurisée d'un produit via jeton CSRF.
     */
    #[Route('/produit/delete/{id}', name: 'produit_delete', methods: ['POST'])]
    public function delete(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Le produit a été retiré de l\'inventaire.');
        }
        
        return $this->redirectToRoute('produit_index');
    }
}