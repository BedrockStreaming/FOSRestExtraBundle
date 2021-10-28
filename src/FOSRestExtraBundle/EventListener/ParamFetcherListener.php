<?php

namespace M6Web\Bundle\FOSRestExtraBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * This listener handles various setup tasks related to the query fetcher.
 *
 * - Checking the parameters vs request
 */
class ParamFetcherListener
{
    /** @var Reader */
    protected $reader;

    /** @var ParamFetcherInterface */
    protected $paramFetcher;

    /** @var int Error code to return for invalid input */
    protected $errorCode = 400;

    /** @var bool */
    protected $allowExtraParam = true;

    /** @var bool */
    protected $strict = false;

    public function __construct(Reader $reader, ParamFetcherInterface $paramFetcher)
    {
        $this->reader = $reader;
        $this->paramFetcher = $paramFetcher;
    }

    /**
     * @param bool $allow
     *
     * @return ParamFetcherListener
     */
    public function setAllowExtraParam($allow)
    {
        $this->allowExtraParam = $allow;

        return $this;
    }

    /**
     * @param bool $strict
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
     * @param int $code
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
     * @throws HttpException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $request = $event->getRequest();
            $paramFetcher = $this->paramFetcher;

            if ($request->attributes->has('paramFetcher')) {
                $paramFetcher = $request->attributes->get('paramFetcher');
            }

            $requestGetParams = $request->query->all();
            $requestPostParams = $request->request->all();

            // Check difference between the paramFetcher and the request
            foreach ([$requestGetParams, $requestPostParams] as $requestParams) {
                try {
                    $invalidParams = array_diff(
                        array_keys($requestParams),
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
    }

    protected function isExtraParametersCheckRequired(ControllerEvent $event): bool
    {
        $controller = $event->getController();

        if (is_callable($controller) && (is_object($controller) || is_string($controller)) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
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
