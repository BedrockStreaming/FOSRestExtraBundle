<?php

namespace M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener;

use mageekguy\atoum;
use Symfony\Component\HttpFoundation\ParameterBag;
use M6Web\Bundle\FOSRestExtraBundle\EventListener\ParamFetcherListener as Base;

/**
 * Test ParamFetcherListener
 */
class ParamFetcherListener extends atoum\test
{
    /**
     * Test valid params on a restricted route
     */
    public function testCheckRestrictedValidParams()
    {
        $this
            ->if($base = $this->getBase(['test' => null], true))
            ->and($event = $this->getFilterControllerEvent('getRestrictedAction', ['test' => 1]))
            ->then
                ->variable($base->onKernelController($event))
                    ->isNull()
        ;
    }

    /**
     * Test invalid params on a restricted route
     */
    public function testCheckRestrictedInvalidParams()
    {
        $this
            ->if($base = $this->getBase(['test' => null], true))
            ->and($event = $this->getFilterControllerEvent('getRestrictedAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->exception(
                    function() use($base, $event) {
                        $base->onKernelController($event);
                    }
                )
                    ->isInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException')
                    ->hasMessage("Invalid parameters 'test2' for route 'get_test'")
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(400)
        ;

        $this
            ->if($base = $this->getBase(['test' => null], true))
            ->and($base->alwaysCheckRequestParameters(true))
            ->and($base->setErrorCode(401))
            ->and($event = $this->getFilterControllerEvent('getNonRestrictedAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->exception(
                    function() use($base, $event) {
                        $base->onKernelController($event);
                    }
                )
                    ->isInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException')
                    ->hasMessage("Invalid parameters 'test2' for route 'get_test'")
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(401)
        ;
    }

    /**
     * Test valid params on a non-restricted route
     */
    public function testCheckNonRestrictedValidParams()
    {
        $this
            ->if($base = $this->getBase(['test' => null], false))
            ->and($event = $this->getFilterControllerEvent('getNonRestrictedAction', ['test' => 1]))
            ->then
                ->variable($base->onKernelController($event))
                    ->isNull()
        ;
    }

    /**
     * Test valid invalid params on a non-restricted route
     */
    public function testCheckNonRestrictedInvalidParams()
    {
        $this
            ->if($base = $this->getBase(['test' => null], false))
            ->and($event = $this->getFilterControllerEvent('getNonRestrictedAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->variable($base->onKernelController($event))
                    ->isNull()
        ;
    }

    protected function getBase($fetcherParams, $restricted)
    {
        $this->mockGenerator->orphanize('__construct');

        // Generate Reader (a real one !)
        $reader = new \Doctrine\Common\Annotations\AnnotationReader;

        // Generate ParamFetcher
        $paramFetcher = new \mock\FOS\RestBundle\Request\ParamFetcherInterface;
        $paramFetcher->getMockController()->all = $fetcherParams;

        // Generate Base
        $base = new Base($reader, $paramFetcher);

        return $base;
    }

    protected function getFilterControllerEvent($controllerMethod, $queryParams)
    {
        $this->mockGenerator->orphanize('__construct');

        // Generate Request
        $request = new \mock\Symfony\Component\HttpFoundation\Request;
        $request->query = new ParameterBag($queryParams);
        $request->attributes = new ParameterBag(['_route' => 'get_test']);

        $this->mockGenerator->orphanize('__construct');

        // Generate Event
        $event = new \mock\Symfony\Component\HttpKernel\Event\FilterControllerEvent;
        $event->getMockController()->getRequest = $request;
        $event->getMockController()->getController = [
            'M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener\TestController',
            $controllerMethod
        ];

        return $event;
    }
}
