<?php

namespace App\Service;

use App\Event\ActivityEvent;
use App\Message\UserActivityLoggedMessage;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActivityEventDispatcher 
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private MessageBusInterface      $bus,
        private Security                 $security
    )
    {
    }

    /**
     * @param mixed $ressource
     * @param string $action
     * @param string|null $ressourceClass
     * @return ActivityEvent
     */
    public function dispatch(mixed $ressource, string $action, ?string $ressourceClass = null): ActivityEvent
    {
        $eventName = ActivityEvent::getEventName(null === $ressource ? $ressourceClass : get_class($ressource), $action);

        if ($this->security->getUser())
            $this->bus->dispatch(new UserActivityLoggedMessage( 
                $this->security->getUser()->getUserIdentifier(),
                new \DateTimeImmutable(),
                $action,
                null === $ressource ? $ressourceClass : get_class($ressource),
                $ressource?->getId()
            ));

        return $this->dispatcher->dispatch(new ActivityEvent($ressource, $action, $ressourceClass), $eventName);
    }
}
