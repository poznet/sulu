<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SecurityBundle\Security\Exception;

/**
 * This exception is thrown when the password is mandatory but missing.
 * @package Sulu\Bundle\SecurityBundle\Security\Exception
 */
class MissingPasswordException extends SecurityException
{
    public function __construct()
    {
        parent::__construct('security.user.error.missingPassword', 1002);
    }

    public function toArray()
    {
        return array(
            'code' => $this->code,
            'message' => $this->message
        );
    }
}