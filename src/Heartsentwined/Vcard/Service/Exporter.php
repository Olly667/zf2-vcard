<?php
namespace Heartsentwined\Vcard\Service;

use Doctrine\ORM\EntityManager;
use Heartsentwined\ArgValidator\ArgValidator;
use Heartsentwined\DateTimeParser\Parser as DateTimeParser;
use Heartsentwined\Utf8\Utf8;
use Heartsentwined\Vcard\Entity;
use Heartsentwined\Vcard\Repository;
use Sabre\VObject\Node;
use Sabre\VObject\ParseException;
use Sabre\VObject\Property;
use Sabre\VObject\Reader;

class Exporter
{
    /**
     * ORM Entity Manager
     *
     * @var EntityManager
     */
    protected $em;
    public function setEm(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }
    public function getEm()
    {
        return $this->em;
    }

    /**
     * DateTimeParser
     *
     * @var DateTimeParser
     */
    protected $dateTimeParser;
    public function setDateTimeParser(DateTimeParser $dateTimeParser)
    {
        $this->dateTimeParser = $dateTimeParser;

        return $this;
    }
    public function getDateTimeParser()
    {
        return $this->dateTimeParser;
    }

    /**
     * intermediate object, parsed from Reader
     *
     * @var Node
     */
    protected $card;
    public function setCard(Node $card)
    {
        $this->card = $card;

        return $this;
    }
    public function getCard()
    {
        return $this->card;
    }

    /**
     * vcard intermediate parser
     *
     * @var Reader
     */
    protected $reader;
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;

