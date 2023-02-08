<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class AbstractRestService {
    public function __construct(
        protected EntityManagerInterface $emi,
        protected ServiceEntityRepository $repo,
        protected NormalizerInterface $normalizer,
        protected DenormalizerInterface $denormalizer
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
            if (method_exists($object, 'jsonSerialize')) {
                $ret[] = $object->jsonSerialize();
            } else {
                $ret[] = $this->normalizer->normalize($object, 'json');
            }

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
     * @return mixed
     */
    public function findOneBy(array $criteria, ?array $orderBy = null, ?bool $returnSerialized = false): mixed {
        $object = $this->repo->findOneBy($criteria, $orderBy);

        if ($object !== null) {
            if ($returnSerialized) {
                if (method_exists($object, 'jsonSerialize')) {
                    $ret[] = $object->jsonSerialize();
                } else {
                    $ret[] = $this->normalizer->normalize($object, 'json');
                }
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
     * @param mixed $object
     * 
     * @return void
     */
    public function create($object): void {
        $this->emi->persist($object);
        $this->emi->flush();
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