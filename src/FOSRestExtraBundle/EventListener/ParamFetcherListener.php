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
     * @var integer $errorCode Error code to return for invalid input
     */
    protected $errorCode = 400;

    /**
     * @var boolean
     */
    protected $alwaysCheckRequestParameters = false;

    /**
     * @param Reader                $reader
     * @param ParamFetcherInterface $paramFetcher
     */
    public function __construct(Reader $reader, ParamFetcherInterface $paramFetcher)
    {
        $this->reader       = $reader;
        $this->paramFetcher = $paramFetcher;
    }

    /**
     * @param boolean $check
     *
     * @return ParamFetcherListener
     */
    public function alwaysCheckRequestParameters($check)
    {
        $this->alwaysCheckRequestParameters = $check;

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
     * @throws \InvalidArgumentException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($this->isCheckRequired($event)) {
            $request = $event->getRequest();

            // Check difference between the paramFetcher and the request
            $invalidParams = array_diff(
                array_keys($request->query->all()),
                array_keys($this->paramFetcher->all())
            );

            if (!empty($invalidParams)) {
                $msg = sprintf(
                    "Invalid parameters '%s' for route '%s'",
                    implode(', ', $invalidParams),
                    $request->attributes->get('_route')
                );

                throw new HttpException($this->errorCode, $msg);
            }
        }
    }

    protected function isCheckRequired(FilterControllerEvent $event)
    {
        if ($this->alwaysCheckRequestParameters) {
            return true;
        }

        $controller = $event->getController();

        if (is_callable($controller) && method_exists($controller, '__invoke')) {
            $controller = array($controller, '__invoke');
        }

        if (!is_array($controller)) {
            return false;
        }

        return $this->reader->getMethodAnnotation(
            new \ReflectionMethod($controller[0], $controller[1]),
            'M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam'
        );
    }
}
