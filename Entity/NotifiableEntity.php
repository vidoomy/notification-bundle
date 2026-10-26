<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vidoomy\NotificationBundle\Entity\Repository\NotifiableRepository;

/**
 * Row that links a notifiable application entity (by class and identifier) to its notifications.
 */
#[ORM\Entity(repositoryClass: NotifiableRepository::class)]
#[UniqueEntity(fields: ['identifier', 'class'])]
class NotifiableEntity implements \JsonSerializable
{
    public const ENTITY_FIELD_ID = 'id';
    public const ENTITY_FIELD_IDENTIFIER = 'identifier';
    public const ENTITY_FIELD_CLASS = 'class';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $identifier;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $class;

    /**
     * @var Collection<int, NotifiableNotification>
     */
    #[ORM\OneToMany(targetEntity: NotifiableNotification::class, mappedBy: 'notifiableEntity', cascade: ['persist'])]
    protected Collection $notifiableNotifications;

    public function __construct(string $identifier, string $class)
    {
        $this->identifier = $identifier;
        $this->class = $class;
        $this->notifiableNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return Collection<int, NotifiableNotification>
     */
    public function getNotifiableNotifications(): Collection
    {
        return $this->notifiableNotifications;
    }

    public function addNotifiableNotification(NotifiableNotification $notifiableNotification): self
    {
        if (!$this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications[] = $notifiableNotification;
            $notifiableNotification->setNotifiableEntity($this);
        }

        return $this;
    }

    public function removeNotifiableNotification(NotifiableNotification $notifiableNotification): self
    {
        if ($this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications->removeElement($notifiableNotification);
            $notifiableNotification->setNotifiableEntity(null);
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            self::ENTITY_FIELD_ID => $this->getId(),
            self::ENTITY_FIELD_IDENTIFIER => $this->getIdentifier(),
            self::ENTITY_FIELD_CLASS => $this->getClass(),
        ];
    }
}
