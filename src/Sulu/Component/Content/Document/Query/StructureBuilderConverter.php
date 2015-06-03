<?php

namespace Sulu\Component\Content\Document\Query;

use Sulu\Component\DocumentManager\Query\BuilderConverterSulu;
use Doctrine\ODM\PHPCR\Query\Builder\SourceDocument;
use Doctrine\ODM\PHPCR\Query\Builder\OperandDynamicField;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Content\Document\Behavior\LocalizedStructureBehavior;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use PHPCR\SessionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sulu\Component\DocumentManager\MetadataFactoryInterface;
use Sulu\Component\DocumentManager\PropertyEncoder;
use Sulu\Component\Content\Document\Subscriber\StructureSubscriber;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as QOMConstants;

class StructureBuilderConverter extends BuilderConverterSulu
{
    private $structures;
    private $encoder;
    private $webspaceManager;

    public function __construct(
        SessionInterface $session,
        EventDispatcherInterface $dispatcher,
        MetadataFactoryInterface $factory,
        PropertyEncoder $encoder,
        WebspaceManagerInterface $webspaceManager
    ) {
        parent::__construct($session, $dispatcher, $factory);
        $this->encoder = $encoder;
        $this->webspaceManager = $webspaceManager;
    }

    public function walkSourceDocument(SourceDocument $node)
    {
        $documentClass = $node->getDocumentFqn();

        $structure = strstr($documentClass, '#');

        if (false !== $structure) {
            $documentClass = substr($documentClass, 0, -strlen($structure));
            $structure = substr($structure, 1);
            $this->structures[$node->getAlias()] = $structure;

            $node = new SourceDocument($node->getParent(), $documentClass, $node->getAlias());
        }

        return parent::walkSourceDocument($node);
    }

    protected function applySourceConstraints(QueryBuilder $builder)
    {
        parent::applySourceConstraints($builder);

        $locales = $builder->getLocale() ? array($builder->getLocale()) : $this->getAllLocales();

        foreach ($this->structures as $alias => $structureName) {
            $compositeConstraint = null;
            foreach ($locales as $locale) {
                $reflection = $this->aliasMetadata[$alias]->getReflection();

                if ($reflection->isSubclassOf(LocalizedStructureBehavior::class)) {
                    $structureTypeProp = $this->encoder->localizedSystemName(StructureSubscriber::STRUCTURE_TYPE_FIELD, $locale);
                } else {
                    $structureTypeProp = $this->encoder->systemName(StructureSubscriber::STRUCTURE_TYPE_FIELD);
                }

                $structureConstraint = $this->qomf->comparison(
                    $this->qomf->propertyValue(
                        $alias,
                        $structureTypeProp
                    ),
                    QOMConstants::JCR_OPERATOR_EQUAL_TO,
                    $this->qomf->literal($structureName)
                );

                if (null === $compositeConstraint) {
                    $compositeConstraint = $structureConstraint;
                } else {
                    $compositeConstraint = $this->qomf->orConstraint(
                        $compositeConstraint,
                        $structureConstraint
                    );
                }
            }

            $this->constraint = $this->qomf->andConstraint(
                $this->constraint,
                $compositeConstraint
            );
        }
    }

    /**
     * TODO: There should be a better way to get the list of locales
     *       https://github.com/sulu-io/sulu/issues/1179
     */
    private function getAllLocales()
    {
        $locales = array();
        foreach ($this->webspaceManager->getAllLocalizations() as $localization) {
            $locales[] = $localization->getLocalization();
        }

        return $locales;
    }
}
