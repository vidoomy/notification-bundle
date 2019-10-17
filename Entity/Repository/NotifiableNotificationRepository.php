<?php
/**
 * Created by PhpStorm.
 * User: maximilien
 * Date: 12/10/17
 * Time: 09:24
 */

namespace Vidoomy\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Vidoomy\NotificationBundle\Entity\NotifiableEntity;
use Vidoomy\NotificationBundle\Entity\NotifiableNotification;

class NotifiableNotificationRepository extends EntityRepository
{

    /**
     * @param int $notificationId
     * @param NotifiableEntity $notifiable
     *
     * @return NotifiableNotification|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOne(int $notificationId, NotifiableEntity $notifiable): NotifiableNotification
    {
        return $this->createQueryBuilder('nn')
            ->join('nn.notification', 'n')
            ->join('nn.notifiableEntity', 'ne')
            ->where('n.id = :notification_id')
            ->andWhere('ne.id = :notifiable_id')
            ->setParameter('notification_id', $notificationId)
            ->setParameter('notifiable_id', $notifiable->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Get all NotifiableNotifications for a notifiable
     *
     * @param string $identifier
     * @param string $class
     * @param string $order
     *
     * @return NotifiableNotification[]
     */
    public function findAllForNotifiable(string $identifier, string $class, $order = 'DESC'): array
    {
        return $this->createQueryBuilder('nn')
            ->join('nn.notifiableEntity', 'ne')
            ->join('nn.notification', 'no')
            ->where('ne.identifier = :identifier')
            ->andWhere('ne.class = :class')
            ->setParameter('identifier', $identifier)
            ->setParameter('class', $class)
            ->orderBy('no.id', $order)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $identifier
     * @param string $class
     * @param bool|null $seen
     * @param string    $order
     * @param null|int  $limit
     * @param null|int  $offset
     *
     * @return array
     */
    public function findAllByNotifiable(
        string $identifier,
        string $class,
        bool $seen = null,
        string $order = 'DESC',
        int $limit = null,
        int $offset = null
    ) {
        $qb = $this->findAllByNotifiableQb($identifier, $class, $order);

        if ($seen !== null) {
            $whereSeen = $seen ? 1 : 0;
            $qb
                ->andWhere('nn.seen = :seen')
                ->setParameter('seen', $whereSeen)
            ;
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $identifier
     * @param string $class
     * @param string $order
     *
     * @return QueryBuilder
     */
    public function findAllByNotifiableQb(string $identifier, string $class, string $order = 'DESC')
    {
        return $this->createQueryBuilder('nn')
            ->addSelect('n')
            ->join('nn.notification', 'n')
            ->join('nn.notifiableEntity', 'ne')
            ->where('ne.identifier = :identifier')
            ->andWhere('ne.class = :class')
            ->orderBy('n.date', $order)
            ->addOrderBy('n.priority', 'ASC')
            ->setParameter('identifier', $identifier)
            ->setParameter('class', $class);
    }

    /**
     * @param int $id
     * @param string $order
     *
     * @return QueryBuilder
     */
    public function findAllForNotifiableIdQb(int $id, string $order = 'DESC'): QueryBuilder
    {
        return $this->createQueryBuilder('nn')
            ->addSelect('n')
            ->join('nn.notification', 'n')
            ->join('nn.notifiableEntity', 'ne')
            ->where('ne.id = :id')
            ->orderBy('n.id', $order)
            ->setParameter('id', $id)
        ;
    }

    /**
     * Get the NotifiableNotifications for a NotifiableEntity id
     *
     * @param int $id
     * @param string $order
     *
     * @return NotifiableNotification[]
     */
    public function findAllForNotifiableId(int $id, string $order = 'DESC'): array
    {
        return $this->findAllForNotifiableIdQb($id, $order)->getQuery()->getResult();
    }

    /**
     * @param string $identifier
     * @param string $class
     *
     * @return QueryBuilder
     */
    protected function getNotificationCountQb(string $identifier, string $class)
    {
        return $this->createQueryBuilder('nn')
            ->select('COUNT(nn.id)')
            ->join('nn.notifiableEntity', 'ne')
            ->where('ne.identifier = :notifiable_identifier')
            ->andWhere('ne.class = :notifiable_class')
            ->setParameter('notifiable_identifier', $identifier)
            ->setParameter('notifiable_class', $class)
        ;
    }

    /**
     * Get the count of Notifications for a Notifiable entity.
     *
     * seen option results :
     *      null : get all notifications
     *      true : get seen notifications
     *      false : get unseen notifications
     *
     * @param string $identifier
     * @param string $class
     * @param bool|null $seen
     *
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getNotificationCount(string $identifier, string $class, $seen = null): int
    {
        $qb = $this->getNotificationCountQb($identifier, $class);

        if ($seen !== null) {
            $whereSeen = $seen ? 1 : 0;
            $qb
                ->andWhere('nn.seen = :seen')
                ->setParameter('seen', $whereSeen);
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
}
