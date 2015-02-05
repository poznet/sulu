<?php

namespace Sulu\Bundle\LocationBundle\Tests\Unit\Content\Types;

use Sulu\Bundle\LocationBundle\Content\Types\LocationContentType;
use Sulu\Component\Content\PropertyParameter;

class LocationContentTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $nodeRepository;
    protected $locationContent;
    protected $mapManager;

    public function setUp()
    {
        $this->nodeRepository = $this->getMock('Sulu\Bundle\ContentBundle\Repository\NodeRepositoryInterface');
        $this->phpcrNode = $this->getMock('PHPCR\NodeInterface');
        $this->suluProperty = $this->getMock('Sulu\Component\Content\PropertyInterface');
        $this->mapManager = $this->getMock('Sulu\Bundle\LocationBundle\Map\MapManager');
        $this->locationContent = new LocationContentType(
            $this->nodeRepository,
            'Foo:bar.html.twig',
            $this->mapManager,
            'some_geolocator'
        );
    }

    protected function initReadTest($data)
    {
        $this->suluProperty->expects($this->once())
            ->method('setValue')
            ->with($data);
    }

    public function provideRead()
    {
        return array(
            array(
                array('foo_bar' => 'bar_foo'),
            )
        );
    }

    /**
     * @dataProvider provideRead
     */
    public function testRead($data)
    {
        $this->initReadTest($data);

        $this->phpcrNode->expects($this->once())
            ->method('getPropertyValueWithDefault')
            ->with('foobar', '{}')
            ->will($this->returnValue(json_encode($data)));

        $this->suluProperty->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('foobar'));

        $this->locationContent->read(
            $this->phpcrNode,
            $this->suluProperty,
            'webspace_key',
            'fr',
            'segment'
        );
    }

    /**
     * @dataProvider provideRead
     */
    public function testReadForPreview($data)
    {
        $this->initReadTest($data);

        $this->locationContent->readForPreview(
            $data,
            $this->suluProperty,
            'webspace_key',
            'fr',
            'segment'
        );
    }

    /**
     * @dataProvider provideRead
     */
    public function testWrite($data)
    {
        $this->suluProperty->expects($this->once())
            ->method('getName')
            ->willReturn('myname');

        $this->suluProperty->expects($this->once())
            ->method('getValue')
            ->willReturn($data);

        $this->phpcrNode->expects($this->once())
            ->method('setProperty')
            ->with('myname', json_encode($data));

        $this->locationContent->write(
            $this->phpcrNode,
            $this->suluProperty,
            1,
            'webspace_key',
            'fr',
            'segment'
        );
    }

    public function testGetParams()
    {
        $expected = array(
            'countries' => new PropertyParameter(
                'countries', 'collection', array(
                    'at' => new PropertyParameter('at', 'string', 'Austria'),
                    'fr' => new PropertyParameter('fr', 'string', 'France'),
                    'gb' => new PropertyParameter('gb', 'string', 'Great Britain'),
                )
            ),
            'mapProviders' => new PropertyParameter(
                'mapProviders', 'collection', array(
                    'foo' => 'Foo',
                    'bar' => 'Bar',
                )
            ),
            'defaultProvider' => new PropertyParameter('defaultProvider', 'string', 'leaflet'),
            'geolocatorName' => new PropertyParameter('geolocatorName', 'string', 'some_geolocator'),
        );

        $this->mapManager->expects($this->once())
            ->method('getProvidersAsArray')
            ->will($this->returnValue($expected['mapProviders']->getValue()));

        $this->mapManager->expects($this->once())
            ->method('getDefaultProviderName')
            ->will($this->returnValue('leaflet'));

        $this->assertEquals($expected, $this->locationContent->getDefaultParams());
    }
}
