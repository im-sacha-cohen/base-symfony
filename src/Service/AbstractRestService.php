<?php

namespace App\Service;

use Error;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractRestService {
    public function __construct(
        protected EntityManagerInterface $emi,
        protected ServiceEntityRepository $repo,
        protected NormalizerInterface $normalizer,
        protected DenormalizerInterface $denormalizer,
        protected ValidatorInterface $validator
    )
    { }

    /**
     * @param array $data
     * 
     * @return mixed
     */
    public function denormalize(array $data): mixed {
        return $this->denormalizer->denormalize($data, $this->repo->getClassName(), 'json');
    }

    /**
     * @return array
     */
    public function findAll(): array {
        $ret = [];
        $objects = $this->repo->findAll();

        foreach($objects as $object) {
            if (method_exists($object, 'jsonSerialize')) {
                $ret[] = $object->jsonSerialize();
            } else {
                $ret[] = $this->normalizer->normalize($object, 'json');
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null): array {
        $objects = $this->repo->findBy($criteria, $orderBy, $limit);

        foreach ($objects as $object) {
            $ret[] = $this->serialize($object);

            return array(
                'status' => 200,
                'object' => $ret
            );
        }

        return array(
            'status' => 404,
            'message' => 'Page not found'
        );
    }

    /**
     * @param object $object
     * 
     * @return array
     */
    public function serialize(object $object): array {
        $ret = [];

        if (method_exists($object, 'jsonSerialize')) {
            $ret[] = $object->jsonSerialize();
        } else {
            $ret[] = $this->normalizer->normalize($object, 'json');
        }

        return $ret;
    }

    /**
     * @return mixed
     */
    public function findOneBy(array $criteria, ?array $orderBy = null, ?bool $returnSerialized = false): mixed {
        $object = $this->repo->findOneBy($criteria, $orderBy);

        if ($object !== null) {
            if ($returnSerialized) {
                $ret[] = $this->serialize($object);
            } else {
                return $object;
            }

            return array(
                'status' => 200,
                'object' => $ret
            );
        } else {
            if ($returnSerialized) {
                return array(
                    'status' => 404,
                    'message' => 'Page not found'
                );
            }

            return null;
        }
    }

    /**
     * @param Request $request
     * 
     * @return object
     */
    public function new(Request $request): object {
        $data = $this->getDataFromRequest($request);
        $object = $this->denormalize($data);

        $errors = $this->validator->validate($object);
        
        if (count($errors) > 0) {
            $tmp = [];

            foreach($errors as $error) {
                $tmp[] = $error->getMessage();
            }

            throw new Error(implode(", ", $tmp));
        }

        $this->create($object);

        return $object;
    }

    /**
     * @param mixed $object
     * 
     * @return void
     */
    public function create($object): void {
        $this->emi->persist($object);
        $this->emi->flush();
    }

    /**
     * @param Request $request
     * @param string $secretId
     * 
     * @return object
     */
    public function put(Request $request, string $secretId): object {
        $data = $this->getDataFromRequest($request);
        $dataSerialized = $this->denormalize($data);
        $object = $this->findOneBy(['secretId' => $secretId]);
        
        $errors = $this->validator->validate($dataSerialized);
        
        if (count($errors) > 0) {
            $tmp = [];
            
            foreach($errors as $error) {
                // Only add error in array if the key is in the payload. Instead, we consider the value fully constrained by new()
                if (isset($data[$error->getPropertyPath()])) {
                    $tmp[] = $error->getMessage();
                }
            }

            if (count($tmp) > 0) {
                throw new Error(implode(", ", $tmp));
            }
        }

        $this->update($object, $data);

        return $object;
    }

    /**
     * @param object $object
     * @param array $data
     * 
     * @return mixed array if has errors, null if no errors
     */
    public function update(object $object, array $data): mixed {
        foreach($data as $key => $value) {
            $keyProperty = ucfirst($key);
            $method = 'set' . $keyProperty;

            if (method_exists($object, $method)) {
                $object->${"method"}($value);
            } else {
                return array(
                    'status' => 400,
                    'message' => 'Cannot update property ' . $key
                );
            }
        }

        $this->emi->persist($object);
        $this->emi->flush();

        return null;
    }

    /**
     * @param string $secretId
     * 
     * @return array
     */
    public function remove(string $secretId): array {
        $object = $this->findOneBy(['secretId' => $secretId]);
        
        if ($object !== null) {
            $this->delete($object);

            return array(
                'status' => 200
            );
        }

        return array(
            'status' => 400,
            'message' => 'This object does not exist.'
        );
    }

    /**
     * @param mixed $object
     * 
     * @return void
     */
    public function delete($object): void {
        $this->emi->remove($object);
        $this->emi->flush();
    }

    /**
     * @param array $mandatoryFields
     * @param array $data
     * 
     * @return bool|array - false if all mandatory fields are present, array of missing fields otherwise to implode
     */
    public function isMandatoryFieldMissing(array $mandatoryFields, array $data): bool|array {
        $fieldsMissing = [];
        foreach ($mandatoryFields as $field) {
            if (!isset($data[$field])) {
                $fieldsMissing[] = $field;
            }
        }

        return count($fieldsMissing) > 0 ? $fieldsMissing : false;
    }

    public function getDataFromRequest(Request $request): array {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        }

        return $data;
    }

    public function generateSecretId(): string {
        return hash('adler32', uniqid() . '-' . uniqid());
    }
}