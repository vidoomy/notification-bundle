<?php

namespace Vidoomy\NotificationBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface NotificationInterface
{
    public function getId(): ?int;

    public function getDate(): \DateTimeInterface;

    public function setDate(\DateTimeInterface $date): NotificationInterface;

    public function getSubject(): string;

    public function setSubject(?string $subject): NotificationInterface;

    public function getExcerpt(): ?string;

    public function setExcerpt(?string $excerpt): NotificationInterface;

    public function getMessage(): ?string;

    public function setMessage(?string $message): NotificationInterface;

    public function getLink(): ?string;

    public function setLink(?string $link): NotificationInterface;

    public function getPriority(): ?int;

    public function setPriority(int $priority): NotificationInterface;

    public function getTags(): array;

    public function setTags(array $tags): NotificationInterface;

    /**
     * @return Collection<int, NotifiableNotification>
     */
    public function getNotifiableNotifications(): Collection;

    public function addNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface;

    public function removeNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface;
}
