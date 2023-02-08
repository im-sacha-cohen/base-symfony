<?php

namespace App\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;

class GlobalProperyListener
{
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        
        if (method_exists($entity, 'setSecretId')) {
            if ($entity->getSecretId() === null) {
                $secretId = strtoupper(hash('adler32', uniqid('', true)));
                $entity->setSecretId($secretId);
            }
        }
    }
}