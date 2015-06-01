<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Document\Behavior;

use Sulu\Component\DocumentManager\Behavior\Mapping\LocaleBehavior;

/**
 * Documents implementing this behavior can have structures applied to them.
 *
 * Content is set by binding raw data to the PropertyContainerInterface retried
 * through the getContent method.
 *
 * Content is accessed as folows:
 *
 * ````
 * $this->getContent()->getProperty('foo')->getValue();
 * ````
 */
interface StructureBehavior extends LocaleBehavior
{
    /**
     * Return the type of the structure used for the content
     *
     * @return string
     */
    public function getStructureType();

    /**
     * Set the structure type used for the content
     *
     * @param string
     */
    public function setStructureType($structureType);

    /**
     * Return the PropertyContainerInterface instance.
     *
     * @return PropertyContainerInterface
     */
    public function getPropertyContainer();
}


