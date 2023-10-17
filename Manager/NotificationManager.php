<?php

namespace Vidoomy\NotificationBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Vidoomy\NotificationBundle\Entity\NotifiableEntity;
use Vidoomy\NotificationBundle\Entity\NotifiableNotification;
use Vidoomy\NotificationBundle\Entity\Notification;
use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\Entity\Repository\NotifiableNotificationRepository;
use Vidoomy\NotificationBundle\Entity\Repository\NotifiableRepository;
use Vidoomy\NotificationBundle\Entity\Repository\NotificationRepository;
use Vidoomy\NotificationBundle\Event\AbstractNotificationEvent;
use Vidoomy\NotificationBundle\Event\AssignedNotificationEvent;
use Vidoomy\NotificationBundle\Event\CreatedNotificationEvent;
use Vidoomy\NotificationBundle\Event\DeletedNotificationEvent;
use Vidoomy\NotificationBundle\Event\ModifiedNotificationEvent;
use Vidoomy\NotificationBundle\Event\RemovedNotificationEvent;
use Vidoomy\NotificationBundle\Event\SeenNotificationEvent;
use Vidoomy\NotificationBundle\Event\UnseenNotificationEvent;
use Vidoomy\NotificationBundle\VidoomyNotificationEvents;
use Vidoomy\NotificationBundle\NotifiableDiscovery;
use Vidoomy\NotificationBundle\NotifiableInterface;
use Vidoomy\NotificationBundle\Model\Notification as NotificationModel;

/**
 * Class NotificationManager
 * Manager for notifications
 * @package Vidoomy\NotificationBundle\Manager
 */
