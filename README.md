# zf2-vcard

[![Build Status](https://secure.travis-ci.org/heartsentwined/zf2-vcard.png)](http://travis-ci.org/heartsentwined/zf2-vcard)

Work with Vcards in a ZF2 app.

# Installation

[Composer](http://getcomposer.org/):

```json
{
    "require": {
        "heartsentwined/zf2-vcard": "dev-master"
    }
}
```

Then add `Heartsentwined\Vcard` to the `modules` key in `(app root)/config/application.config.*`

Vcard module will also hook onto your application's database, through [`DoctrineORMModule`](https://github.com/doctrine/DoctrineORMModule). It will create a number of tables with the prefix `he_vcard_*`, and will use the default EntityManager `doctrine.entitymanager.orm_default`. If your settings are different, please modify the `doctrine` section of `config/module.config.yml` as needed.

Finally, you need to update your database schema. The recommended way is through Doctrine's CLI:

```sh
$ vendor/bin/doctrine-module orm:schema-tool:update --force
```

# Config

todo

# Usage

todo

## Working with Vcards

You can use the Doctrine 2 ORM API directly. Mapping files are located at `(zf2-vcard)/src/Heartsentwined/Vcard/Entity/Mapping`.
