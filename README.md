bundle-controller-extra
=======================

Extra features for the FOSRestBundle

## Usage

- RestrictExtraParam Annotation : to forbid unknown parameters

```php

use FOS\RestBundle\Controller\Annotations\QueryParam;
use M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam;

/**
 * TestController
 */
class TestController
{
    /**
     * Restricted controller : only "param1" is permitted
     *
     * @return void
     *
     * @RestrictExtraParam()
     *
     * @QueryParam(name="param1", requirements="\d+", nullable=true, description="My Param 1")
     */
    public function getRestrictedAction() {

    }

    /**
     * Unrestricted controller : "param1" and unknown parameters are permitted
     *
     * @QueryParam(name="param1", requirements="\d+", nullable=true, description="My Param 1")
     *
     * @return void
     */
    public function getNonRestrictedAction() {

    }
}

```
## Launch Tests

```shell
$ ./vendor/bin/atoum
```