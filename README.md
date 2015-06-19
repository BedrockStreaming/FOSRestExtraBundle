bundle-controller-extra
=======================

Extra features for the FOSRestBundle

## Dependency

FOSRestExtraBundle requires [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle).

## Installation

Add this line in your composer.json:

```json
{
    "require": {
        "m6web/fos-rest-extra-bundle": "~1.0"
    }
}
```

Update your vendors:

```sh
$ composer update m6web/fos-rest-extra-bundle
```

Add to your `AppKernel.php`:

```php
new FOS\RestBundle\FOSRestBundle(),
new M6Web\Bundle\FOSRestExtraBundle\M6WebFOSRestExtraBundle(),
```

## Configuration

Modify the FOSRestBundle configuration of your application to add :

```yaml
fost_rest:
    param_fetcher_listener: true
```

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