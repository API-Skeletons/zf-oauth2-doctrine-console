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

Console Routes
------------------

* `oauth2:jwt:create` Create a new JWT for a given client.  This JWT will be used by an oauth2 connection requesting a grant_type of `urn:ietf:params:oauth:grant-type:jwt-bearer`.  Creating the JWT puts the oauth2 connection request's public key in place in the OAuth2 tables.

* `oauth2:public-key:create` Create the public/private key record for the given client.  This data is used to sign JWT access tokens.  Each client may have only one key pair.

For the connecting side [zf-oauth2-client](https://github.com/TomHAnderson/zf-oauth2-client) provides a command line tool to generate a JWT reqeust.  See also http://bshaffer.github.io/oauth2-server-php-docs/grant-types/jwt-bearer/

