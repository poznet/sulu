<?php
/*
 * This file is part of the Sulu CMF.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Websocket\ConnectionContext;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\QueryString;
use Ratchet\ConnectionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Simple websocket context
 */
class ConnectionContext implements ConnectionContextInterface
{
    /**
     * @var RequestInterface
     */
    private $request = null;

    /**
     * @var QueryString
     */
    private $query = null;

    /**
     * @var SessionInterface
     */
    private $session = null;

    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * @var string
     */
    private $id;

    public function __construct(ConnectionInterface $conn)
    {
        if (isset($conn->WebSocket)) {
            $this->request = $conn->WebSocket->request;
            $this->query = $this->request->getUrl(true)->getQuery();
        }

        if (isset($conn->Session)) {
            $this->session = $conn->Session;
        }

        if (isset($conn->resourceId)) {
            $this->id = $conn->resourceId;
        }

        $this->parameters = new ParameterBag();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($firewall)
    {
        if ($this->session !== null) {
            return unserialize($this->session->get('_security_' . $firewall));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($firewall)
    {
        if (null !== ($token = $this->getToken($firewall))) {
            return $token->getUser();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->parameters->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->parameters->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->parameters->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->parameters->clear();
    }
}
