<?php

namespace Vidoomy\NotificationBundle\Controller;

use Vidoomy\NotificationBundle\Entity\Notification;
use Vidoomy\NotificationBundle\NotifiableInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Vidoomy\NotificationBundle\Entity\NotifiableNotification;

/**
 * Class NotificationController
 * the base controller for notifications
 */
class NotificationController extends AbstractController
{

    /**
     * List of all notifications
     *
     * @Route("/{notifiable}", name="notification_list", methods={"GET"})
     * @param NotifiableInterface $notifiable
     *
     * @return Response
     */
    public function listAction(NotifiableInterface $notifiable): Response
    {
        $notifiableRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository(NotifiableNotification::class);
        $notificationList = $notifiableRepo->findAllForNotifiableId($notifiable);
        return $this->render('@VidoomyNotification/notifications.html.twig', array(
            'notificationList' => $notificationList,
            'notifiableNotifications' => $notificationList // deprecated: alias for backward compatibility only
        ));
    }

    /**
     * @Route("/{notifiable}/all", name="notification_all", methods={"GET"})
     * @param NotifiableInterface $notifiable
     * @return JsonResponse
     */
    public function allAction(NotifiableInterface $notifiable): JsonResponse
    {
        $notifiableRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository(NotifiableNotification::class);
        $notificationList = $notifiableRepo->findAllForNotifiableId($notifiable);

        return new JsonResponse(["status" => "success", "data" => $notificationList]);

    }

    /**
     * Set a Notification as seen
     *
     * @Route("/{notifiable}/mark_as_seen/{notification}", name="notification_mark_as_seen", methods={"POST"})
     * @param int           $notifiable
     * @param Notification  $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function markAsSeenAction(int $notifiable, Notification $notification): JsonResponse
    {
        $manager = $this->get('vidoomy.notification');
        $notifiableEntity = $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable));

        if ($notification->isOwnedBy($notifiableEntity)) {
            $manager->markAsSeen(
                $notifiableEntity,
                $notification,
                true
            );

            return new JsonResponse(["status" => "success"]);
        }

        return new JsonResponse(
            ["status" => "error", "message" => "Unauthorized to perform action"],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Delete a notification
     *
     * @Route("/{notifiable}/delete/{notification}", name="notification_delete", methods={"POST"})
     * @param int           $notifiable
     * @param Notification  $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function deleteAction(int $notifiable, Notification $notification): JsonResponse
    {
        $manager = $this->get('vidoomy.notification');
        $notifiableEntity = $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable));

        if ($notification->isOwnedBy($notifiableEntity)) {
            $manager->deleteNotification(
                $notification,
                true
            );

            return new JsonResponse(["status" => "success"]);

        }

        return new JsonResponse(
            ["status" => "error", "message" => "Unauthorized to perform action"],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Set a Notification as unseen
     *
     * @Route("/{notifiable}/mark_as_unseen/{notification}", name="notification_mark_as_unseen", methods={"POST"})
     * @param int $notifiable
     * @param Notification $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function markAsUnSeenAction(int $notifiable, Notification $notification): JsonResponse
    {
        $manager = $this->get('vidoomy.notification');
        $notifiableEntity = $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable));

        if ($notification->isOwnedBy($notifiableEntity)) {
            $manager->markAsUnseen(
                $notifiableEntity,
                $notification,
                true
            );

            return new JsonResponse(["status" => "success"]);
        }

        return new JsonResponse(
            ["status" => "error", "message" => "Unauthorized to perform action"],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Set all Notifications for a User as seen
     *
     * @Route("/{notifiable}/markAllAsSeen", name="notification_mark_all_as_seen", methods={"POST"})
     * @param int $notifiable
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAllAsSeenAction(int $notifiable)
    {
        $manager = $this->get('vidoomy.notification');
        $manager->markAllAsSeen(
            $manager->getNotifiableInterface($manager->getNotifiableEntityById($notifiable)),
            true
        );

        return new JsonResponse(["status" => "success"]);
    }
}
