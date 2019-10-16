<?php

namespace Vidoomy\NotificationBundle;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

class NotifiableDiscovery
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $notifiables = [];


    /**
     * NotifiableDiscovery constructor.
     * @param EntityManager $em
     * @param Reader $annotationReader
     * @throws \ReflectionException
     */
    public function __construct(EntityManager $em, Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->em = $em;
        $this->discoverNotifiables();
    }

    /**
     * Returns all the workers
     * @throws \InvalidArgumentException
     */
    public function getNotifiables()
    {
        return $this->notifiables;
    }

    /**
     * @param NotifiableInterface $notifiable
     * @return string|null
     * @throws \ReflectionException
     */
    public function getNotifiableName(NotifiableInterface $notifiable): ?string
    {
        $class = $this->em->getClassMetadata(get_class($notifiable))->getName();
        $annotation = $this->annotationReader->getClassAnnotation(
            new \ReflectionClass($class),
            'Vidoomy\NotificationBundle\Annotation\Notifiable'
        );

        if ($annotation) {
            return $annotation->getName();
        }

        return null;
    }

    /**
     * @throws \ReflectionException
     */
    private function discoverNotifiables(): void
    {
        /** @var ClassMetadata[] $entities */
        $entities = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($entities as $entity) {
            $class = $entity->name;
            $annotation = $this->annotationReader->getClassAnnotation(
                new \ReflectionClass($class),
                'Vidoomy\NotificationBundle\Annotation\Notifiable'
            );
            if ($annotation) {
                $this->notifiables[$annotation->getName()] = [
                    'class' => $entity->name,
                    'annotation' => $annotation,
                    'identifiers' => $entity->getIdentifier()
                ];
            }
        }
    }
}
