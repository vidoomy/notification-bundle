<?php

namespace Vidoomy\NotificationBundle\Event;

use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\NotifiableInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractNotificationEvent extends Event
{
    private $notification;
    private $notifiable;

    /**
     * NotificationEvent constructor.
     *
     * @param NotificationInterface    $notification
     * @param NotifiableInterface|null $notifiable
     */
    public function __construct(NotificationInterface $notification, NotifiableInterface $notifiable = null)
    {
        $this->notification = $notification;
        $this->notifiable = $notifiable;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }

    /**
     * @return NotifiableInterface
     */
    public function getNotifiable(): NotifiableInterface
    {
        return $this->notifiable;
    }
}
