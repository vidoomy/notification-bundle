<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vidoomy\NotificationBundle\Entity\Repository\NotificationRepository;
use Vidoomy\NotificationBundle\Model\Notification as NotificationModel;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification extends NotificationModel implements NotificationInterface
{
}
