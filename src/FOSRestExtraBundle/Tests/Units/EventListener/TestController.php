<?php

namespace M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener;

use M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam;

/**
 * TestController
 */
class TestController
{
    /**
     * Test controller
     *
     * @return void
     *
     * @RestrictExtraParam()
     */
    public function getRestrictedAction() {

    }

    /**
     * Test controller
     *
     * @return void
     */
    public function getNonRestrictedAction() {

    }
}