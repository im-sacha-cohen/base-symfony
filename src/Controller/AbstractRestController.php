<?php

namespace App\Controller;

use App\Service\AbstractRestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        return new JsonResponse($get, $get['status']);
    }

    #[Route('/{secretId}', name: '_get-by', methods: ['OPTIONS', 'GET'])]
    public function findBy(string $secretId): JsonResponse
    {
        $get = $this->service->findOneBy(['secretId' => $secretId]);

        return new JsonResponse($get, $get['status']);
    }

    #[Route('', name: '_new', methods: ['OPTIONS', 'POST'])]
    public function new(Request $request): JsonResponse
    {
        $post = $this->service->new($request);

        return new JsonResponse($post, $post['status']);
    }

    #[Route('/{secretId}', name: '_update', methods: ['OPTIONS', 'PUT'])]
    public function update(string $secretId, Request $request): JsonResponse
    {
        $put = $this->service->put($request, ['secretId' => $secretId]);

        return new JsonResponse($put, $put['status']);
    }

    #[Route('/{secretId}', name: '_delete', methods: ['OPTIONS', 'DELETE'])]
    public function delete(string $secretId): JsonResponse
    {
        $delete = $this->service->remove(['secretId' => $secretId]);

        return new JsonResponse($delete, $delete['status']);
    }
}
