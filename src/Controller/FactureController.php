<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Repository\ClientRepository;
use App\Repository\FactureRepository;
use App\Service\NumberFactureGenerator;
use DateTimeImmutable;
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
            'clientId'    => $facture->getClient()->getId(),
            'clientName'  => $facture->getClient()->getName(),
            'number' => $facture->getNumber(),
            'status' => $facture->getStatus(),
            'date' => $facture->getDate()->format('d/m/Y'),
            'dueDate' => $facture->getDueDate()->format('d/m/Y'),
            'total' => $facture->getTotal()
        ], $factures);

        return $this->json($data);
    }

    #[Route('/create', name: 'api_factures_create', methods: ['POST'])]
    #[Route('/edit/{id}', name: 'api_factures_edit', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, ?Facture $facture, FactureRepository $fr, ClientRepository $cr, NumberFactureGenerator $numGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $facture = $facture ?? new Facture();

        $client = $cr->find($data['client']);

        $date =  new DateTimeImmutable($data['date']);
        $duedate = new DateTimeImmutable($data['echeance']);

        $number = $numGenerator->generateNumber($fr);

        $facture->setClient($client);
        $facture->setStatus($data['status']);
        $facture->setNumber($number);
        $facture->setDate($date);
        $facture->setDueDate($duedate);
        $facture->setTotal($data['total']);

        $em->persist($facture);
        $em->flush();

        return $this->json(['id' => $facture->getId()], 201);
    }
}
