<?php

namespace Vidoomy\NotificationBundle\Annotation;

use Vidoomy\NotificationBundle\Attribute\Notifiable as NotifiableAttribute;

/**
 * Kept so that code importing the 1.x class name keeps working as a PHP attribute.
 * The docblock form (@Notifiable) is no longer read: use #[Notifiable(name: '...')].
 *
 * @deprecated since 2.0, use {@see NotifiableAttribute} instead.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Notifiable extends NotifiableAttribute
{
}
