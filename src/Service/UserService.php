<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class UserService extends AbstractRestService {
    public function __construct(
        EntityManagerInterface $emi,
        UserRepository $repo,
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($emi, $repo, $normalizer, $denormalizer, $validator);
    }
}