<?php

namespace Vidoomy\NotificationBundle;

final class VidoomyNotificationEvents
{
    /**
     * Occurs when a Notification is created.
     *
     * @Event("Vidoomy\NotificationBundle\Event\CreatedNotificationEvent")
     */
    const CREATED = 'vidoomy.notification.created';

    /**
     * Occurs when a Notification is assigned to a NotifiableEntity.
     *
     * @Event("Vidoomy\NotificationBundle\Event\AssignedNotificationEvent")
     */
    const ASSIGNED = 'vidoomy.notification.assigned';

    /**
     * Occurs when a Notification is marked as seen.
     *
     * @Event("Vidoomy\NotificationBundle\Event\SeenNotificationEvent")
     */
    const SEEN = 'vidoomy.notification.seen';

    /**
     * Occurs when a Notification is marked as unseen.
     *
     * @Event("Vidoomy\NotificationBundle\Event\UnseenNotificationEvent")
     */
    const UNSEEN = 'vidoomy.notification.unseen';

    /**
     * Occurs when a Notification is modified.
     *
     * @Event("Vidoomy\NotificationBundle\Event\ModifiedNotificationEvent")
     */
    const MODIFIED = 'vidoomy.notification.modified';

    /**
     * Occurs when a Notification is removed.
     *
     * @Event("Vidoomy\NotificationBundle\Event\RemovedNotificationEvent")
     */
    const REMOVED = 'vidoomy.notification.removed';

    /**
     * Occurs when a Notification is deleted
     *
     * @Event("Vidoomy\NotificationBundle\Event\DeletedNotificationEvent")
     */
    const DELETED = 'vidoomy.notification.delete';
}
