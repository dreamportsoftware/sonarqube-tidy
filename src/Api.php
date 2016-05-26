<?php

namespace SonarqubeTidy;

use GuzzleHttp\Client;

/**
 * Class Api
 *
 * Base class for interactions with remote REST services
 *
 * @package SonarqubeTidy
 */
class Api
{
    protected $username;
    protected $password;
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = parse_ini_file(dirname(__FILE__).'/../config/config.ini', true);
    }

    /**
     * @param mixed $password
     *
     * @return this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param mixed $username
     *
     * @return this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }
}
