<?php

namespace Vidoomy\NotificationBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Notifiable
 * @package Vidoomy\NotificationBundle\Annotation
 *
 * @Annotation
 * @Annotation\Target("CLASS")
 */
class Notifiable
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Notifiable
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
}
