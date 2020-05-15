<?php declare(strict_types=1);

namespace Mika;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as Guzzle;

class Client
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $log;
    private ?string $key;
    private Guzzle $http_client;

    function __construct(LoggerInterface $logger, string $host = "http://localhost:34001/", string $key = null)
    {
        $this->setLogger($logger);
        $this->key = $key;
        $this->http_client = new Guzzle([
            'query' => ['key' => $key],
            'base_uri' => $host,
            'timeout' => 5.0
        ]);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $json_data
     * @return ResponseInterface
     */
    private function make_request(string $method, string $path, array $json_data) {
        return $this->http_client->request($method, $path, [
            'json' => $json_data
        ]);
    }

    public function ping()
    {
        $this->log->debug("Pinging remote host");
        $resp = $this->make_request("POST", "ping", ['ping' => "ping"]);
        return $resp->getStatusCode() == 200;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->log = $logger;
        return null;
    }
}