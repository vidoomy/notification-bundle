<?php

namespace Vidoomy\NotificationBundle\Model;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vidoomy\NotificationBundle\Entity\NotifiableNotification;
use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\Entity\Repository\NotificationRepository;
use Vidoomy\NotificationBundle\NotifiableInterface;

/**
 * Notifications defined in your app must extend this class.
 */
#[ORM\MappedSuperclass(repositoryClass: NotificationRepository::class)]
abstract class Notification implements \JsonSerializable, NotificationInterface
{
    public const ENTITY_FIELD_ID = 'id';
    public const ENTITY_FIELD_DATE = 'date';
    public const ENTITY_FIELD_SUBJECT = 'subject';
    public const ENTITY_FIELD_EXCERPT = 'excerpt';
    public const ENTITY_FIELD_MESSAGE = 'message';
    public const ENTITY_FIELD_LINK = 'link';
    public const ENTITY_FIELD_HUMAN_DATE = 'humanDate';

    public const NOTIFICATION_PRIORITY_HIGH = 1;
    public const NOTIFICATION_PRIORITY_MEDIUM = 2;
    public const NOTIFICATION_PRIORITY_LOW = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    protected \DateTimeInterface $date;

    #[ORM\Column(type: 'string', length: 4000)]
    protected ?string $subject = null;

    #[ORM\Column(type: 'string', length: 4000, nullable: true)]
    protected ?string $excerpt = null;

    #[ORM\Column(type: 'string', length: 4000, nullable: true)]
    protected ?string $message = null;

    #[ORM\Column(type: 'string', length: 4000, nullable: true)]
    protected ?string $link = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $priority = self::NOTIFICATION_PRIORITY_LOW;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $tags = null;

    /**
     * @var Collection<int, NotifiableNotification>
     */
    #[ORM\OneToMany(targetEntity: NotifiableNotification::class, mappedBy: 'notification', cascade: ['persist'])]
    protected Collection $notifiableNotifications;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->notifiableNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getHumanDate(string $locale = 'en'): string
    {
        return Carbon::instance($this->date)->locale($locale)->diffForHumans();
    }

    public function setDate(\DateTimeInterface $date): NotificationInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getSubject(): string
    {
        return (string) $this->subject;
    }

    public function setSubject(?string $subject): NotificationInterface
    {
        $this->subject = $subject;

        return $this;
    }

    public function getExcerpt(bool $generateIfEmpty = true): ?string
    {
        if (null !== $this->excerpt) {
            return $this->excerpt;
        }

        if ($generateIfEmpty) {
            return $this->generateExcerptFromMessage();
        }

        return null;
    }

    public function setExcerpt(?string $excerpt): NotificationInterface
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): NotificationInterface
    {
        $this->message = $message;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): NotificationInterface
    {
        $this->link = $link;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): NotificationInterface
    {
        $this->priority = $priority;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    public function setTags(array $tags): NotificationInterface
    {
        $this->tags = $tags;

        return $this;
    }

    public function addTag(string $tag): NotificationInterface
    {
        if (!in_array($tag, $this->getTags(), true)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @return Collection<int, NotifiableNotification>
     */
    public function getNotifiableNotifications(): Collection
    {
        return $this->notifiableNotifications;
    }

    public function addNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface
    {
        if (!$this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications[] = $notifiableNotification;
            $notifiableNotification->setNotification($this);
        }

        return $this;
    }

    public function removeNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface
    {
        if ($this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications->removeElement($notifiableNotification);
            $notifiableNotification->setNotification(null);
        }

        return $this;
    }

    public function isOwnedBy(NotifiableInterface $notifiable): bool
    {
        foreach ($this->getNotifiableNotifications() as $notifiableNotification) {
            if (
                $notifiableNotification->getNotification()?->getId() === $this->getId()
                && (int) $notifiableNotification->getNotifiableEntity()?->getIdentifier() === (int) $notifiable->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->getSubject() . ' - ' . $this->getMessage();
    }

    public function generateExcerptFromMessage(int $excerptLength = 100): string
    {
        if ($this->isHTML()) {
            $excerpt = $this->extractParagraph();
            if (strlen($excerpt) <= $excerptLength) {
                return $excerpt;
            }

            return $this->adjustExcerpt($excerpt, $excerptLength);
        }

        return $this->adjustExcerpt((string) $this->message, $excerptLength);
    }

    private function adjustExcerpt(string $excerpt, int $excerptLength = 100): string
    {
        $messageLength = strlen($excerpt);

        do {
            $excerptLength++;
            $charAtPosition = substr($excerpt, $excerptLength, 1);
        } while ($excerptLength < $messageLength && ' ' !== $charAtPosition);

        return substr((string) $this->message, 0, $excerptLength) . '...';
    }

    private function extractParagraph(): string
    {
        $string = (string) $this->message;

        return substr($string, (int) strpos($string, '<p'), (int) strpos($string, '</p>') + 4);
    }

    private function isHTML(): bool
    {
        return $this->message != strip_tags((string) $this->message);
    }

    public function jsonSerialize(): array
    {
        return [
            self::ENTITY_FIELD_ID => $this->getId(),
            self::ENTITY_FIELD_DATE => $this->getDate()->format(\DateTimeInterface::ISO8601),
            self::ENTITY_FIELD_HUMAN_DATE => $this->getHumanDate(),
            self::ENTITY_FIELD_SUBJECT => $this->getSubject(),
            self::ENTITY_FIELD_EXCERPT => $this->getExcerpt(),
            self::ENTITY_FIELD_MESSAGE => $this->getMessage(),
            self::ENTITY_FIELD_LINK => $this->getLink(),
        ];
    }
}