class NotificationManager
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var NotifiableDiscovery $discovery
     */
    protected $discovery;

    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @var EventDispatcherInterface $dispatcher
     */
    protected $dispatcher;

    /**
     * @var NotifiableRepository $notifiableRepository
     */
    protected $notifiableRepository;

    /**
     * @var NotificationRepository $notificationRepository
     */
    protected $notificationRepository;

    /**
     * @var NotifiableNotificationRepository $notifiableNotificationRepository
     */
    protected $notifiableNotificationRepository;

    /**
     * NotificationManager constructor.
     * @param ContainerInterface $container
     * @param NotifiableDiscovery $discovery
     */
    public function __construct(ContainerInterface $container, NotifiableDiscovery $discovery)
    {
        $this->container = $container;
        $this->discovery = $discovery;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->dispatcher = $container->get('event_dispatcher');
        $this->notifiableRepository = $this->em->getRepository(NotifiableEntity::class);
        $this->notificationRepository = $this->em->getRepository(Notification::class);
        $this->notifiableNotificationRepository = $this->em->getRepository(NotifiableNotification::class);
    }

    /**
     * Returns a list of available workers.
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getDiscoveryNotifiables()
    {
        return $this->discovery->getNotifiables();
    }

    /**
     * Returns one notifiable by name
     *
     * @param string $name
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getNotifiable(string $name): array
    {
        $notifiables = $this->getDiscoveryNotifiables();
        if (isset($notifiables[$name])) {
            return $notifiables[$name];
        }

        throw new \RuntimeException('Notifiable not found.');
    }

    /**
     * Get the name of the notifiable
     *
     * @param NotifiableInterface $notifiable
     * @return string|null
     * @throws \ReflectionException
     */
    public function getNotifiableName(NotifiableInterface $notifiable): ?string
    {
        return $this->discovery->getNotifiableName($notifiable);
    }

    /**
     * @param NotifiableInterface $notifiable
     * @return array
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getNotifiableIdentifier(NotifiableInterface $notifiable): array
    {
        $name = $this->getNotifiableName($notifiable);

        return $this->getNotifiable($name)['identifiers'];
    }

    /**
     * Get the identifier mapping for a NotifiableEntity
     *
     * @param NotifiableEntity $notifiableEntity
     *
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getNotifiableEntityIdentifiers(NotifiableEntity $notifiableEntity): array
    {
        $discoveryNotifiables = $this->getDiscoveryNotifiables();
        foreach ($discoveryNotifiables as $notifiable) {
            if ($notifiable['class'] === $notifiableEntity->getClass()) {
                return $notifiable['identifiers'];
            }
        }
        throw new \RuntimeException('Unable to get the NotifiableEntity identifiers. This could be an Entity mapping issue');
    }

    /**
     * Generates the identifier value to store a NotifiableEntity
     *
     * @param NotifiableInterface $notifiable
     * @return string
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generateIdentifier(NotifiableInterface $notifiable): string
    {
        $identifiers = $this->getNotifiableIdentifier($notifiable);
        $identifierValues = array();
        foreach ($identifiers as $identifier) {
            $method = sprintf('get%s', ucfirst($identifier));
            $identifierValues[] = $notifiable->$method();
        }

        return implode('-', $identifierValues);
    }

    /**
     * @param NotifiableInterface $notifiable
     * @return NotifiableEntity
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getNotifiableEntity(NotifiableInterface $notifiable): NotifiableEntity
    {
        $identifier = $this->generateIdentifier($notifiable);
        $class = $this->em->getClassMetadata(get_class($notifiable))->getName();
        $entity = $this->notifiableRepository->findOneBy(array(
            'identifier' => $identifier,
            'class' => $class
        ));

        if (!$entity) {
            $entity = new NotifiableEntity($identifier, $class);
            $this->em->persist($entity);
            $this->em->flush();
        }

        return $entity;
    }

    /**
     * @param NotifiableEntity $notifiableEntity
     * @return NotifiableInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getNotifiableInterface(NotifiableEntity $notifiableEntity): NotifiableInterface
    {
        return $this->notifiableRepository->findNotifiableInterface(
            $notifiableEntity,
            $this->getNotifiableEntityIdentifiers($notifiableEntity)
        );
    }

    /**
     * @param int $id
     *
     * @return NotifiableEntity|null
     */
    public function getNotifiableEntityById(int $id): NotifiableEntity
    {
        return $this->notifiableRepository->findOneById($id);
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param NotificationInterface $notification
     * @return NotifiableNotification
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    private function getNotifiableNotification(NotifiableInterface $notifiable, NotificationInterface $notification): NotifiableNotification
    {
        return $this->notifiableNotificationRepository->findOne(
            $notification->getId(),
            $this->getNotifiableEntity($notifiable)
        );
    }

    /**
     * Avoid code duplication
     *
     * @param $flush
     */
    private function flush($flush)
    {
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Get all notifications
     *
     * @param string $order
     *
     * @return Notification[]
     */
    public function getAll($order = 'DESC'): array
    {
        return $this->notificationRepository
            ->createQueryBuilder('n')
            ->orderBy('n.id', $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param string $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     * @throws \ReflectionException
     */
    public function getNotifications(
        NotifiableInterface $notifiable,
        string $order = 'DESC',
        int $limit = null,
        int $offset = null
    ) {
        return $this->notifiableNotificationRepository->findAllByNotifiable(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName(),
            null,
            $order,
            $limit,
            $offset
        );
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \ReflectionException
     */
    public function getUnseenNotifications(
        NotifiableInterface $notifiable,
        string $order = 'DESC',
        int $limit = null,
        int $offset = null
    ) {
        return $this->notifiableNotificationRepository->findAllByNotifiable(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName(),
            false,
            $order,
            $limit,
            $offset
        );
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param string $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     * @throws \ReflectionException
     */
    public function getSeenNotifications(
        NotifiableInterface $notifiable,
        string $order = 'DESC',
        int $limit = null,
        int $offset = null
    ) {
        return $this->notifiableNotificationRepository->findAllByNotifiable(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName(),
            true,
            $order,
            $limit,
            $offset
        );
    }


    /**
     * Get one notification by id
     *
     * @param $id
     *
     * @return Notification
     */
    public function getNotification(int $id): Notification
    {
        return $this->notificationRepository->findOneById($id);
    }

    /**
     * @param string $subject
     * @param string|null $excerpt
     * @param string|null $message
     * @param string|null $link
     * @param int $priority
     * @param array $tags
     * @return Notification
     */
    public function createNotification(
        string $subject,
        string $excerpt = null,
        string $message = null,
        string $link = null,
        int $priority = NotificationModel::NOTIFICATION_PRIORITY_LOW,
        array $tags = []
    ): Notification {
        $notification = new Notification();
        $notification
            ->setSubject($subject)
            ->setExcerpt($excerpt)
            ->setMessage($message)
            ->setLink($link)
            ->setPriority($priority)
            ->setTags($tags);

        $event = new CreatedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * Add a Notification to a list of NotifiableInterface entities
     *
     * @param array $notifiables
     * @param NotificationInterface $notification
     * @param bool $flush
     * @throws \ReflectionException
     */
    public function addNotification(array $notifiables, NotificationInterface $notification, bool $flush = false): void
    {
        foreach ($notifiables as $notifiable) {
            $entity = $this->getNotifiableEntity($notifiable);

            $notifiableNotification = new NotifiableNotification();
            $entity->addNotifiableNotification($notifiableNotification);
            $notification->addNotifiableNotification($notifiableNotification);

            $event = new AssignedNotificationEvent($notification, $notifiable);
            $this->dispatcher->dispatch($event);
        }

        $this->flush($flush);
    }

    /**
     * Deletes the link between a Notifiable and a Notification
     *
     * @param array $notifiables
     * @param NotificationInterface $notification
     * @param bool $flush
     * @throws \ReflectionException
     */
    public function removeNotification(array $notifiables, NotificationInterface $notification, bool $flush = false)
    {
        $repo = $this->em->getRepository(NotifiableNotification::class);
        foreach ($notifiables as $notifiable) {
            $repo->createQueryBuilder('nn')
                ->delete()
                ->where('nn.notifiableEntity = :entity')
                ->andWhere('nn.notification = :notification')
                ->setParameter('entity', $this->getNotifiableEntity($notifiable))
                ->setParameter('notification', $notification)
                ->getQuery()
                ->execute();

            $event = new RemovedNotificationEvent($notification, $notifiable);
            $this->dispatcher->dispatch($event);
        }

        $this->flush($flush);
    }

    /**
     * @param NotificationInterface $notification
     * @param bool $flush
     */
    public function deleteNotification(NotificationInterface $notification, bool $flush = false)
    {
        $this->em->remove($notification);
        $this->flush($flush);

        $event = new DeletedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param NotificationInterface $notification
     * @param bool $flush
     * @throws EntityNotFoundException
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function markAsSeen(
        NotifiableInterface $notifiable,
        NotificationInterface $notification,
        bool $flush = false
    ) {
        $nn = $this->getNotifiableNotification($notifiable, $notification);
        if ($nn) {
            $nn->setSeen(true);
            $event = new SeenNotificationEvent($notification, $notifiable);
            $this->dispatcher->dispatch($event);
            $this->flush($flush);
        } else {
            throw new EntityNotFoundException(
                'The link between the notifiable and the notification has not been found'
            );
        }
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param NotificationInterface $notification
     * @param bool $flush
     * @throws EntityNotFoundException
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function markAsUnseen(
        NotifiableInterface $notifiable,
        NotificationInterface $notification,
        bool $flush = false
    ) {
        $nn = $this->getNotifiableNotification($notifiable, $notification);

        if ($nn) {
            $nn->setSeen(false);
            $event = new UnseenNotificationEvent($notification, $notifiable);
            $this->dispatcher->dispatch($event);
            $this->flush($flush);
        } else {
            throw new EntityNotFoundException(
                'The link between the notifiable and the notification has not been found'
            );
        }
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param bool $flush
     * @throws \ReflectionException
     */
    public function markAllAsSeen(NotifiableInterface $notifiable, bool $flush = false)
    {
        $nns = $this->notifiableNotificationRepository->findAllForNotifiable(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName()
        );
        foreach ($nns as $nn) {
            $nn->setSeen(true);
            $event = new SeenNotificationEvent($nn->getNotification(), $notifiable);
            $this->dispatcher->dispatch($event);
        }

        $this->flush($flush);
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param NotificationInterface $notification
     * @return bool
     * @throws EntityNotFoundException
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function isSeen(NotifiableInterface $notifiable, NotificationInterface $notification): bool
    {
        $nn = $this->getNotifiableNotification($notifiable, $notification);
        if ($nn) {
            return $nn->isSeen();
        }

        throw new EntityNotFoundException(
            'The link between the notifiable and the notification has not been found'
        );
    }

    /**
     * @param NotifiableInterface $notifiable
     * @return int
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws \ReflectionException
     */
    public function getNotificationCount(NotifiableInterface $notifiable): int
    {
        return $this->notifiableNotificationRepository->getNotificationCount(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName()
        );
    }

    /**
     * @param NotifiableInterface $notifiable
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function getUnseenNotificationCount(NotifiableInterface $notifiable): int
    {
        return $this->notifiableNotificationRepository->getNotificationCount(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName(),
            false
        );
    }

    /**
     * @param NotifiableInterface $notifiable
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws \ReflectionException
     */
    public function getSeenNotificationCount(NotifiableInterface $notifiable): int
    {
        return $this->notifiableNotificationRepository->getNotificationCount(
            $this->generateIdentifier($notifiable),
            $this->em->getClassMetadata(get_class($notifiable))->getName(),
            true
        );
    }

    /**
     * @param NotificationInterface $notification
     * @param \DateTimeInterface $dateTime
     * @param bool $flush
     * @return NotificationInterface
     */
    public function setDate(
        NotificationInterface $notification,
        \DateTimeInterface $dateTime,
        bool $flush = false
    ): NotificationInterface {
        $notification->setDate($dateTime);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     * @param string $subject
     * @param bool $flush
     * @return NotificationInterface
     */
    public function setSubject(
        NotificationInterface $notification,
        string $subject,
        bool $flush = false
    ): NotificationInterface {
        $notification->setSubject($subject);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     * @param string $excerpt
     * @param bool $flush
     * @return NotificationInterface
     */
    public function setExcerpt(
        NotificationInterface $notification,
        string $excerpt,
        bool $flush = false
    ): NotificationInterface {
        $notification->setExcerpt($excerpt);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     * @param string $message
     * @param bool $flush
     * @return NotificationInterface
     */
    public function setMessage(
        NotificationInterface $notification,
        string $message,
        bool $flush = false
    ): NotificationInterface {
        $notification->setMessage($message);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     * @param string       $link
     * @param bool         $flush
     *
     * @return NotificationInterface
     */
    public function setLink(
        NotificationInterface $notification,
        string $link,
        bool $flush = false
    ): NotificationInterface {
        $notification->setLink($link);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     * @param int       $priority
     * @param bool         $flush
     *
     * @return NotificationInterface
     */
    public function setPriority(
        NotificationInterface $notification,
        int $priority,
        bool $flush = false
    ): NotificationInterface {
        $notification->setPriority($priority);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     * @param array       $tags
     * @param bool         $flush
     *
     * @return NotificationInterface
     */
    public function setTags(
        NotificationInterface $notification,
        array $tags,
        bool $flush = false
    ): NotificationInterface {
        $notification->setTags($tags);
        $this->flush($flush);

        $event = new ModifiedNotificationEvent($notification);
        $this->dispatcher->dispatch($event);

        return $notification;
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return NotifiableInterface[]
     */
    public function getNotifiables(NotificationInterface $notification): array
    {
        return $this->notifiableRepository->findAllByNotification($notification);
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return NotifiableInterface[]
     */
    public function getUnseenNotifiables(NotificationInterface $notification): array
    {
        return $this->notifiableRepository->findAllByNotification($notification, true);
    }

    /**
     * @param NotificationInterface $notification
     *
     * @return NotifiableInterface[]
     */
    public function getSeenNotifiables(NotificationInterface $notification): array
    {
        return $this->notifiableRepository->findAllByNotification($notification, false);
    }
}
