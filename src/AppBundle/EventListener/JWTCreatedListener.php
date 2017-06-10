<?php

namespace AppBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\UserBundle\Doctrine\UserManager;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     *
     */
    private $userManager;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, UserManager $userManager)
    {
        $this->requestStack = $requestStack;
        $this->userManager = $userManager;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $user = $this->userManager->findUserByUsername($request->get("_username"));

        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['email'] = $user->getEmail();
        $payload['lastLogin'] = $user->getLastLogin();
        $payload['roles'] = $user->getRoles();

        $event->setData($payload);
    }
}