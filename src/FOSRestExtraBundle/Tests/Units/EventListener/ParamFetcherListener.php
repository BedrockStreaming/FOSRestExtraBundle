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
            ->and($event = $this->getFilterControllerEvent('getRestrictedTrueAction', ['test' => 1]))
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
            ->and($event = $this->getFilterControllerEvent('getRestrictedTrueAction', ['test' => 'toto', 'test2' => 1]))
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

            ->if($base->setAllowExtraParam(true))
            ->and($base->setErrorCode(401))
            ->and($event = $this->getFilterControllerEvent('getRestrictedTrueAction', ['test' => 'toto', 'test2' => 1]))
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

            ->if($base->setErrorCode(400))
            ->and($event = $this->getFilterControllerEvent('getRestrictedFalseAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->variable($base->onKernelController($event))
                    ->isNull()

            ->if($event = $this->getFilterControllerEvent('getRestrictedDefaultAction', ['test' => 'toto', 'test2' => 1]))
            ->and($base->setAllowExtraParam(false))
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

    /**
     * Test strict case
     */
    public function testStrictParameter()
    {
        $this
            ->if($base = $this->getBase(['test' => null], false, true))
            ->and($base->setStrict(true))
            ->and($event = $this->getFilterControllerEvent('getNonRestrictedAction', ['test' => 'toto', 'test2' => 1]))
            ->then
                ->exception(function() use ($base, $event) {
                    $base->onKernelController($event);
                })
                    ->isInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException')
                ->integer($this->exception->getStatusCode())
                    ->isEqualTo(400)
        ;
    }

    protected function getBase($fetcherParams, $restricted)
    {
        $this->mockGenerator->orphanize('__construct');

        // Generate Reader (a real one !)
        $reader = new \Doctrine\Common\Annotations\AnnotationReader;

        // Generate ParamFetcher
        $paramFetcher = new \mock\FOS\RestBundle\Request\ParamFetcherInterface;

        $paramFetcher->getMockController()->all = function ($strict = false) use ($fetcherParams) {
            if ($strict) {
                throw new \RuntimeException;
            }

            return $fetcherParams;
        };

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
        $request->request = new ParameterBag($queryParams);
        $request->attributes = new ParameterBag(['_route' => 'get_test']);

        $this->mockGenerator->orphanize('__construct');

        // Generate Event
        $event = new \mock\Symfony\Component\HttpKernel\Event\FilterControllerEvent;
        $event->getMockController()->isMasterRequest = true;
        $event->getMockController()->getRequest = $request;
        $event->getMockController()->getController = [
            'M6Web\Bundle\FOSRestExtraBundle\Tests\Units\EventListener\TestController',
            $controllerMethod
        ];

        return $event;
    }
}
