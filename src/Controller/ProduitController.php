<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\PdfService; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'produit_index')]
    public function index(ProduitRepository $repository, Request $request): Response
    {
        $searchTerm = $request->query->get('q', '');
        $sortBy = $request->query->get('sort', 'nom');
        $direction = $request->query->get('direction', 'asc');

        $produits = $searchTerm 
            ? $repository->findBySearch($searchTerm) 
            : $repository->findBy([], [$sortBy => $direction]);

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

        return $this->render('admin/produit/index.html.twig', [
            'produits' => $produits,
            'searchTerm' => $searchTerm,
            'stats' => $stats
        ]);
    }

    #[Route('/produit/new', name: 'produit_new')]
    #[Route('/produit/edit/{id}', name: 'produit_edit')]
    public function form(?Produit $produit = null, Request $request, EntityManagerInterface $em): Response
    {
        $editMode = ($produit !== null);
        $aujourdhui = new \DateTime('today');

        if (!$produit) {
            $produit = new Produit();
            $produit->setDateCreation(new \DateTime()); 
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 1. Sécurité supplémentaire : Date de fabrication
            // On accepte le passé seulement en mode EDITION pour ne pas bloquer les vieux produits
            if (!$editMode && $produit->getDateFabrication() < $aujourdhui) {
                $this->addFlash('error', 'La date de fabrication ne peut pas être antérieure à aujourd\'hui.');
                return $this->render('admin/produit/formulaire.html.twig', [
                    'form' => $form->createView(),
                    'editMode' => $editMode
                ]);
            }

            // 2. Gestion de l'image
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

            // 3. Sauvegarde
            $em->persist($produit);
            $em->flush();
            
            $this->addFlash('success', $editMode ? 'Produit mis à jour !' : 'Produit ajouté avec succès !');
            return $this->redirectToRoute('produit_index');
        }

        return $this->render('admin/produit/formulaire.html.twig', [
            'form' => $form->createView(),
            'editMode' => $editMode,
            'produit' => $produit 
        ]);
    }

    #[Route('/produit/pdf', name: 'produit_pdf')]
    public function generatePdf(ProduitRepository $repository, PdfService $pdf): Response
    {
        $produits = $repository->findAll();
        $html = $this->renderView('admin/produit/pdf.html.twig', [
            'produits' => $produits
        ]);
        
        // On retourne la réponse générée par le service (Content-Type: application/pdf)
        return $pdf->showPdfFile($html, 'Rapport_Produits_Stratix_' . date('Y-m-d'));
    }

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