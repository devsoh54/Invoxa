<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

#[IsGranted('ROLE_USER')]
final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    #[Route('/api/clients', name: 'api_clients', methods: ['GET'])]
    public function list(ClientRepository $clientRepository): JsonResponse
    {
        $clients = $clientRepository->findBy([
            'User' => $this->getUser()
        ]);

        $data = array_map(fn($client) => [
            'id'    => $client->getId(),
            'name'  => $client->getName(),
            'email' => $client->getEmail(),
            // ... les champs dont tu as besoin
        ], $clients);

        return $this->json($data);
    }
}
