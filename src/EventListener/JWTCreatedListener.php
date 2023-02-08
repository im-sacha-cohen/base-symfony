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
        //$payload['firstName'] = $event->getUser()->getFirstName();
        //$payload['fullName'] = $event->getUser()->getFirstName() . ' ' . $event->getUser()->getLastName();
        //$payload['username'] = $event->getUser()->getUserIdentifier();
        
        $event->setData($payload);
    }
}