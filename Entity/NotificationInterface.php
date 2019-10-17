<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface NotificationInterface
 *
 * @package Vidoomy\NotificationBundle\Entity
 */
interface NotificationInterface
{

    /**
     * @return int Notification Id
     */
    public function getId();

    /**
     * @return \DateTimeInterface
     */
    public function getDate();

    /**
     * @param \DateTimeInterface $date
     *
     * @return NotificationInterface
     */
    public function setDate(\DateTimeInterface $date): NotificationInterface;

    /**
     * @return string Notification subject
     */
    public function getSubject(): string;

    /**
     * @param string $subject Notification subject
     *
     * @return NotificationInterface
     */
    public function setSubject(string $subject): NotificationInterface;

    /**
     * @return string Notification excerpt
     */
    public function getExcerpt(): string;

    /**
     * @param string $excerpt Notification excerpt
     *
     * @return NotificationInterface
     */
    public function setExcerpt(string $excerpt): NotificationInterface;

    /**
     * @return string Notification message
     */
    public function getMessage(): string;

    /**
     * @param string $message Notification message
     *
     * @return NotificationInterface
     */
    public function setMessage(string $message): NotificationInterface;

    /**
     * @return string Link to redirect the user
     */
    public function getLink():string;

    /**
     * @param string $link Link to redirect the user
     *
     * @return NotificationInterface
     */
    public function setLink(string $link): NotificationInterface;

    /**
     * @return int Notification priority
     */
    public function getPriority():int;

    /**
     * @param int $priority Notification priority
     *
     * @return NotificationInterface
     */
    public function setPriority(int $priority): NotificationInterface;

    /**
     * @return ArrayCollection|NotifiableNotification[]
     */
    public function getNotifiableNotifications();

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return NotificationInterface
     */
    public function addNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface;

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return NotificationInterface
     */
    public function removeNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface;
}