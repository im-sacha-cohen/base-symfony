<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class UserController extends AbstractRestController
{
    public function __construct(
        UserService $service
    )
    {
        parent::__construct($service);
    }
}
