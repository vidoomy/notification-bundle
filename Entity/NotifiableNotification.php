<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vidoomy\NotificationBundle\Entity\Repository\NotifiableNotificationRepository;
use Vidoomy\NotificationBundle\Model\Notification as NotificationModel;

/**
 * One notification delivered to one notifiable, with its seen flag.
 */
#[ORM\Entity(repositoryClass: NotifiableNotificationRepository::class)]
class NotifiableNotification implements \JsonSerializable
{
    public const ENTITY_FIELD_ID = 'id';
    public const ENTITY_FIELD_SEEN = 'seen';
    public const ENTITY_FIELD_NOTIFICATION = 'notification';
    public const ENTITY_FIELD_NOTIFIABLE = 'notifiable';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(name: 'seen', type: 'boolean')]
    protected bool $seen = false;

    #[ORM\ManyToOne(targetEntity: Notification::class, inversedBy: 'notifiableNotifications', cascade: ['persist'])]
    protected ?NotificationModel $notification = null;

    #[ORM\ManyToOne(targetEntity: NotifiableEntity::class, inversedBy: 'notifiableNotifications', cascade: ['persist'])]
    protected ?NotifiableEntity $notifiableEntity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSeen(): bool
    {
        return $this->seen;
    }

    public function setSeen(bool $isSeen): self
    {
        $this->seen = $isSeen;

        return $this;
    }

    public function getNotification(): ?NotificationModel
    {
        return $this->notification;
    }

    public function setNotification(?NotificationModel $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getNotifiableEntity(): ?NotifiableEntity
    {
        return $this->notifiableEntity;
    }

    public function setNotifiableEntity(?NotifiableEntity $notifiableEntity = null): self
    {
        $this->notifiableEntity = $notifiableEntity;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            self::ENTITY_FIELD_ID => $this->getId(),
            self::ENTITY_FIELD_SEEN => $this->isSeen(),
            self::ENTITY_FIELD_NOTIFICATION => $this->getNotification(),
            self::ENTITY_FIELD_NOTIFIABLE => ['id' => $this->getNotifiableEntity()?->getId()],
        ];
    }
}
