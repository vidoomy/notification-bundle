<?php

/*
 * Symfony\Component\Routing\Attribute\Route exists since 6.4 and the Annotation\Route name is
 * deprecated since 7.4. On 5.4 only the Annotation name exists, so alias it to the Attribute name:
 * the controllers can then import Attribute\Route on every supported version.
 */
if (!class_exists(\Symfony\Component\Routing\Attribute\Route::class, false)
    && class_exists(\Symfony\Component\Routing\Annotation\Route::class)
    && !class_exists(\Symfony\Component\Routing\Attribute\Route::class)
) {
    class_alias(\Symfony\Component\Routing\Annotation\Route::class, \Symfony\Component\Routing\Attribute\Route::class);
}
