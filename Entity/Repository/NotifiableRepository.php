<?php

namespace Vidoomy\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Vidoomy\NotificationBundle\Entity\NotifiableEntity;
use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\NotifiableInterface;

class NotifiableRepository extends EntityRepository
{

    /**
     * @param NotifiableEntity $notifiableEntity
     * @param array            $mapping
     *
     * @return NotifiableInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNotifiableInterface(NotifiableEntity $notifiableEntity, array $mapping): ?NotifiableInterface
    {
        // create the querybuilder from the entity
        $qb = $this->createQueryBuilder('n')->select('e')->from($notifiableEntity->getClass(), 'e');

        // map the identifier(s) to the value(s)
        $identifiers = explode('-', $notifiableEntity->getIdentifier());
        foreach ($mapping as $key => $identifier) {
            $qb->andWhere(sprintf('e.%s = :%s', $identifier, $identifier));
            $qb->setParameter($identifier, $identifiers[$key]);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param NotificationInterface $notification
     * @param bool|null $seen
     * @return array
     */
    public function findAllByNotification(NotificationInterface $notification, bool $seen = null): array
    {
        $qb = $this
            ->createQueryBuilder('notifiable')
            ->join('notifiable.notifiableNotifications', 'nn')
            ->join('nn.notification', 'notification')
            ->where('notification.id = :notification_id')
            ->setParameter('notification_id', $notification->getId())
        ;

        if ($seen !== null) {
            $whereSeen = $seen ? 1 : 0;
            $qb
                ->andWhere('nn.seen = :seen')
                ->setParameter('seen', $whereSeen)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
