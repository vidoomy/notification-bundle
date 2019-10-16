<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class NotifiableEntity
 * @package Vidoomy\NotificationBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Vidoomy\NotificationBundle\Entity\Repository\NotifiableRepository")
 * @UniqueEntity(fields={"identifier", "class"})
 */
class NotifiableEntity implements \JsonSerializable
{
    const ENTITY_FIELD_ID = "id";
    const ENTITY_FIELD_IDENTIFIER = "identifier";
    const ENTITY_FIELD_CLASS = "class";

    /**
     * @var string $id
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string $identifier
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $class;

    /**
     * @var NotifiableNotification[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Vidoomy\NotificationBundle\Entity\NotifiableNotification", mappedBy="notifiableEntity",cascade={"persist"})
     */
    protected $notifiableNotifications;

    /**
     * AbstractNotifiableEntity constructor.
     *
     * @param $identifier
     * @param $class
     */
    public function __construct($identifier, $class)
    {
        $this->identifier = $identifier;
        $this->class = $class;
        $this->notifiableNotifications = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return NotifiableEntity
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return NotifiableEntity
     */
    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return ArrayCollection|NotifiableNotification[]
     */
    public function getNotifiableNotifications()
    {
        return $this->notifiableNotifications;
    }

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return $this
     */
    public function addNotifiableNotification(NotifiableNotification $notifiableNotification): self
    {
        if (!$this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications[] = $notifiableNotification;
            $notifiableNotification->setNotifiableEntity($this);
        }

        return $this;
    }

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return $this
     */
    public function removeNotifiableNotification(NotifiableNotification $notifiableNotification): self
    {
        if ($this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications->removeElement($notifiableNotification);
            $notifiableNotification->setNotifiableEntity(null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            self::ENTITY_FIELD_ID         => $this->getId(),
            self::ENTITY_FIELD_IDENTIFIER => $this->getIdentifier(),
            self::ENTITY_FIELD_CLASS      => $this->getClass()
        ];
    }
}
