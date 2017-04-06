<?php

namespace M6Web\Bundle\FOSRestExtraBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * This listener handles various setup tasks related to the query fetcher.
 *
 * - Checking the parameters vs request
 */
class ParamFetcherListener
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var ParamFetcherInterface
     */
    protected $paramFetcher;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * @var integer $errorCode Error code to return for invalid input
     */
    protected $errorCode = 400;

    /**
     * @var boolean
     */
    protected $allowExtraParam = true;

    /**
     * @var boolean
     */
    protected $strict = false;

    /**
     * @param Reader                $reader
     * @param ParamFetcherInterface $paramFetcher
     * @param boolean               $debug
     */
    public function __construct(Reader $reader, ParamFetcherInterface $paramFetcher, $debug = false)
    {
        $this->reader       = $reader;
        $this->paramFetcher = $paramFetcher;
        $this->debug        = $debug;
    }

    /**
     * @param boolean $allow
     *
     * @return ParamFetcherListener
     */
    public function setAllowExtraParam($allow)
    {
        $this->allowExtraParam = $allow;

        return $this;
    }

    /**
     * @param boolean $strict
     *
     * @return ParamFetcherListener
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Define HTTP status code returned on error
     *
     * @param integer $code
     *
     * @return ParamFetcherListener
     */
    public function setErrorCode($code)
    {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Core controller handler.
     *
     * @param FilterControllerEvent $event
     *
     * @throws HttpException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->isMasterRequest()) {
            $request      = $event->getRequest();
            $paramFetcher = $this->paramFetcher;

            if ($this->isDebugExclude($event)) {
                return;
            }

            if ($request->attributes->has('paramFetcher')) {
                $paramFetcher = $request->attributes->get('paramFetcher');
            }

            // Check difference between the paramFetcher and the request
            try {
                $invalidParams = array_diff(
                    array_keys($request->query->all()),
                    array_keys($paramFetcher->all($this->strict))
                );
            } catch (\RuntimeException $e) {
                throw new HttpException($this->errorCode, $e->getMessage(), $e);
            }

            if (!empty($invalidParams) && $this->isExtraParametersCheckRequired($event)) {
                $msg = sprintf(
                    "Invalid parameters '%s' for route '%s'",
                    implode(', ', $invalidParams),
                    $request->attributes->get('_route')
                );

                throw new HttpException($this->errorCode, $msg);
            }
        }
    }

    protected function isDebugExclude(FilterControllerEvent $event)
    {
        return ($this->debug === true && in_array($event->getRequest()->get('_route'), ['_profiler', 'wdt']));
    }

    protected function isExtraParametersCheckRequired(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (is_callable($controller) && method_exists($controller, '__invoke')) {
            $controller = array($controller, '__invoke');
        }

        if (is_array($controller)) {
            $annotation = $this->reader->getMethodAnnotation(
                new \ReflectionMethod($controller[0], $controller[1]),
                'M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam'
            );

            if (!is_null($annotation)) {
                return (bool) $annotation->value;
            }
        }

        return !$this->allowExtraParam;
    }
}
