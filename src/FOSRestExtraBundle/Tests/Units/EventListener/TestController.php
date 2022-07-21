<?php

namespace M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener;

use M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam;

class TestController
{
    /**
     * @RestrictExtraParam(true)
     */
    public static function getRestrictedTrueAction()
    {
    }

    /**
     * @RestrictExtraParam(false)
     */
    public static function getRestrictedFalseAction()
    {
    }

    /**
     * @RestrictExtraParam()
     */
    public static function getRestrictedDefaultAction()
    {
    }

    public static function getNonRestrictedAction()
    {
    }
}
