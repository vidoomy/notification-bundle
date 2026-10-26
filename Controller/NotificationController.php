<?php

namespace Vidoomy\NotificationBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Vidoomy\NotificationBundle\Entity\Notification;
use Vidoomy\NotificationBundle\Entity\NotifiableNotification;
use Vidoomy\NotificationBundle\Entity\Repository\NotifiableNotificationRepository;
use Vidoomy\NotificationBundle\Manager\NotificationManager;
use Vidoomy\NotificationBundle\NotifiableInterface;

/**
 * Base controller for notifications. {notifiable} is the NotifiableEntity id and
 * {notification} the Notification id; both are resolved here, without a param converter.
 */
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationManager $manager,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/{notifiable}', name: 'notification_list', methods: ['GET'], requirements: ['notifiable' => '\d+'])]
    public function listAction(int $notifiable): Response
    {
        $notificationList = $this->notifiableNotificationRepository()->findAllForNotifiableId($notifiable);

        return $this->render('@VidoomyNotification/notifications.html.twig', [
            'notificationList' => $notificationList,
            'notifiableNotifications' => $notificationList, // deprecated: alias for backward compatibility only
        ]);
    }

    #[Route('/{notifiable}/all', name: 'notification_all', methods: ['GET'], requirements: ['notifiable' => '\d+'])]
    public function allAction(int $notifiable): JsonResponse
    {
        $notificationList = $this->notifiableNotificationRepository()->findAllForNotifiableId($notifiable);

        return new JsonResponse(['status' => 'success', 'data' => $notificationList]);
    }

    #[Route('/{notifiable}/mark_as_seen/{notification}', name: 'notification_mark_as_seen', methods: ['POST'], requirements: ['notifiable' => '\d+', 'notification' => '\d+'])]
    public function markAsSeenAction(int $notifiable, int $notification): JsonResponse
    {
        $notificationEntity = $this->notification($notification);
        $notifiableEntity = $this->notifiable($notifiable);

        if ($notificationEntity->isOwnedBy($notifiableEntity)) {
            $this->manager->markAsSeen($notifiableEntity, $notificationEntity, true);

            return new JsonResponse(['status' => 'success']);
        }

        return $this->unauthorized();
    }

    #[Route('/{notifiable}/delete/{notification}', name: 'notification_delete', methods: ['POST'], requirements: ['notifiable' => '\d+', 'notification' => '\d+'])]
    public function deleteAction(int $notifiable, int $notification): JsonResponse
    {
        $notificationEntity = $this->notification($notification);
        $notifiableEntity = $this->notifiable($notifiable);

        if ($notificationEntity->isOwnedBy($notifiableEntity)) {
            $this->manager->deleteNotification($notificationEntity, true);

            return new JsonResponse(['status' => 'success']);
        }

        return $this->unauthorized();
    }

    #[Route('/{notifiable}/mark_as_unseen/{notification}', name: 'notification_mark_as_unseen', methods: ['POST'], requirements: ['notifiable' => '\d+', 'notification' => '\d+'])]
    public function markAsUnSeenAction(int $notifiable, int $notification): JsonResponse
    {
        $notificationEntity = $this->notification($notification);
        $notifiableEntity = $this->notifiable($notifiable);

        if ($notificationEntity->isOwnedBy($notifiableEntity)) {
            $this->manager->markAsUnseen($notifiableEntity, $notificationEntity, true);

            return new JsonResponse(['status' => 'success']);
        }

        return $this->unauthorized();
    }

    #[Route('/{notifiable}/markAllAsSeen', name: 'notification_mark_all_as_seen', methods: ['POST'], requirements: ['notifiable' => '\d+'])]
    public function markAllAsSeenAction(int $notifiable): JsonResponse
    {
        $this->manager->markAllAsSeen($this->notifiable($notifiable), true);

        return new JsonResponse(['status' => 'success']);
    }

    /**
     * @throws NotFoundHttpException when the id is unknown (what a param converter would have answered)
     */
    private function notification(int $id): Notification
    {
        try {
            return $this->manager->getNotification($id);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @throws NotFoundHttpException when the NotifiableEntity id is unknown or no longer maps to an entity
     */
    private function notifiable(int $id): NotifiableInterface
    {
        try {
            $notifiable = $this->manager->getNotifiableInterface($this->manager->getNotifiableEntityById($id));
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
        if (null === $notifiable) {
            throw new NotFoundHttpException(sprintf('Notifiable %d not found', $id));
        }

        return $notifiable;
    }

    private function notifiableNotificationRepository(): NotifiableNotificationRepository
    {
        return $this->em->getRepository(NotifiableNotification::class);
    }

    private function unauthorized(): JsonResponse
    {
        return new JsonResponse(
            ['status' => 'error', 'message' => 'Unauthorized to perform action'],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
