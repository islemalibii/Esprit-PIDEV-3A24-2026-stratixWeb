<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Repository\CategorieServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employee/services')]
final class EmployeeServiceController extends AbstractController
{
    #[Route('/', name: 'app_employee_service_index', methods: ['GET'])]
    public function index(Request $request, ServiceRepository $serviceRepository, CategorieServiceRepository $categorieServiceRepository): Response
    {
        $search = $request->query->get('search', '');
        $categorie = $request->query->get('categorie', '');

        $queryBuilder = $serviceRepository->createQueryBuilder('s')
            ->leftJoin('s.categorie', 'c')
            ->where('s.archive = false');

        if (!empty($search)) {
            $queryBuilder->andWhere('s.titre LIKE :search OR s.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($categorie)) {
            $queryBuilder->andWhere('c.nom = :categorie')
                ->setParameter('categorie', $categorie);
        }

        $services = $queryBuilder->orderBy('s.id', 'DESC')->getQuery()->getResult();
        $categories = $categorieServiceRepository->findBy(['archive' => false]);

        return $this->render('employee/service/index.html.twig', [
            'services' => $services,
            'categories' => $categories,
            'search' => $search,
            'selectedCategorie' => $categorie,
            'showArchives' => false,
        ]);
    }

    #[Route('/{id}', name: 'app_employee_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        if ($service->isArchive()) {
            throw $this->createNotFoundException('Service non disponible.');
        }
        
        return $this->render('employee/service/show.html.twig', [
            'service' => $service,
        ]);
    }
}