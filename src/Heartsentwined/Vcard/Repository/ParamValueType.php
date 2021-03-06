<?php

namespace Heartsentwined\Vcard\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ParamValueType
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ParamValueType extends EntityRepository
{
    const TEXT          = 'text';
    const URI           = 'uri';
    const DATE          = 'date';
    const TIME          = 'time';
    const DATETIME      = 'datetime';
    const DATEANDORTIME = 'date-and-or-time';
    const TIMESTAMP     = 'timestamp';
    const BOOLEAN       = 'boolean';
    const INTEGER       = 'integer';
    const FLOAT         = 'float';
    const UTCOFFSET     = 'utf-offset';
    const LANGUAGETAG   = 'language-tag';
}
