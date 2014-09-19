Kilix ApiCoreBundle
===================

Installation
------------

* Add the kilix composer repository to `composer.json`

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://composer.kilix.net"
        }
    ]
}
```

* add the bundle to dependencies

```sh
php composer.phar require kilix/api-core-bundle=~0.1
```
* then enable the bundle in the kernel in `app/AppKernel.php`

```php
<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Kilix\Bundle\ApiCoreBundle\KilixApiCoreBundle(),
        );

        // ...
    }

    // ...
}
```
