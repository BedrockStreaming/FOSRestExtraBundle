<?php

namespace M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener;

use M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam;

class TestController
{
    /**
     * @RestrictExtraParam(true)
     */
    public function getRestrictedTrueAction()
    {
    }

    /**
     * @RestrictExtraParam(false)
     */
    public function getRestrictedFalseAction()
    {
    }

    /**
     * @RestrictExtraParam()
     */
    public function getRestrictedDefaultAction()
    {
    }

    public function getNonRestrictedAction()
    {
    }
}
