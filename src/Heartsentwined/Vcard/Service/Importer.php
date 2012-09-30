<?php
namespace Heartsentwined\Vcard\Service;

use Doctrine\ORM\EntityManager;
use Heartsentwined\ArgValidator\ArgValidator;
use Heartsentwined\Utf8\Utf8;
use Heartsentwined\Vcard\Entity;
use Sabre\VObject\Node;
use Sabre\VObject\ParseException;
use Sabre\VObject\Property;
use Sabre\VObject\Reader;

class Importer
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
     * vcard source string
     *
     * @var string
     */
    protected $vcardStr;
    public function setVcardStr($vcardStr)
    {
        ArgValidator::assert($vcardStr, 'string');
        $this->vcardStr = $vcardStr;

        return $this;
    }
    public function getVcardStr()
    {
        return $this->vcardStr;
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
     * normalize vcard source for parsing
     *
     * @param  string $vcardStr
     * @return string
     */
    public function normalizeSource($vcardStr)
    {
        ArgValidator::assert($vcardStr, 'string');

        if (strpos($vcardStr, 'BEGIN:') !== 0) {
            return '';
        }

        /* convert <U+xxxx> to characters */
        if (preg_match_all('/<[uU]\+([0-9A-Fa-f]{4})>/',
            $vcardStr, $matches, PREG_SET_ORDER)) {
            $conversions = array();
            foreach ($matches as $match) {
                $conversions[$match[0]] = Utf8::uchr(hexdec($match[1]));
            }
            $vcardStr = strtr($vcardStr, $conversions);
        }

        /* strip extra leading spaces */
        $vcardStr = preg_replace('/\n[\s]+/', "\n ", $vcardStr);

        return $vcardStr;
    }

    /**
     * parse vcard source into intermediate object
     *
     * @return Node|null
     */
    public function parseSource($vcardStr)
    {
        ArgValidator::assert($vcardStr, 'string');

        try {
            $card = $this->getReader()->read($vcardStr);
        } catch (ParseException $e) {
            return null;
        }

        return $card;
    }

    /**
     * helper function to import common parameters
     *
     * @param  Property     $entitySrc
     * @return Entity\Param
     */
    public function importParam(Property $property)
    {
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
    }

    /**
     * multiple instances properties
     *
     * @param  Property   $property
     * @param  string     $entityClass
     * @return Entity\*[]
     */
    public function importMultiple(Property $property, $entityClass)
    {
        ArgValidator::assertClass($entityClass);

        $em = $this->getEm();
        $entities = array();
        foreach ($property as $eachProperty) {
            $entity = new $entityClass;
            $em->persist($entity);
            $entity
                ->setValue((string) $eachProperty)
                ->setParam($this->importParam($eachProperty));
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * single instance properties
     *
     * @param  Property $property
     * @param  string   $entityClass
     * @return Entity\*
     */
    public function importSingle(Property $property, $entityClass)
    {
        ArgValidator::assertClass($entityClass);

        //get first instance
        foreach ($property as $property) { break; }

        $entity = new $entityClass;
        $this->getEm()->persist($entity);
        $entity
            ->setValue((string) $property)
            ->setParam($this->importParam($property));

        return $entity;
    }

    /**
     * SOURCE - Source
     *
     * @return self
     */
    public function importSource()
    {
        $vcard = $this->getVcard();
        if (($sourceSrc = $this->getCard()->SOURCE) && count($sourceSrc)) {
            foreach ($this->importMultiple(
                $sourceSrc, 'Heartsentwined\Vcard\Entity\Source')
            as $source) {
                $vcard->addSource($source);
            }
        }

        return $this;
    }

    /**
     * KIND - Kind
     *
     * @return self
     */
    public function importKind()
    {
        // not yet implemented
    }

    /**
     * FN - FormattedName
     *
     * @return self
     */
    public function importFn()
    {
        // not yet implemented
    }

    /**
     * N - Name
     *
     * @return self
     */
    public function importN()
    {
        // not yet implemented
    }

    /**
     * NICKNAME - Nickname
     *
     * @return self
     */
    public function importNickname()
    {
        // not yet implemented
    }

    /**
     * PHOTO - Photo
     *
     * @return self
     */
    public function importPhoto()
    {
        // not yet implemented
    }

    /**
     * BDAY - Birthday
     *
     * @return self
     */
    public function importBday()
    {
        // not yet implemented
    }

    /**
     * ANNIVERSARY - Anniversary
     *
     * @return self
     */
    public function importAnniversary()
    {
        // not yet implemented
    }

    /**
     * GENDER - Gender
     *
     * @return self
     */
    public function importGender()
    {
        // not yet implemented
    }

    /**
     * ADR - Address
     *
     * @return self
     */
    public function importAdr()
    {
        // not yet implemented
    }

    /**
     * TEL - Phone
     *
     * @return self
     */
    public function importTel()
    {
        // not yet implemented
    }

    /**
     * EMAIL - Email
     *
     * @return self
     */
    public function importEmail()
    {
        // not yet implemented
    }

    /**
     * IMPP / X-AIM / X-SKYPE / etc - Im
     *
     * @return self
     */
    public function importImpp()
    {
        // not yet implemented
    }

    /**
     * LANG - Language
     *
     * @return self
     */
    public function importLang()
    {
        // not yet implemented
    }

    /**
     * TZ - Timezone
     *
     * @return self
     */
    public function importTz()
    {
        // not yet implemented
    }

    /**
     * GEO - Geo
     *
     * @return self
     */
    public function importGeo()
    {
        // not yet implemented
    }

    /**
     * TITLE - Title
     *
     * @return self
     */
    public function importTitle()
    {
        // not yet implemented
    }

    /**
     * ROLE - Role
     *
     * @return self
     */
    public function importRole()
    {
        // not yet implemented
    }

    /**
     * LOGO - Logo
     *
     * @return self
     */
    public function importLogo()
    {
        // not yet implemented
    }

    /**
     * ORG - Org
     *
     * @return self
     */
    public function importOrg()
    {
        // not yet implemented
    }

    /**
     * MEMBER - Member
     *
     * @return self
     */
    public function importMember()
    {
        // not yet implemented
    }

    /**
     * RELATED - Relation
     *
     * @return self
     */
    public function importRelated()
    {
        // not yet implemented
    }

    /**
     * CATEGORIES - Tag
     *
     * @return self
     */
    public function importCategories()
    {
        // not yet implemented
    }

    /**
     * NOTE - Note
     *
     * @return self
     */
    public function importNote()
    {
        // not yet implemented
    }

    /**
     * SOUND - Sound
     *
     * @return self
     */
    public function importSound()
    {
        // not yet implemented
    }

    /**
     * UID - Uid
     *
     * @return self
     */
    public function importUid()
    {
        // not yet implemented
    }

    /**
     * URL - Url
     *
     * @return self
     */
    public function importUrl()
    {
        // not yet implemented
    }

    /**
     * KEY - PublicKey
     *
     * @return self
     */
    public function importKey()
    {
        // not yet implemented
    }

    /**
     * FBURL - Freebusy
     *
     * @return self
     */
    public function importFburl()
    {
        // not yet implemented
    }

    /**
     * CALURI - Calendar
     *
     * @return self
     */
    public function importCaladruri()
    {
        // not yet implemented
    }

    /**
     * CALADRURI - CalendarRequest
     *
     * @return self
     */
    public function importCaluri()
    {
        // not yet implemented
    }
}
