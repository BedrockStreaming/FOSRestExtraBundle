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
    protected Reader $reader;
    protected ParamFetcherInterface $paramFetcher;
    /** @var int Error code to return for invalid input */
    protected int $errorCode = 400;
    protected bool $allowExtraParam = true;
    protected bool $strict = false;

    public function __construct(Reader $reader, ParamFetcherInterface $paramFetcher)
    {
        $this->reader = $reader;
        $this->paramFetcher = $paramFetcher;
    }

    public function setAllowExtraParam(bool $allow): self
    {
        $this->allowExtraParam = $allow;

        return $this;
    }

    public function setStrict(bool $strict): self
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Define HTTP status code returned on error
     */
    public function setErrorCode(int $code): self
    {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Core controller handler.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->isMainRequest()) {
            $request = $event->getRequest();
            $paramFetcher = $this->paramFetcher;

            if ($request->attributes->has('paramFetcher') && $request->attributes->get('paramFetcher') instanceof ParamFetcherInterface) {
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
                    $route = $request->attributes->get('_route');
                    $msg = sprintf(
                        "Invalid parameters '%s' for route '%s'",
                        implode(', ', $invalidParams),
                        is_string($route) ? $route : 'unknown'
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
