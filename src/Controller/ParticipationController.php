<?php
// src/Controller/ParticipationController.php

namespace App\Controller;
use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Service\ParticipationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/participation')]
class ParticipationController extends AbstractController
{
    public function __construct(private ParticipationService $participationService) {}

    #[Route('/join/{id}', name: 'participation_join', methods: ['POST'])]
    public function join(Evenement $evenement): JsonResponse
    {
        $user = $this->getUser();

    if (!$user instanceof Utilisateur) {
        return $this->json(['success' => false, 'message' => 'Vous devez être connecté.'], 401);
    }

    $result = $this->participationService->participate($evenement, $user->getEmail());

    return $this->json($result, $result['success'] ? 200 : 409);
    }

    #[Route('/cancel/{id}', name: 'participation_cancel', methods: ['POST'])]
    public function cancel(Evenement $evenement): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            return $this->json(['success' => false, 'message' => 'Vous devez être connecté.'], 401);
        }

        $result = $this->participationService->cancelParticipation($evenement, $user->getUserIdentifier());

        return $this->json($result, $result['success'] ? 200 : 409);
    }
}