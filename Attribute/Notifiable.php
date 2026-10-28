<?php

namespace Vidoomy\NotificationBundle\Attribute;

/**
 * Marks an entity as able to receive notifications.
 *
 * The entity must also implement {@see \Vidoomy\NotificationBundle\NotifiableInterface}.
 * The name identifies the notifiable in the discovery registry.
 *
 *     #[Notifiable(name: 'user')]
 *     class User implements NotifiableInterface { ... }
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Notifiable
{
    public function __construct(public string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
