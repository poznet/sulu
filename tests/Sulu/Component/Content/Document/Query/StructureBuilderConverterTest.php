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
        $this->propertyEncoder = $this->prophesize(PropertyEncoder::class);
        $this->webspaceManager = $this->prophesize(WebspaceManagerInterface::class);
        $this->localization1 = $this->prophesize(Localization::class);
        $this->localization2 = $this->prophesize(Localization::class);
        $this->localization1->getLocalization()->willReturn('de');
        $this->localization2->getLocalization()->willReturn('fr');
        $this->webspaceManager->getAllLocalizations()->willReturn(array(
            $this->localization1,
            $this->localization2
        ));

        $this->converter = new StructureBuilderConverter(
            $this->session,
            $this->dispatcher->reveal(),
            $this->metadataFactory->reveal(),
            $this->propertyEncoder->reveal(),
            $this->webspaceManager->reveal()
        );
    }

    /**
     * It should parse a source document with a structure
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

        $query = $builder->getQuery();
        var_dump($query->getPhpcrQuery()->getStatement());die();;
    }

    private function createBuilder()
    {
        $builder =  new QueryBuilder();
        $builder->setConverter($this->converter);

        return $builder;
    }
}
