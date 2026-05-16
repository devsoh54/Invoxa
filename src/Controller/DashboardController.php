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

    #[Route('/ca-semaine', name: 'api_dashboard_ca_semaine', methods: ['GET'])]
    public function caSemaine(FactureRepository $factureRepository): JsonResponse
    {
        $data = $factureRepository->getCAParSemaineByUser($this->getUser());
        return $this->json($data);
    }

    #[Route('/dernieres-factures', name: 'api_dashboard_dernieres_factures', methods: ['GET'])]
    public function dernieresFactures(FactureRepository $factureRepository): JsonResponse
    {
        $factures = $factureRepository->getLastFacturesByUser($this->getUser());

        $data = array_map(fn($f) => [
            'id'         => $f->getId(),
            'clientName' => $f->getClient()->getName(),
            'total'      => $f->getTotal(),
            'status'     => $f->getStatus(),
            'date'       => $f->getDate()?->format('d/m/Y'),
        ], $factures);

        return $this->json($data);
    }
}
