<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\FactureRepository;

#[IsGranted('ROLE_USER')]
#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/kpi', name: 'api_dashboard_kpi', methods: ['GET'])]
    public function kpi(FactureRepository $factureRepository): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'chiffreAffaires'  => $factureRepository->getTotalByUser($user),
            'facturesEnvoyees' => $factureRepository->countByStatusAndUser('sent', $user),
            'enAttente'        => $factureRepository->countByStatusAndUser('draft', $user),
            'enRetard'         => $factureRepository->countByStatusAndUser('late', $user),
        ]);
    }
}
