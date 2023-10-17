<?php

namespace Vidoomy\NotificationBundle\Model;

use Carbon\Carbon;
use Carbon\Translator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Vidoomy\NotificationBundle\Entity\NotifiableNotification;
use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\NotifiableInterface;

/**
 * Class Notification
 * Notifications defined in your app must implement this class
 *
 * @ORM\MappedSuperclass(repositoryClass="Vidoomy\NotificationBundle\Entity\Repository\NotificationRepository")
 * @package Vidoomy\NotificationBundle\Model
 */
abstract class Notification implements \JsonSerializable, NotificationInterface
{

    const ENTITY_FIELD_ID = "id";
    const ENTITY_FIELD_DATE = "date";
    const ENTITY_FIELD_SUBJECT = "subject";
    const ENTITY_FIELD_EXCERPT = "excerpt";
    const ENTITY_FIELD_MESSAGE = "message";
    const ENTITY_FIELD_LINK = "link";
    const ENTITY_FIELD_HUMAN_DATE = "humanDate";

    const NOTIFICATION_PRIORITY_HIGH = 1;
    const NOTIFICATION_PRIORITY_MEDIUM = 2;
    const NOTIFICATION_PRIORITY_LOW = 3;

    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @var string
     * @ORM\Column(type="string", length=4000)
     */
    protected $subject;

    /**
     * @var string
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    protected $excerpt;

    /**
     * @var string
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    protected $message;

    /**
     * @var string
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    protected $link;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $priority = self::NOTIFICATION_PRIORITY_LOW;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    protected $tags;

    /**
     * @var NotifiableNotification[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Vidoomy\NotificationBundle\Entity\NotifiableNotification", mappedBy="notification", cascade={"persist"})
     */
    protected $notifiableNotifications;

    /**
     * AbstractNotification constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->notifiableNotifications = new ArrayCollection();
    }

    /**
     * @return int Notification Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getHumanDate($locale = "en"): string
    {
        $translator = Translator::get($locale);
        $date = Carbon::instance($this->date);

        return $date->locale($locale)->diffForHumans();
    }

    /**
     * @param \DateTimeInterface $date
     * @return $this
     */
    public function setDate(\DateTimeInterface $date): NotificationInterface
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     * @return $this
     */
    public function setSubject(?string $subject): NotificationInterface
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param bool $generateIfEmpty
     * @return string
     */
    public function getExcerpt($generateIfEmpty = true): ?string
    {
        if (!is_null($this->excerpt)) {
            return $this->excerpt;
        } else {
            if ($generateIfEmpty) {
                return $this->generateExcerptFromMessage();
            }
        }

        return null;
    }

    /**
     * @param string|null $excerpt
     * @return NotificationInterface
     */
    public function setExcerpt(?string $excerpt): NotificationInterface
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(?string $message): NotificationInterface
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setLink(?string $link): NotificationInterface
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority): NotificationInterface
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return NotificationInterface
     */
    public function setTags(array $tags): NotificationInterface
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @param string $tag
     * @return NotificationInterface
     */
    public function addTag(string $tag): NotificationInterface
    {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @return ArrayCollection|NotifiableNotification[]
     */
    public function getNotifiableNotifications()
    {
        return $this->notifiableNotifications;
    }

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return $this
     */
    public function addNotifiableNotification(NotifiableNotification $notifiableNotification): NotificationInterface
    {
        if (!$this->notifiableNotifications->contains($notifiableNotification)) {
            $this->notifiableNotifications[] = $notifiableNotification;
            $notifiableNotification->setNotification($this);
        }

        return $this;
    }

    /**
     * @param NotifiableNotification $notifiableNotification
     *
     * @return $this
     */
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
                $notifiableNotification->getNotification()->getId() === $this->getId() &&
                (int) $notifiableNotification->getNotifiableEntity()->getIdentifier() === (int) $notifiable->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSubject() . ' - ' . $this->getMessage();
    }

    /**
     * @param int $excerptLength
     * @return string
     */
    public function generateExcerptFromMessage(int $excerptLength = 100): string
    {
        if ($this->isHTML()) {
            $excerpt = $this->extractParagraph();
            if (strlen($excerpt) <= $excerptLength) {
                return $excerpt;
            } else {
                return $this->adjustExcerpt($excerpt, $excerptLength);
            }
        } else {
            return $this->adjustExcerpt($this->message, $excerptLength);
        }
    }

    /**
     * @param string $excerpt
     * @param int $excerptLength
     * @return string
     */
    private function adjustExcerpt(string $excerpt, int $excerptLength = 100): string
    {
        $charAtPosition = "";
        $messageLength = strlen($excerpt);

        do {
            $excerptLength++;
            $charAtPosition = substr($excerpt, $excerptLength, 1);
        } while ($excerptLength < $messageLength && $charAtPosition !== " ");

        return substr($this->message, 0, $excerptLength) . "...";

    }

    /**
     * @return string
     */
    private function extractParagraph(): string
    {
        $string = $this->message;

        if (!is_null($string)) {
            return substr($string, strpos($string, "<p"), strpos($string, "</p>") + 4);
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isHTML(): bool
    {
        return $this->message != strip_tags($this->message) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            self::ENTITY_FIELD_ID           => $this->getId(),
            self::ENTITY_FIELD_DATE         => $this->getDate()->format(\DateTime::ISO8601),
            self::ENTITY_FIELD_HUMAN_DATE   => $this->getHumanDate(),
            self::ENTITY_FIELD_SUBJECT      => $this->getSubject(),
            self::ENTITY_FIELD_EXCERPT      => $this->getExcerpt(),
            self::ENTITY_FIELD_MESSAGE      => $this->getMessage(),
            self::ENTITY_FIELD_LINK         => $this->getLink()
        ];
    }
}
