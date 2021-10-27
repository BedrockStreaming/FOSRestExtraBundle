<?php

namespace M6Web\Bundle\FOSRestExtraBundle\Annotation;

/**
 * RestrictExtraParam annotation to forbid unknown parameters
 *
 * @Annotation
 * @Target("METHOD")
 */
class RestrictExtraParam
{
    public bool $value = true;
}
