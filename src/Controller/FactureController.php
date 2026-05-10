<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\FactureItem;
use App\Repository\ClientRepository;
use App\Repository\FactureItemRepository;
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
            'total' => $facture->getTotal(),
            'factureItems' => array_map(
                fn($item) => [
                    'id' => $item->getId(),
                    'description' => $item->getDescription(),
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice(),
                    'total' => $item->getTotal(),
                ],
                $facture->getFactureItems()->toArray()
            )
        ], $factures);

        return $this->json($data);
    }

    #[Route('/create', name: 'api_factures_create', methods: ['POST'])]
    #[Route('/edit/{id}', name: 'api_factures_edit', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, ?Facture $facture, ?FactureItem $fi, FactureRepository $fr, FactureItemRepository $fir, ClientRepository $cr, NumberFactureGenerator $numGenerator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $isEdit = $facture !== null;
        $facture = $facture ?? new Facture();

        $client = $cr->find($data['client']);

        $date =  new DateTimeImmutable($data['date']);
        $duedate = new DateTimeImmutable($data['echeance']);

        // Numéro uniquement à la création
        if (!$isEdit) {
            $facture->setNumber($numGenerator->generateNumber($fr));
        }

        $facture->setClient($client);
        $facture->setStatus($data['status']);
        $facture->setDate($date);
        $facture->setDueDate($duedate);
        $facture->setTotal($data['total']);

        // Récupère les ids envoyés par le front (possible uniquement lors d'une édition)
        $submittedIds = array_filter(
            array_column($data['items'], 'id')
        );

        // Supprime les items qui ne sont plus dans la liste
        if ($isEdit) {
            foreach ($facture->getFactureItems() as $existingItem) {
                if (!in_array($existingItem->getId(), $submittedIds)) {
                    $facture->removeFactureItem($existingItem);
                    $em->remove($existingItem);
                }
            }
        }

        foreach ($data['items'] as $item) {
            if (!empty($item['id'])) {
                // Mise à jour d'un item existant
                $factureItem = $fir->find($item['id']);
            } else {
                // Nouvel item
                $factureItem = new FactureItem();
                $factureItem->setFacture($facture);
            }
            $factureItem->setDescription($item['description']);
            $factureItem->setQuantity($item['quantity']);
            $factureItem->setPrice($item['price']);
            $factureItem->setTotal($item['total']);
            $em->persist($factureItem);
        }

        $em->persist($facture);
        $em->flush();

        return $this->json(['id' => $facture->getId()], 201);
    }

    #[Route('/delete/{id}', name: 'api_facture_delete', methods: ['GET'])]
    public function delete(EntityManagerInterface $em, ?Facture $facture): JsonResponse
    {

        $em->remove($facture);
        $em->flush();

        return $this->json(['id' => $facture->getId()], 201);
    }
}
