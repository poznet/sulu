<?php

namespace Sulu\Component\Content\Document\Query;

use Sulu\Component\DocumentManager\Query\BuilderConverterSulu;
use Doctrine\ODM\PHPCR\Query\Builder\SourceDocument;
use Doctrine\ODM\PHPCR\Query\Builder\OperandDynamicField;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Content\Document\Behavior\LocalizedStructureBehavior;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;

class StructureBuilderConverter extends BuilderConverterSulu
{
    private $structures;
    private $encoder;
    private $webspaceManager;

    public function __construct(
        SessionInterface $session,
        EventDispatcherInterface $dispatcher,
        MetadataFactoryInterface $factory,
        PropertyEncoder $propertyEncoder,
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
        parent::applySourceConstraints();

        $locales = $builder->getLocale() ? array($builder->getLocale()) : $this->getAllLocales();

        foreach ($this->structures as $alias => $structureName) {
            foreach ($locales as $locale) {
                $reflection = $this->aliasMetadata[$alias]->getReflection();

                if ($reflection instanceof LocalizedStructureBehavior) {
                    $structureTypeProp = $this->propertyEncoder->localizedSystemName(StructureSubscriber::STRUCTURE_TYPE_FIELD, $locale);
                } else {
                    $structureTypeProp = $this->propertyEncoder->systemName(StructureSubscriber::STRUCTURE_TYPE_FIELD);
                }

                $this->constraint = $this->qomf->andConstraint(
                    $this->constraint,
                    $this->qomf->comparison(
                        $this->qomf->propertyValue(
                            $alias,
                            $structureTypeProp
                        ),
                        QOMConstants::JCR_OPERATOR_EQUAL_TO,
                        $this->qomf->literal($structureName)
                    )
                );
            }
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
            $locales[] = $localization->getLocale();
        }

        return $locales;
    }
}
