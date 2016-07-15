<?php

namespace Frigg\KeeprBundle\Elastica;

use FOS\ElasticaBundle\Elastica\Client as BaseClient;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Elastica\Exception\ExceptionInterface;
use Elastica\Response;
use Elastica\Request;

/**
 * Override to suppress exceptions
 */
class ElasticaClient extends BaseClient
{
    /**
     *
     *
     * @param string $path
     * @param string $method
     * @param array $data
     * @param array $query
     * @return Response
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [])
    {
        try {
            return parent::request($path, $method, $data, $query);
        } catch (ExceptionInterface $e) {
            return new Response('{"took":0,"timed_out":false,"hits":{"total":0,"max_score":0,"hits":[]}}');
        }
    }
}
