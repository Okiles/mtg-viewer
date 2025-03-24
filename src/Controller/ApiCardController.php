<?php

namespace App\Controller;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/all', name: 'list_all_cards', methods: ['GET'])]
    #[OA\Get(description: 'Return all cards in the database')]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(): Response
    {
        $this->logger->info('Request received: Fetching all cards');

        $cards = $this->entityManager->getRepository(Card::class)->findAll();

        if (!$cards) {
            $this->logger->warning('No cards found in the database');
            return $this->json(['message' => 'No cards found'], 404);
        }

        $this->logger->info('Successfully retrieved ' . count($cards) . ' cards');
        return $this->json($cards);
    }

    #[Route('/{uuid}', name: 'show_card', methods: ['GET'])]
    #[OA\Parameter(
        name: 'uuid',
        description: 'UUID of the card',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Get(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $this->logger->info("Request received: Fetching card with UUID: {$uuid}");

        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);

        if (!$card) {
            $this->logger->error("Card not found with UUID: {$uuid}");
            return $this->json(['error' => 'Card not found'], 404);
        }

        $this->logger->info("Successfully retrieved card with UUID: {$uuid}");
        return $this->json($card);
    }
}