        return $this;
    }
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Vcard entity
     *
     * @var Entity\Vcard
     */
    protected $vcard;
    public function setVcard(Entity\Vcard $vcard)
    {
        $this->vcard = $vcard;

        return $this;
    }
    public function getVcard()
    {
        return $this->vcard;
    }

    /**
     * main method: export a vcard
     *
     * @param  string       $vcardStr vcard source string
     * @return Entity\Vcard
     */
    public function export($vcardStr)
    {
        // not yet implemented
        // skeleton from importer
        /*
        ArgValidator::assert($vcardStr, 'string');

        $vcard = new Entity\Vcard;
        $this
            ->setCard(
                $this->parseSource(
                    $this->normalizeSource($vcardStr)))
            ->setVcard($vcard)
            ->exportSource()
            ->exportSource()
            ->exportKind()
            ->exportFormattedName()
            ->exportName()
            ->exportNickname()
            ->exportPhoto()
            ->exportBirthday()
            ->exportAnniversary()
            ->exportGender()
            ->exportAddress()
            ->exportPhone()
            ->exportEmail()
            ->exportIm()
            ->exportLanguage()
            ->exportTimezone()
            ->exportGeo()
            ->exportTitle()
            ->exportRole()
            ->exportLogo()
            ->exportOrg()
            ->exportMember()
            ->exportRelation()
            ->exportTag()
            ->exportNote()
            ->exportSound()
            ->exportUid()
            ->exportUrl()
            ->exportPublicKey()
            ->exportFreebusy()
            ->exportCalendar()
            ->exportCalendarRequest();

        return $vcard;
         */
    }

    /**
     * create an intermediate vcard object
     *
     * @return Node
     */
    public function createCard()
    {
        $vcardStr = "BEGIN:VCARD\nEND:VCARD";
        $card = $this->getReader()->read($vcardStr);
        $card->VERSION = Repository\Vcard::VERSION;

        return $card;
    }

    /**
     * normalize vcard source string after parsing
     *
     * @param  string $vcardStr vcard source string
     * @return string
     */
    public function normalizeSource($vcardStr)
    {
        ArgValidator::assert($vcardStr, 'string');

        /* convert chars outside ASCII range to <U+xxxx> expression */
        if (preg_match_all('/[^\x{0000}-\x{007F}]/u', $vcardStr, $matches)) {
            $conversion = array();
            foreach (array_unique($matches[0]) as $char) {
                $conversion[$char] =
                    '<U+' . strtoupper(dechex(Utf8::uord($char))) . '>';
            }
            $vcardStr = strtr($vcardStr, $conversion);
        }

        /* workaround to enable comma-separated values */
        $vcardStr = strtr($vcardStr, array(self::JOINCHAR => ','));

        return $vcardStr;
    }

    /**
     * parse vcard source into intermediate object
     *
     * @param  string    $vcardStr vcard source string
     * @return Node|null
     */
    public function parseSource($vcardStr)
    {
        // not yet implemented
        // skeleton from importer
        /*
        ArgValidator::assert($vcardStr, 'string');

        try {
            $card = $this->getReader()->read($vcardStr);
        } catch (ParseException $e) {
            return null;
        }

        return $card;
         */
    }

    /**
     * helper function to export common parameters
     *
     * @param  Property     $entitySrc
     * @return Entity\Param
     */
    public function exportParam(Property $property)
    {
        // not yet implemented
        // skeleton from importer
        /*
        static $paramMap = array(
            'ALTID'     => 'AltId',
            'GEO'       => 'Geo',
            'LABEL'     => 'Label',
            'LANGUAGE'  => 'Language',
            'MEDIATYPE' => 'MediaType',
            'PREF'      => 'Pref',
            'SORT-AS'   => 'SortAs',
            'TZ'        => 'Timezone',
        );

        static $typeRepo;
        static $typeMap = array();
        static $valueTypeRepo;
        static $valueTypeMap = array();

        // cannot use static, otherwise persist() won't work
        $em = $this->getEm();

        if (empty($typeRepo) || empty($valueTypeRepo)) {
            $typeRepo =
                $em->getRepository('Heartsentwined\Vcard\Entity\Type');
            $valueTypeRepo =
                $em->getRepository('Heartsentwined\Vcard\Entity\ParamValueType');
        }

        $param = new Entity\Param;
        $em->persist($param);

        foreach ($paramMap as $paramName => $propertyName) {
            $func = "set$propertyName";
            $param->$func((string) $property[$paramName]);
        }

        $value = (string) $property['VALUE'];
        if (!empty($value)) {
            if (isset($valueTypeMap[$value])) {
                $valueType = $valueTypeMap[$value];
            } elseif (!$valueType = $valueTypeRepo
                ->findOneBy(array('value' => $value))) {
                $valueType = new Entity\ParamValueType;
                $em->persist($valueType);
                $valueType->setValue($value);
                $valueTypeMap[$value] = $valueType;
            }
            $param->setValueType($valueType);
        }

        if (isset($property['TYPE']) && count($property['TYPE'])) {
            foreach ($property['TYPE'] as $eachType) {
                foreach (explode(',', $eachType) as $typeSrc) {
                    if ($typeSrc === '') continue;

                    if (isset($typeMap[$typeSrc])) {
                        $type = $typeMap[$typeSrc];
                    } elseif (!$type = $typeRepo
                        ->findOneBy(array('value' => $typeSrc))) {
                        $type = new Entity\Type;
                        $em->persist($type);
                        $type->setValue($typeSrc);
                        $typeMap[$typeSrc] = $type;
                    }
                    $param->addType($type);
                }
            }
        }

        return $param;
         */
    }

    /**
     * multiple instances properties
     *
     * @param  Property   $property
     * @param  string     $entityClass
     * @return Entity\*[]
     */
    public function exportMultiple(Property $property, $entityClass)
    {
        // not yet implemented
        // skeleton from importer
        /*
        ArgValidator::assertClass($entityClass);

        $em = $this->getEm();
        $entities = array();
        foreach ($property as $eachProperty) {
            $entity = new $entityClass;
            $em->persist($entity);
            $entity
                ->setValue((string) $eachProperty)
                ->setParam($this->exportParam($eachProperty));
            $entities[] = $entity;
        }

        return $entities;
         */
    }

    /**
     * single instance properties
     *
     * @param  Property $property
     * @param  string   $entityClass
     * @return Entity\*
     */
    public function exportSingle(Property $property, $entityClass)
    {
        // not yet implemented
        // skeleton from importer
        /*
        ArgValidator::assertClass($entityClass);

        //get first instance
        foreach ($property as $property) { break; }

        $entity = new $entityClass;
        $this->getEm()->persist($entity);
        $entity
            ->setValue((string) $property)
            ->setParam($this->exportParam($property));

        return $entity;
         */
    }

    /**
     * multiple instances properties, with comma-separated values + type param
     *
     * @param  Property   $property
     * @param  string     $entityClass
     * @return Entity\*[]
     */
    public function exportMultipleWithType(Property $property, $entityClass)
    {
        // not yet implemented
        // skeleton from importer
        /*
        ArgValidator::assertClass($entityClass);

        $em             = $this->getEm();
        $vcard          = $this->getVcard();

        $entityArray    = explode('\\', $entityClass);
        $entityName     = end($entityArray);
        $entityRepo     = $em->getRepository($entityClass);
        $typeClass      = "{$entityClass}Type";
        $typeRepo       = $em->getRepository($typeClass);
        $typeSetter     = "add{$entityName}Type";
        static $typeMap = array();

        $entities       = array();
        foreach ($property as $eachProperty) {
            $entity = new $entityClass;
            $em->persist($entity);
            $entities[] = $entity;
            $entity
                ->setValue((string) $eachProperty)
                ->setParam($this->exportParam($eachProperty));
            foreach ($eachProperty['TYPE'] as $eachType) {
                foreach (explode(',', $eachType) as $typeSrc) {
                    if ($typeSrc === '') continue;

                    if (isset($typeMap[$typeClass][$typeSrc])) {
                        $type = $typeMap[$typeClass][$typeSrc];
                    } elseif (!$type = $typeRepo
                        ->findOneBy(array('value' => $typeSrc))) {
                        $type = new $typeClass;
                        $em->persist($type);
                        $type->setValue($typeSrc);
                        $typeMap[$typeClass][$typeSrc] = $type;
                    }
                    $entity->$typeSetter($type);
                }
            }
        }

        return $entities;
         */
    }

    /**
     * single instance properties, with datetime / text values
     *
     * @param  Property $property
     * @param  string   $entityClass
     * @return Entity\*
     */
    public function exportSingleDatetime(Property $property, $entityClass)
    {
        // not yet implemented
        // skeleton from importer
        /*
        ArgValidator::assertClass($entityClass);

        $em             = $this->getEm();
        $vcard          = $this->getVcard();
        $dateTimeParser = $this->getDateTimeParser();
        $entityArray    = explode('\\', $entityClass);
        $entityName     = end($entityArray);

        //get first instance
        foreach ($property as $property) { break; }

        $entity = new $entityClass;
        $em->persist($entity);
        $dateTimeText = new Entity\DateTimeText;
        $em->persist($dateTimeText);
        $entity->setValue($dateTimeText);

        if ($property['VALUE'] == 'text') {
            $dateTimeText
                ->setFormat(Repository\DateTimeText::TEXT)
                ->setValueText((string) $property);
        } else {
            $dt = $dateTimeParser->parseDateTime((string) $property);
            if ($timestamp = $dateTimeParser->createTimestamp(
                $dt['year'], $dt['month'], $dt['day'],
                $dt['hour'], $dt['minute'], $dt['second'], $dt['timezone'])) {
                $dateTimeText
                    ->setFormat(Repository\DateTimeText::FULL)
                    ->setValue(
                        \DateTime::createFromFormat('U', $timestamp));
            } else {
                $dateTimeText
                    ->setFormat(Repository\DateTimeText::PARTIAL);
            }
            $dateTimeText
                ->setYear($dt['year'])
                ->setMonth($dt['month'])
                ->setDay($dt['day'])
                ->setHour($dt['hour'])
                ->setMinute($dt['minute'])
                ->setSecond($dt['second'])
                ->setTimezone($dt['timezone']);
        }

        return $entity;
         */
    }

    /**
     * SOURCE - Source
     *
     * @return self
     */
    public function exportSource()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->SOURCE === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->SOURCE,
            'Heartsentwined\Vcard\Entity\Source')
        as $source) {
            $vcard->addSource($source);
        }

        return $this;
         */
    }

    /**
     * KIND - Kind
     *
     * @return self
     */
    public function exportKind()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $em = $this->getEm();
        $card = $this->getCard();

        $kindValueSrc = (string) $card->KIND
            ? (string) $card->KIND : Repository\KindValue::DEF;
        if (!$kindValue = $em
            ->getRepository('Heartsentwined\Vcard\Entity\KindValue')
            ->findOneBy(array('value' => $kindValueSrc))) {
            $kindValue = new Entity\KindValue;
            $em->persist($kindValue);
            $kindValue->setValue($kindValueSrc);
        }
        $kind = new Entity\Kind;
        $em->persist($kind);
        $kind->setValue($kindValue);
        if ((string) $card->KIND !== '') {
            $kind->setParam($this->exportParam($card->KIND));
        } else {
            $param = new Entity\Param;
            $em->persist($param);
            $kind->setParam($param);
        }
        $this->getVcard()->setKind($kind);

        return $this;
         */
    }

    /**
     * FN - FormattedName
     *
     * @return self
     */
    public function exportFormattedName()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->FN === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->FN,
            'Heartsentwined\Vcard\Entity\FormattedName')
        as $formattedName) {
            $vcard->addFormattedName($formattedName);
        }

        return $this;
         */
    }

    /**
     * N - Name
     *
     * @return self
     */
    public function exportName()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->N === '') return $this;

        $em = $this->getEm();
        $vcard = $this->getVcard();

        static $components = array(
            'FamilyName',
            'GivenName',
            'AdditionalName',
            'Prefix',
            'Suffix'
        );

        foreach ($card->N as $nameSrc) {
            $name = new Entity\Name;
            $em->persist($name);
            $vcard->addName($name);
            $name->setParam($this->exportParam($nameSrc));

            foreach (explode(';', $nameSrc) as $key => $componentSrc) {
                if (empty($componentSrc)) continue;

                foreach (explode(',', $componentSrc) as $valueSrc) {
                    $componentClass =
                        "Heartsentwined\\Vcard\Entity\\{$components[$key]}";
                    $component = new $componentClass;
                    $em->persist($component);
                    $component->setValue($valueSrc);
                    $setter = "add{$components[$key]}";
                    $name->$setter($component);
                }
            }
        }

        return $this;
         */
    }

    /**
     * NICKNAME - Nickname
     *
     * @return self
     */
    public function exportNickname()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->NICKNAME === '') return $this;

        $em = $this->getEm();
        $vcard = $this->getVcard();

        foreach ($card->NICKNAME as $nicknameSrc) {
            $nickname = new Entity\Nickname;
            $em->persist($nickname);
            $vcard->addNickname($nickname);
            $nickname->setParam($this->exportParam($nicknameSrc));

            foreach (explode(',', $nicknameSrc) as $valueSrc) {
                $nicknameValue = new Entity\NicknameValue;
                $em->persist($nicknameValue);
                $nickname->addValue($nicknameValue);
                $nicknameValue->setValue($valueSrc);
            }
        }

        return $this;
         */
    }

    /**
     * PHOTO - Photo
     *
     * @return self
     */
    public function exportPhoto()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->PHOTO === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->PHOTO,
            'Heartsentwined\Vcard\Entity\Photo')
        as $photo) {
            $vcard->addPhoto($photo);
        }

        return $this;
         */
    }

    /**
     * BDAY - Birthday
     *
     * @return self
     */
    public function exportBirthday()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->BDAY === '') return $this;

        $vcard = $this->getVcard()->setBirthday($this->exportSingleDatetime(
            $card->BDAY, 'Heartsentwined\Vcard\Entity\Birthday'));

        return $this;
         */
    }

    /**
     * ANNIVERSARY - Anniversary
     *
     * @return self
     */
    public function exportAnniversary()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->ANNIVERSARY === '') return $this;

        $vcard = $this->getVcard()->setAnniversary($this->exportSingleDatetime(
            $card->ANNIVERSARY, 'Heartsentwined\Vcard\Entity\Anniversary'));

        return $this;
         */
    }

    /**
     * GENDER - Gender
     *
     * @return self
     */
    public function exportGender()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $em = $this->getEm();
        $card = $this->getCard();
        $genderValueRepo =
            $em->getRepository('Heartsentwined\Vcard\Entity\GenderValue');

        $genderProperty = $card->GENDER;
        $genderValueSrc = (string) $card->GENDER;
        if ($genderValueSrc === '') {
            $genderProperty = $card->{'X-GENDER'};
            switch (strtolower($card->{'X-GENDER'})) { // non-standard form
                case 'male':
                case 'm':
                    $genderValueSrc = Repository\GenderValue::M;
                    break;
                case 'female':
                case 'f':
                    $genderValueSrc = Repository\GenderValue::F;
                    break;
            }
        }

        if ($genderValueSrc === '') return $this;

        if (!strpos($genderValueSrc, ';')) {
            $genderValueSrc .= ';';
        }
        list($value, $comment) = explode(';', $genderValueSrc);
        $refl = new \ReflectionClass($genderValueRepo);
        if (!in_array($value, $refl->getConstants())) {
            $value = '';
        }
        $gender = new Entity\Gender;
        $em->persist($gender);
        $gender
            ->setComment($comment)
            ->setParam($this->exportParam($genderProperty));
        if ($genderValue = $genderValueRepo
            ->findOneBy(array('value' => $value))) {
            $gender->setValue($genderValue);
        }
        $this->getVcard()->setGender($gender);

        return $this;
         */
    }

    /**
     * ADR - Address
     *
     * @return self
     */
    public function exportAddress()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->ADR === '') return $this;

        $em = $this->getEm();
        $vcard = $this->getVcard();

        foreach ($card->ADR as $addressSrc) {
            $address = new Entity\Address;
            $em->persist($address);
            $vcard->addAddress($address);
            $address->setParam($this->exportParam($addressSrc));

            // replace literal \n's in source string with new line
            $addressSrc = strtr($addressSrc, array('\n' => "\n"));
            list($poBox, $ext, $street, $locality, $region,
                $postalCode, $country) = explode(';', $addressSrc);

            $streetParts = array($poBox, $ext, $street);
            foreach ($streetParts as $key => $streetPart) {
                if (empty($streetPart)) {
                    unset($streetParts[$key]);
                }
            }
            $assembledStreet = implode("\n", $streetParts);

            $address
                ->setStreet($assembledStreet)
                ->setLocality($locality)
                ->setRegion($region)
                ->setPostalCode($postalCode)
                ->setCountry($country);
        }

        return $this;
         */
    }

    /**
     * TEL - Phone
     *
     * @return self
     */
    public function exportPhone()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->TEL === '') return $this;

        $vcard = $this->getVcard();
        foreach ($card->TEL as $property) {
            if ((string) $property['TYPE'] === '') {
                $property['TYPE'] = Repository\PhoneType::DEF;
            }
        }
        foreach ($this->exportMultipleWithType($card->TEL,
            'Heartsentwined\Vcard\Entity\Phone')
        as $phone) {
            $vcard->addPhone($phone);
        }

        return $this;
         */
    }

    /**
     * EMAIL - Email
     *
     * @return self
     */
    public function exportEmail()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->EMAIL === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->EMAIL,
            'Heartsentwined\Vcard\Entity\Email')
        as $email) {
            $vcard->addEmail($email);
        }

        return $this;
         */
    }

    /**
     * IMPP / X-AIM / X-SKYPE / etc - Im
     *
     * @return self
     */
    public function exportIm()
    {
        // not yet implemented
        // skeleton from importer
        /*
        // non-standard properties
        static $propertyProtocol = array(
            'IMPP'              => '',
            'X-AIM'             => Repository\ImProtocol::AIM,
            'X-GADUGADU'        => Repository\ImProtocol::GADUGADU,
            'X-GROUPWISE'       => Repository\ImProtocol::GROUPWISE,
            'X-ICQ'             => Repository\ImProtocol::ICQ,
            'X-JABBER'          => Repository\ImProtocol::JABBER,
            'X-MSN'             => Repository\ImProtocol::MSN,
            'X-SKYPE'           => Repository\ImProtocol::SKYPE,
            'X-SKYPE-USERNAME'  => Repository\ImProtocol::SKYPE,
            'X-TWITTER'         => Repository\ImProtocol::TWITTER,
            'X-YAHOO'           => Repository\ImProtocol::YAHOO,
        );
        static $uriProtocol = array(
            'xmpp'      => Repository\ImProtocol::JABBER,
            'aim'       => Repository\ImProtocol::AIM,
            'callto'    => Repository\ImProtocol::SKYPE,
            'gg'        => Repository\ImProtocol::GADUGADU,
            'gtalk'     => Repository\ImProtocol::JABBER,
            'msnim'     => Repository\ImProtocol::MSN,
            'skype'     => Repository\ImProtocol::SKYPE,
            'ymsgr'     => Repository\ImProtocol::YAHOO,
            'im'        => Repository\ImProtocol::JABBER,
        );
        static $imProtocolMap = array();
        $em             = $this->getEm();
        $imProtocolRepo = $em
            ->getRepository('Heartsentwined\Vcard\Entity\ImProtocol');
        $card           = $this->getCard();
        $vcard          = $this->getVcard();
        foreach ($propertyProtocol as $property => $protocolSrc) {
            if ((string) $card->$property === '') continue;

            foreach ($card->$property as $imSrc) {
                $im = new Entity\Im;
                $em->persist($im);
                $im
                    ->setValue((string) $imSrc)
                    ->setParam($this->exportParam($imSrc));
                $vcard->addIm($im);

                // detect protocol from URI
                if ($property === 'IMPP') {
                    foreach ($uriProtocol as $uri => $protocol) {
                        if (strpos($imSrc, "$uri:") !== false) {
                            $protocolSrc = $protocol;
                            $im->setIsUri(true);
                            break;
                        }
                    }
                }

                if (isset($imProtocolMap[$protocolSrc])) {
                    $imProtocol = $imProtocolMap[$protocolSrc];
                    $im->setProtocol($imProtocol);
                } elseif ($imProtocol = $imProtocolRepo
                    ->findOneBy(array('value' => $protocolSrc))) {
                    $imProtocolMap[$protocolSrc] = $imProtocol;
                    $im->setProtocol($imProtocol);
                }
            }
        }

        return $this;
         */
    }

    /**
     * LANG - Language
     *
     * @return self
     */
    public function exportLanguage()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->LANG === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->LANG,
            'Heartsentwined\Vcard\Entity\Language')
        as $language) {
            $vcard->addLanguage($language);
        }

        return $this;
         */
    }

    /**
     * TZ - Timezone
     *
     * @return self
     */
    public function exportTimezone()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->TZ === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->TZ,
            'Heartsentwined\Vcard\Entity\Timezone')
        as $timezone) {
            $vcard->addTimezone($timezone);
        }

        return $this;
         */
    }

    /**
     * GEO - Geo
     *
     * @return self
     */
    public function exportGeo()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->GEO === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->GEO,
            'Heartsentwined\Vcard\Entity\Geo')
        as $geo) {
            $vcard->addGeo($geo);
        }

        return $this;
         */
    }

    /**
     * TITLE - Title
     *
     * @return self
     */
    public function exportTitle()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->TITLE === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->TITLE,
            'Heartsentwined\Vcard\Entity\Title')
        as $title) {
            $vcard->addTitle($title);
        }

        return $this;
         */
    }

    /**
     * ROLE - Role
     *
     * @return self
     */
    public function exportRole()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->ROLE === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->ROLE,
            'Heartsentwined\Vcard\Entity\Role')
        as $role) {
            $vcard->addRole($role);
        }

        return $this;
         */
    }

    /**
     * LOGO - Logo
     *
     * @return self
     */
    public function exportLogo()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->LOGO === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->LOGO,
            'Heartsentwined\Vcard\Entity\Logo')
        as $logo) {
            $vcard->addLogo($logo);
        }

        return $this;
         */
    }

    /**
     * ORG - Org
     *
     * @return self
     */
    public function exportOrg()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->ORG === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->ORG,
            'Heartsentwined\Vcard\Entity\Org')
        as $org) {
            $vcard->addOrg($org);
        }

        return $this;
         */
    }

    /**
     * MEMBER - Member
     *
     * @return self
     */
    public function exportMember()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->MEMBER === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->MEMBER,
            'Heartsentwined\Vcard\Entity\Member')
        as $member) {
            $vcard->addMember($member);
        }

        return $this;
         */
    }

    /**
     * RELATED - Relation
     *
     * @return self
     */
    public function exportRelation()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->RELATED === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultipleWithType($card->RELATED,
            'Heartsentwined\Vcard\Entity\Relation')
        as $relation) {
            $vcard->addRelation($relation);
        }

        return $this;
         */
    }

    /**
     * CATEGORIES - Tag
     *
     * @return self
     */
    public function exportTag()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->CATEGORIES === '') return $this;

        $em    = $this->getEm();
        $vcard = $this->getVcard();
        $tagValueRepo =
            $em->getRepository('Heartsentwined\Vcard\Entity\TagValue');
        static $tagValueMap = array();

        foreach ($card->CATEGORIES as $tagSrc) {
            $tag = new Entity\Tag;
            $em->persist($tag);
            $vcard->addTag($tag);
            $tag->setParam($this->exportParam($tagSrc));

            foreach (explode(',', $tagSrc) as $valueSrc) {
                if ($valueSrc === '') continue;

                if (isset($tagValueMap[$valueSrc])) {
                    $tagValue = $tagValueMap[$valueSrc];
                } elseif (!$tagValue = $tagValueRepo
                    ->findOneBy(array('value' => $valueSrc))) {
                    $tagValue = new Entity\TagValue;
                    $em->persist($tagValue);
                    $tagValue->setValue((string) $valueSrc);
                    $tagValueMap[$valueSrc] = $tagValue;
                }
                $tag->addValue($tagValue);
            }
        }

        return $this;
         */
    }

    /**
     * NOTE - Note
     *
     * @return self
     */
    public function exportNote()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->NOTE === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->NOTE,
            'Heartsentwined\Vcard\Entity\Note')
        as $note) {
            $vcard->addNote($note);
        }

        return $this;
         */
    }

    /**
     * SOUND - Sound
     *
     * @return self
     */
    public function exportSound()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->SOUND === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->SOUND,
            'Heartsentwined\Vcard\Entity\Sound')
        as $sound) {
            $vcard->addSound($sound);
        }

        return $this;
         */
    }

    /**
     * UID - Uid
     *
     * @return self
     */
    public function exportUid()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->UID === '') return $this;

        $this->getVcard()->setUid($this->exportSingle($card->UID,
            'Heartsentwined\Vcard\Entity\Uid'));

        return $this;
         */
    }

    /**
     * URL - Url
     *
     * @return self
     */
    public function exportUrl()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->URL === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->URL,
            'Heartsentwined\Vcard\Entity\Url')
        as $url) {
            $vcard->addUrl($url);
        }

        return $this;
         */
    }

    /**
     * KEY - PublicKey
     *
     * @return self
     */
    public function exportPublicKey()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->KEY === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->KEY,
            'Heartsentwined\Vcard\Entity\PublicKey')
        as $publicKey) {
            $vcard->addPublicKey($publicKey);
        }

        return $this;
         */
    }

    /**
     * FBURL - Freebusy
     *
     * @return self
     */
    public function exportFreebusy()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->FBURL === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->FBURL,
            'Heartsentwined\Vcard\Entity\Freebusy')
        as $freebusy) {
            $vcard->addFreebusy($freebusy);
        }

        return $this;
         */
    }

    /**
     * CALURI - Calendar
     *
     * @return self
     */
    public function exportCalendar()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->CALURI === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->CALURI,
            'Heartsentwined\Vcard\Entity\Calendar')
        as $calendar) {
            $vcard->addCalendar($calendar);
        }

        return $this;
         */
    }

    /**
     * CALADRURI - CalendarRequest
     *
     * @return self
     */
    public function exportCalendarRequest()
    {
        // not yet implemented
        // skeleton from importer
        /*
        $card = $this->getCard();
        if ((string) $card->CALADRURI === '') return $this;

        $vcard = $this->getVcard();
        foreach ($this->exportMultiple($card->CALADRURI,
            'Heartsentwined\Vcard\Entity\CalendarRequest')
        as $calendarRequest) {
            $vcard->addCalendarRequest($calendarRequest);
        }

        return $this;
         */
    }
}
