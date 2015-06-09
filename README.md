Console Management of an Apigility Doctrine OAuth2 server
=========================================================


About
-----

This repository provides console routes to manage a headless OAuth2 server.


Installation
------------

Installation of this module uses composer. For composer documentation, please refer to [getcomposer.org](http://getcomposer.org/).

```sh
$ php composer.phar require stuki/zf-oauth2-doctrine-console "*""
```

Add this module to your application's configuration:

```php
'modules' => array(
   ...
   'ZF\OAuth2\Doctrine\Console',
),
```

