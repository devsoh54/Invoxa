<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_USER')]
#[Route("/api/clients")]
final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    #[Route('/list', name: 'api_clients', methods: ['GET'])]
    public function list(ClientRepository $clientRepository): JsonResponse
    {
        $clients = $clientRepository->findBy([
            'User' => $this->getUser()
        ]);

        $data = array_map(fn($client) => [
            'id'    => $client->getId(),
            'name'  => $client->getName(),
            'email' => $client->getEmail(),
            'phone' => $client->getPhone(),
            'company' => $client->getCompany()
        ], $clients);

        return $this->json($data);
    }

    #[Route('/create', name: 'api_clients_create', methods: ['POST'])]
    #[Route('/edit/{id}', name: 'api_clients_edit', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, ?Client $client): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = $client ?? new Client();
        $client->setName($data['nom']);
        $client->setEmail($data['email']);
        $client->setPhone($data['telephone'] ?? null);
        $client->setCompany($data['entreprise'] ?? null);
        $client->setUser($this->getUser());

        $em->persist($client);
        $em->flush();

        return $this->json(['id' => $client->getId()], 201);
    }

    #[Route('/delete/{id}', name: 'api_clients_delete', methods: ['GET'])]
    public function delete(Request $request, EntityManagerInterface $em, ?Client $client): JsonResponse
    {

        $em->remove($client);
        $em->flush();

        return $this->json(['id' => $client->getId()], 201);
    }

}
