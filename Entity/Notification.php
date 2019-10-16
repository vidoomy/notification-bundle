<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vidoomy\NotificationBundle\Model\Notification as NotificationModel;

/**
 * Class Notification
 *
 * @ORM\Entity
 * @package Vidoomy\NotificationBundle\Entity
 */
class Notification extends NotificationModel implements NotificationInterface
{

}