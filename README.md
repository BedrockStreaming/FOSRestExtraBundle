fos-rest-extra-bundle
=======================

Provide extra feature for the [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle).

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


Then modify the FOSRestBundle configuration of your application to add :

```yaml
fost_rest:
    param_fetcher_listener: true
```

## Configuration

```yml
m6_web_fos_rest_extra:
    param_fetcher:

        # Define if extra parameters are allowed. The behavior defined here is the default one and can
        # be overrided by a "RestrictExtraParam" annotation on the current action.
        # Optionnal, true by default
        allow_extra: true

        # Define if all parameters are strict. If true, all given parameters have to match defined
        # format for each on of them.
        # Optionnal, false by default
        strict: false

        # HTTP status code of throwed exception on query with invalid parameters
        # Optionnal, 400 by default
        error_status_code: 400
```

## Usage

- `RestrictExtraParam(true/false)` Annotation : to allow (`false`) or forbid (`true`) unknown parameters, `true` by default.

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
     * @RestrictExtraParam(true)
     *
     * @QueryParam(name="param1", requirements="\d+", nullable=true, description="My Param 1")
     */
    public function getRestrictedAction() {

    }

    /**
     * Unrestricted controller : "param1" and unknown parameters are permitted
     * except if bundle configuration doesn't allow it
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
$ ./vendor/atoum/atoum/bin/atoum
```
