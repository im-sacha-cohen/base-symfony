<?php

namespace App\Controller;

use App\Service\AbstractRestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractRestController extends AbstractController
{
    public function __construct(
        protected AbstractRestService $service
    )
    { }

    #[Route('', name: '_get', methods: ['OPTIONS', 'GET'])]
    public function findAll(): JsonResponse
    {
        $get = $this->service->findAll();

        return new JsonResponse($get, 200);
    }

    #[Route('/{secretId}', name: '_get-by', methods: ['OPTIONS', 'GET'])]
    public function findBy(string $secretId): JsonResponse
    {
        $get = $this->service->findOneBy(['secretId' => $secretId]);

        return new JsonResponse($get, 200);
    }
}
