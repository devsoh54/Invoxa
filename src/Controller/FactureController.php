<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/factures')]
final class FactureController extends AbstractController
{
 
    #[Route('/list', name: 'api_factures_list', methods: ['GET'])]
    public function list(FactureRepository $factureRepository): JsonResponse
    {
        //par défaut on affiche les factures de tout les clients de l'utilisateur connecté
        $factures = $factureRepository->findAllFacturesByUser($this->getUser());

        $data = array_map(fn($facture) => [
            'id'    => $facture->getId(),
            'Client'  => $facture->getName(),
            'number' => $facture->getNumber(),
            'status' => $facture->getStatus(),
            'date' => $facture->getDate(),
            'dueDate' => $facture->getDueDate(),
            'total' => $facture->getTotal()
        ], $factures);

        return $this->json($data);
    }

  
}
