<?php

namespace Vidoomy\NotificationBundle;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Vidoomy\NotificationBundle\Attribute\Notifiable;

/**
 * Finds the mapped entities carrying the #[Notifiable] attribute.
 *
 * Discovery is lazy: the full metadata of the entity manager is only loaded the first time
 * a notifiable is asked for, not when the service is built.
 */
class NotifiableDiscovery
{
    /**
     * @var array<string, array{class: string, annotation: Notifiable, identifiers: string[]}>|null
     */
    private ?array $notifiables = null;

    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return array<string, array{class: string, annotation: Notifiable, identifiers: string[]}>
     */
    public function getNotifiables(): array
    {
        if (null === $this->notifiables) {
            $this->discoverNotifiables();
        }

        return $this->notifiables;
    }

    public function getNotifiableName(NotifiableInterface $notifiable): ?string
    {
        $class = $this->em->getClassMetadata(get_class($notifiable))->getName();

        return $this->readAttribute($class)?->getName();
    }

    private function discoverNotifiables(): void
    {
        $this->notifiables = [];

        /** @var ClassMetadata[] $entities */
        $entities = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($entities as $entity) {
            $attribute = $this->readAttribute($entity->getName());
            if (null !== $attribute) {
                $this->notifiables[$attribute->getName()] = [
                    'class' => $entity->getName(),
                    'annotation' => $attribute,
                    'identifiers' => $entity->getIdentifier(),
                ];
            }
        }
    }

    /**
     * @param class-string $class
     */
    private function readAttribute(string $class): ?Notifiable
    {
        $attributes = (new \ReflectionClass($class))->getAttributes(Notifiable::class, \ReflectionAttribute::IS_INSTANCEOF);

        return [] === $attributes ? null : $attributes[0]->newInstance();
    }
}
