<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vidoomy\NotificationBundle\Model\Notification as NotificationModel;

/**
 * Class NotifiableNotification
 * @package Vidoomy\NotificationBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Vidoomy\NotificationBundle\Entity\Repository\NotifiableNotificationRepository")
 *
 */
class NotifiableNotification implements \JsonSerializable
{
    const ENTITY_FIELD_ID = "id";
    const ENTITY_FIELD_SEEN = "seen";
    const ENTITY_FIELD_NOTIFICATION = "notification";
    const ENTITY_FIELD_NOTIFIABLE = "notifiable";

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var boolean
     * @ORM\Column(name="seen", type="boolean")
     */
    protected $seen;

    /**
     * @var Notification
     * @ORM\ManyToOne(targetEntity="Vidoomy\NotificationBundle\Entity\Notification", inversedBy="notifiableNotifications", cascade={"persist"})
     */
    protected $notification;

    /**
     * @var NotifiableEntity
     * @ORM\ManyToOne(targetEntity="Vidoomy\NotificationBundle\Entity\NotifiableEntity", inversedBy="notifiableNotifications", cascade={"persist"})
     *
     */
    protected $notifiableEntity;

    /**
     * AbstractNotification constructor.
     */
    public function __construct()
    {
        $this->seen = false;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isSeen(): bool
    {
        return $this->seen;
    }

    /**
     * @param boolean $isSeen
     * @return $this
     */
    public function setSeen(bool $isSeen): self
    {
        $this->seen = $isSeen;

        return $this;
    }

    /**
     * @return NotificationModel
     */
    public function getNotification(): NotificationModel
    {
        return $this->notification;
    }

    /**
     * @param NotificationModel $notification
     *
     * @return NotifiableNotification
     */
    public function setNotification(NotificationModel $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return NotifiableEntity
     */
    public function getNotifiableEntity(): NotifiableEntity
    {
        return $this->notifiableEntity;
    }

    /**
     * @param NotifiableEntity $notifiableEntity
     *
     * @return NotifiableNotification
     */
    public function setNotifiableEntity(NotifiableEntity $notifiableEntity = null): self
    {
        $this->notifiableEntity = $notifiableEntity;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            self::ENTITY_FIELD_ID           => $this->getId(),
            self::ENTITY_FIELD_SEEN         => $this->isSeen(),
            self::ENTITY_FIELD_NOTIFICATION => $this->getNotification(),
            self::ENTITY_FIELD_NOTIFIABLE   => [ 'id' => $this->getNotifiableEntity()->getId() ]
        ];
    }
}
