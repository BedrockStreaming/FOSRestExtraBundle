<?php

namespace M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener;

use atoum\atoum;
use M6Web\Bundle\FOSRestExtraBundle\EventListener\ParamFetcherListener as Base;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
            ->and($event = $this->getControllerEvent('getRestrictedTrueAction', ['test' => 1]))
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
            ->and($event = $this->getControllerEvent('getRestrictedTrueAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->exception(
                    function () use ($base, $event) {
                        $base->onKernelController($event);
                    }
                )
                    ->isInstanceOf(HttpException::class)
                    ->hasMessage("Invalid parameters 'test2' for route 'get_test'")
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(400)

            ->if($base->setAllowExtraParam(true))
            ->and($base->setErrorCode(401))
            ->and($event = $this->getControllerEvent('getRestrictedTrueAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->exception(
                    function () use ($base, $event) {
                        $base->onKernelController($event);
                    }
                )
                    ->isInstanceOf(HttpException::class)
                    ->hasMessage("Invalid parameters 'test2' for route 'get_test'")
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(401)

            ->if($base->setErrorCode(400))
            ->and($event = $this->getControllerEvent('getRestrictedFalseAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->variable($base->onKernelController($event))
                    ->isNull()

            ->if($event = $this->getControllerEvent('getRestrictedDefaultAction', ['test' => 'toto', 'test2' => 1]))
            ->and($base->setAllowExtraParam(false))
            ->then
                ->exception(
                    function () use ($base, $event) {
                        $base->onKernelController($event);
                    }
                )
                    ->isInstanceOf(HttpException::class)
                    ->hasMessage("Invalid parameters 'test2' for route 'get_test'")
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    /**
     * Test valid params on a non-restricted route
     */
    public function testCheckNonRestrictedValidParams()
    {
        $this
            ->if($base = $this->getBase(['test' => null], false))
            ->and($event = $this->getControllerEvent('getNonRestrictedAction', ['test' => 1]))
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
            ->and($event = $this->getControllerEvent('getNonRestrictedAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->variable($base->onKernelController($event))
                    ->isNull()
        ;
    }

    /**
     * Test strict case
     */
    public function testStrictParameter()
    {
        $this
            ->if($base = $this->getBase(['test' => null], false, true))
            ->and($base->setStrict(true))
            ->and($event = $this->getControllerEvent('getNonRestrictedAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->exception(function () use ($base, $event) {
                    $base->onKernelController($event);
                })
                    ->isInstanceOf(HttpException::class)
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    protected function getBase($fetcherParams, $restricted)
    {
        $this->mockGenerator->orphanize('__construct');

        // Generate Reader (a real one !)
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();

        // Generate ParamFetcher
        $paramFetcher = new \mock\FOS\RestBundle\Request\ParamFetcherInterface();

        $paramFetcher->getMockController()->all = function ($strict = false) use ($fetcherParams) {
            if ($strict) {
                throw new \RuntimeException();
            }

            return $fetcherParams;
        };

        // Generate Base
        $base = new Base($reader, $paramFetcher);

        return $base;
    }

    protected function getControllerEvent($controllerMethod, $queryParams)
    {
        // Generate Request
        $request = new Request($queryParams, $queryParams, ['_route' => 'get_test']);

        $this->mockGenerator->orphanize('__construct');
        // Generate Event
        $resolver = new \mock\Symfony\Component\HttpKernel\Controller\ControllerResolverInterface();
        $httpKernel = new HttpKernel(
            new EventDispatcher(),
            $resolver,
            null,
            new \mock\Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface()
        );

        return new ControllerEvent(
            $httpKernel,
            [TestController::class, $controllerMethod],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
