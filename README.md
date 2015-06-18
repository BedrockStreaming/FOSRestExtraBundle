bundle-controller-extra
=======================

Extra features for the FOSRestBundle

## Usage

- RestrictExtraParam Annotation : to forbid unknown parameters

```php

use M6Web\Bundle\FOSRestExtraBundle\Annotation\RestrictExtraParam;

/**
 * TestController
 */
class TestController
{
    /**
     * Restricted controller
     *
     * @return void
     *
     * @RestrictExtraParam()
     */
    public function getRestrictedAction() {

    }

    /**
     * Unrestricted controller
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