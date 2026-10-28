<?php

namespace Vidoomy\NotificationBundle\Twig;

use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vidoomy\NotificationBundle\Entity\NotifiableEntity;
use Vidoomy\NotificationBundle\Entity\NotificationInterface;
use Vidoomy\NotificationBundle\Manager\NotificationManager;
use Vidoomy\NotificationBundle\NotifiableInterface;

/**
 * Twig extension to display notifications.
 */
class NotificationExtension extends AbstractExtension
{
    private const ROUTES_WITHOUT_NOTIFICATION = ['notification_list', 'notification_all', 'notification_mark_all_as_seen'];
    private const ROUTES_WITH_NOTIFICATION = ['notification_mark_as_seen', 'notification_mark_as_unseen', 'notification_delete'];

    public function __construct(
        protected NotificationManager $notificationManager,
        protected Environment $twig,
        protected RouterInterface $router
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vidoomy_notification_render', [$this, 'render'], ['is_safe' => ['html']]),
            new TwigFunction('vidoomy_notification_count', [$this, 'countNotifications'], ['is_safe' => ['html']]),
            new TwigFunction('vidoomy_notification_unseen_count', [$this, 'countUnseenNotifications'], ['is_safe' => ['html']]),
            new TwigFunction('vidoomy_notification_generate_path', [$this, 'generatePath'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Options: seen (bool, default true: all notifications; false: unseen only), order, limit, offset, template.
     */
    public function render(NotifiableInterface $notifiable, array $options = []): string
    {
        if (!array_key_exists('seen', $options)) {
            $options['seen'] = true;
        }

        return $this->renderNotifications($notifiable, $options);
    }

    public function renderNotifications(NotifiableInterface $notifiable, array $options): string
    {
        $order = $options['order'] ?? 'DESC';
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? null;

        if ($options['seen']) {
            $notifications = $this->notificationManager->getNotifications($notifiable, $order, $limit, $offset);
        } else {
            $notifications = $this->notificationManager->getUnseenNotifications($notifiable, $order, $limit, $offset);
        }

        $template = $options['template'] ?? '@VidoomyNotification/notification_list.html.twig';

        return $this->twig->render($template, ['notificationList' => $notifications]);
    }

    public function countNotifications(NotifiableInterface $notifiable): int
    {
        return $this->notificationManager->getNotificationCount($notifiable);
    }

    public function countUnseenNotifications(NotifiableInterface $notifiable): int
    {
        return $this->notificationManager->getUnseenNotificationCount($notifiable);
    }

    /**
     * Path to a NotificationController action for a notifiable (entity or NotifiableEntity row).
     */
    public function generatePath(string $route, NotifiableInterface|NotifiableEntity $notifiable, ?NotificationInterface $notification = null): string
    {
        $notifiableId = $notifiable instanceof NotifiableInterface
            ? $this->notificationManager->getNotifiableEntity($notifiable)->getId()
            : $notifiable->getId();

        if (in_array($route, self::ROUTES_WITHOUT_NOTIFICATION, true)) {
            return $this->router->generate($route, ['notifiable' => $notifiableId]);
        }

        if (in_array($route, self::ROUTES_WITH_NOTIFICATION, true)) {
            if (null === $notification) {
                throw new \InvalidArgumentException('You must provide a Notification Entity');
            }

            return $this->router->generate($route, ['notifiable' => $notifiableId, 'notification' => $notification->getId()]);
        }

        throw new \InvalidArgumentException(sprintf(
            'You must provide a valid route path. Paths available: %s',
            implode(', ', array_merge(self::ROUTES_WITHOUT_NOTIFICATION, self::ROUTES_WITH_NOTIFICATION))
        ));
    }
}
