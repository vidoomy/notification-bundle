<?php

namespace Vidoomy\NotificationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\NotifiableInterface;

abstract class AbstractNotificationEvent extends Event
{
    public function __construct(
        private NotificationInterface $notification,
        private ?NotifiableInterface $notifiable = null
    ) {
    }

    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }

    public function getNotifiable(): ?NotifiableInterface
    {
        return $this->notifiable;
    }
}
