<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener {
    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload['firstName'] = $event->getUser()->getFirstName();
        $payload['lastName'] = $event->getUser()->getLastName();
        $payload['fullName'] = $event->getUser()->getFirstName() . ' ' . $event->getUser()->getLastName();
        $payload['email'] = $event->getUser()->getEmail();
        $payload['roles'] = $event->getUser()->getRoles();
        
        $event->setData($payload);
    }
}