<?php

namespace Heartsentwined\Vcard\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DateTimeText
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DateTimeText extends EntityRepository
{
    const FULL      = 'full';
    const PARTIAL   = 'partial';
    const TEXT      = 'text';
}
