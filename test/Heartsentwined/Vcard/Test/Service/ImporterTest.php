<?php
namespace Heartsentwined\Vcard\Test\Service;

use Heartsentwined\Vcard\Service\Importer;
use Heartsentwined\Phpunit\Testcase\Doctrine as DoctrineTestcase;

class ImporterTest extends DoctrineTestcase
{
    public function setUp()
    {
        $this
            ->setBootstrap(__DIR__ . '/../../../../../bootstrap.php')
            ->setEmAlias('doctrine.entitymanager.orm_default')
            ->setTmpDir('tmp');
        parent::setUp();

        $this->importer = $this->sm->get('vcard-importer')
            ->setReader($this->sm->get('Sabre\VObject\Reader'));
    }

    public function tearDown()
    {
        $this->importer = null;
        parent::tearDown();
    }

    public function testNormalize()
    {
        $this->assertSame('', $this->importer->normalize(''));
        $this->assertSame("BEGIN:\né",
            $this->importer->normalize("BEGIN:\n<u+00e9>"));
        $this->assertSame("BEGIN:\n e",
            $this->importer->normalize("BEGIN:\n   e"));
    }

    public function testParse()
    {
        $this->assertInstanceOf(
            'Sabre\VObject\Node',
            $this->importer->parse(<<<STR
BEGIN:VCARD
VERSION:4.0
END:VCARD
STR
            ));
    }
}