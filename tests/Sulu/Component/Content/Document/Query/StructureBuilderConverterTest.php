<?php

namespace Sulu\Component\Content\Document\Query;

use PHPCR\SessionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sulu\Component\DocumentManager\MetadataFactoryInterface;
use Sulu\Component\Content\Document\Query\StructureBuilderConverter;
use PHPCR\Query\QueryManagerInterface;
use PHPCR\WorkspaceInterface;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use Sulu\Component\DocumentManager\Metadata;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Content\Document\Behavior\LocalizedStructureBehavior;
use Sulu\Component\Content\Document\Subscriber\StructureSubscriber;

class StructureBuilderConverterTest extends SuluTestCase
{
    private $session;
    private $dispatcher;
    private $metadataFactory;
    private $converter;

    public function setUp()
    {
        $this->initPhpcr();

        $this->session = $this->getContainer()->get('doctrine_phpcr.default_session');
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->metadataFactory = $this->prophesize(MetadataFactoryInterface::class);
        $this->metadata = $this->prophesize(Metadata::class);
        $this->encoder = $this->prophesize(PropertyEncoder::class);
        $this->webspaceManager = $this->prophesize(WebspaceManagerInterface::class);
        $this->localization1 = $this->prophesize(Localization::class);
        $this->localization2 = $this->prophesize(Localization::class);
        $this->localization1->getLocalization()->willReturn('de');
        $this->localization2->getLocalization()->willReturn('fr');
        $this->webspaceManager->getAllLocalizations()->willReturn(array(
            $this->localization1,
            $this->localization2
        ));
        $this->localizedDocument = $this->prophesize(LocalizedStructureBehavior::class);

        $this->converter = new StructureBuilderConverter(
            $this->session,
            $this->dispatcher->reveal(),
            $this->metadataFactory->reveal(),
            $this->encoder->reveal(),
            $this->webspaceManager->reveal()
        );
    }

    /**
     * It should parse a source document with a structure in all languages
     */
    public function testDocumentWithStructure()
    {
        $alias = 'full';

        $builder = $this->createBuilder();
        $builder->from()->document('full#overview', 'f');

        $this->metadataFactory->hasAlias($alias)->willReturn(true);
        $this->metadataFactory->getMetadataForAlias($alias)->willReturn(
            $this->metadata->reveal()
        );
        $this->metadata->getPhpcrType()->willReturn('hello');
        $this->metadata->getReflection()->willReturn(new \ReflectionClass(get_class($this->localizedDocument->reveal())));
        $this->encoder->localizedSystemName(StructureSubscriber::STRUCTURE_TYPE_FIELD, 'fr')->willReturn('lsys:fr-structype');
        $this->encoder->localizedSystemName(StructureSubscriber::STRUCTURE_TYPE_FIELD, 'de')->willReturn('lsys:de-structype');

        $query = $builder->getQuery();
        $sql2 = $query->getPhpcrQuery()->getStatement();
        $this->assertEquals('SELECT * FROM [nt:unstructured] AS f WHERE (f.[jcr:mixinTypes] = \'hello\' AND (f.[lsys:de-structype] = \'overview\' OR f.[lsys:fr-structype] = \'overview\'))', $sql2);
    }

    /**
     * It should parse a source document with a structure in a specified locale
     */
    public function testDocumentWithStructureSpecifiedLocale()
    {
        $alias = 'full';

        $builder = $this->createBuilder();
        $builder->from()->document('full#overview', 'f');
        $builder->setLocale('fr');

        $this->metadataFactory->hasAlias($alias)->willReturn(true);
        $this->metadataFactory->getMetadataForAlias($alias)->willReturn(
            $this->metadata->reveal()
        );
        $this->metadata->getPhpcrType()->willReturn('hello');
        $this->metadata->getReflection()->willReturn(new \ReflectionClass(get_class($this->localizedDocument->reveal())));
        $this->encoder->localizedSystemName(StructureSubscriber::STRUCTURE_TYPE_FIELD, 'fr')->willReturn('lsys:fr-structype');

        $query = $builder->getQuery();
        $sql2 = $query->getPhpcrQuery()->getStatement();
        $this->assertEquals('SELECT * FROM [nt:unstructured] AS f WHERE (f.[jcr:mixinTypes] = \'hello\' AND f.[lsys:fr-structype] = \'overview\')', $sql2);
    }

    /**
     * It use a structure property in a criteria
     */
    public function testStructureProeprtyCriteria()
    {
        $alias = 'full';

        $builder = $this->createBuilder();
        $builder->from()->document('full#overview', 'f');
        $builder->setLocale('fr');
        $builder->where()->eq()->field('f.#title')->literal('foobar');

        $this->metadataFactory->hasAlias($alias)->willReturn(true);
        $this->metadataFactory->getMetadataForAlias($alias)->willReturn(
            $this->metadata->reveal()
        );
        $this->metadata->getPhpcrType()->willReturn('hello');
        $this->metadata->getReflection()->willReturn(new \ReflectionClass(get_class($this->localizedDocument->reveal())));
        $this->encoder->localizedSystemName(StructureSubscriber::STRUCTURE_TYPE_FIELD, 'fr')->willReturn('lsys:fr-structype');
    }

    private function createBuilder()
    {
        $builder =  new QueryBuilder();
        $builder->setConverter($this->converter);

        return $builder;
    }
}
