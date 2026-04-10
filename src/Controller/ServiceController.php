<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\CategorieService;
use App\Entity\Utilisateur;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use App\Repository\CategorieServiceRepository;
use App\Repository\UtilisateurRepository;
use App\Service\ExchangeRateService;
use App\Service\GroqService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/services')]
final class ServiceController extends AbstractController
{
    #[Route('/', name: 'app_service_index', methods: ['GET'])]
    public function index(Request $request, ServiceRepository $serviceRepository, CategorieServiceRepository $categorieServiceRepository): Response
    {
        $search = $request->query->get('search', '');
        $categorie = $request->query->get('categorie', '');
        $archive = $request->query->get('archive', '0') === '1';

        $queryBuilder = $serviceRepository->createQueryBuilder('s')
            ->leftJoin('s.categorie', 'c')
            ->where('s.archive = :archive')
            ->setParameter('archive', $archive);

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

        return $this->render('admin/service/index.html.twig', [
            'services' => $services,
            'categories' => $categories,
            'search' => $search,
            'selectedCategorie' => $categorie,
            'showArchives' => $archive,
        ]);
    }

    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $service->setDateCreation(new \DateTime());
        $service->setArchive(false);

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service créé avec succès.');
            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render('admin/service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Service modifié avec succès.');
            return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/archive', name: 'app_service_archive', methods: ['POST'])]
    public function archive(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('archive'.$service->getId(), $request->request->get('_token'))) {
            $service->setArchive(!$service->isArchive());
            $entityManager->flush();

            $message = $service->isArchive() ? 'Service archivé.' : 'Service restauré.';
            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service supprimé avec succès.');
        }

        return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/api/exchange-rates', name: 'api_exchange_rates', methods: ['GET'])]
    public function getExchangeRates(ExchangeRateService $exchangeRateService): JsonResponse
    {
        try {
            $usd = $exchangeRateService->convertir(1, 'TND', 'USD');
            $eur = $exchangeRateService->convertir(1, 'TND', 'EUR');
            
            return $this->json([
                'success' => true,
                'base' => 'TND',
                'rates' => [
                    'USD' => $usd,
                    'EUR' => $eur
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/assistant/ask', name: 'api_assistant_ask', methods: ['POST'])]
    public function assistantAsk(Request $request, ServiceRepository $serviceRepository, GroqService $groqService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';
        
        if (empty($question)) {
            return $this->json(['error' => 'Question vide'], 400);
        }

        try {
            $services = $serviceRepository->findBy(['archive' => false]);
            $groqService->setServices($services);
            $response = $groqService->ask($question);
            
            return $this->json(['response' => $response]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

#[Route('/api/test-groq', name: 'api_test_groq', methods: ['GET'])]
public function testGroq(): JsonResponse
{
    $apiKey = '';
    
    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'user', 'content' => 'Dis "API Groq fonctionne parfaitement" en français']
        ]
    ];
    
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    return $this->json([
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => json_decode($response, true)
    ]);
}
}