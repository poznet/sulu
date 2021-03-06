<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContactBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContactMediaController.
 *
 * @RouteResource("Medias")
 */
class ContactMediaController extends AbstractMediaController
{
    protected static $entityName = 'SuluContactBundle:Contact';

    /**
     * Removes a media from the relation to the account.
     *
     * @param $id - contact id
     * @param $slug - media id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id, $slug)
    {
        return $this->removeMediaFromEntity(self::$entityName, $id, $slug);
    }

    /**
     * Adds a new media to the account.
     *
     * @param $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction($id, Request $request)
    {
        return $this->addMediaToEntity(self::$entityName, $id, $request->get('mediaId', ''));
    }
}
