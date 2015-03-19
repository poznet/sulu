<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DTL\Bundle\ContentBundle\Form\Type\Property;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use DTL\Component\Content\Form\ContentView;
use DTL\Component\Content\FrontView\FrontView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DTL\Component\Content\FrontView\FrontViewBuilder;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use DTL\Component\Content\Property\PropertyTypeInterface;

class BlockType extends AbstractType
{
    const TYPE_KEY = 'type';

    private $frontBuilder;

    public function __construct(FrontViewBuilder $frontBuilder)
    {
        $this->frontBuilder = $frontBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $options)
    {
        $options->setRequired(array(
            'default_type',
            'prototypes',
        ));

        $options->setAllowedTypes(array(
            'prototypes' => 'array',
        ));

        $options->setDefaults(array(
            'compound' => true,
        ));

        $options->setNormalizer('prototypes', function ($options, $prototypes) {
            $normalizedPrototypes = array();

            foreach ($prototypes as $name => $prototype) {
                $normalizedPrototypes[$name] = array_merge(array(
                    'type' => null,
                    'options' => array(),
                    'properties' => array(),
                ), $prototype);
            }

            return $normalizedPrototypes;
        });

    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prototypeForms = array();

        $builder->add('type', 'text');

        foreach ($options['prototypes'] as $name => $prototype) {
            $prototypeOptions = $prototype['options'];
            $prototypeOptions['auto_initialize'] = false;
            $prototypeBuilder = $builder->create('block', 'form', $prototypeOptions);

            foreach ($prototype['properties'] as $propName => $prop) {
                $propOptions = $prop['options'];
                $prototypeBuilder->add($propName, $prop['type'], $propOptions);
            }
            $prototypeForms[$name] = $prototypeBuilder->getForm();
        }

        $builder->setAttribute('prototypes', $prototypeForms);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $block = $event->getData();

            if (!isset($block[self::TYPE_KEY])) {
                throw new \RuntimeException(sprintf(
                    'Block data must have the "%s" key set in order to determine the correct block type',
                    self::TYPE_KEY
                ));
            }

            $blockType = $block[self::TYPE_KEY];

            $form = $event->getForm();
            $prototypes = $form->getConfig()->getAttribute('prototypes');

            if (!isset($prototypes[$blockType])) {
                throw new \RuntimeException(sprintf(
                    'The block type "%s" is not known, known types: "%s"',
                    $blockType, implode('", "', array_keys($prototypes))
                ));
            }

            $prototype = $prototypes[$blockType];
            $form->add($prototype);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'block';
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'property';
    }
}
