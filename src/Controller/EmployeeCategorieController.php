<?php

namespace App\Controller;

use App\Entity\CategorieService;
use App\Repository\CategorieServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employee/categories')]
final class EmployeeCategorieController extends AbstractController
{
    #[Route('/', name: 'app_employee_categorie_index', methods: ['GET'])]
    public function index(Request $request, CategorieServiceRepository $categorieServiceRepository): Response
    {
        $search = $request->query->get('search', '');

        $queryBuilder = $categorieServiceRepository->createQueryBuilder('c')
            ->where('c.archive = false');

        if (!empty($search)) {
            $queryBuilder->andWhere('c.nom LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $categories = $queryBuilder->orderBy('c.nom', 'ASC')->getQuery()->getResult();

        return $this->render('employee/categorie_service/index.html.twig', [
            'categorie_services' => $categories,
            'search' => $search,
            'showArchives' => false,
        ]);
    }

    #[Route('/{id}', name: 'app_employee_categorie_show', methods: ['GET'])]
    public function show(CategorieService $categorieService): Response
    {
        if ($categorieService->isArchive()) {
            throw $this->createNotFoundException('Catégorie non disponible.');
        }
        
        return $this->render('employee/categorie_service/show.html.twig', [
            'categorie_service' => $categorieService,
        ]);
    }
}