<?php declare(strict_types=1);

namespace Mika;

use GuzzleHttp\Exception\ClientException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client as Guzzle;

class Client
{
    private ?string $key;
    private Guzzle $http_client;

    function __construct(string $host = "http://localhost:34001/",
                         string $key = null,
                         array $extra = [])
    {
        $this->key = $key;
        $opts = [
            'base_uri' => $host,
            'timeout' => 5.0,
            'query' => $key ? ['key' => $key] : []
        ];
        $this->http_client = new Guzzle(array_merge($opts, $extra));
    }

    /**
     * @param string $method HTTP method to call
     * @param string $path Route/Path portion of the API
     * @param int $code Excepted response code
     * @param array|null $json_data
     * @return ResponseInterface
     * @throws BadResponse
     */
    private function make_request(string $method, string $path, int $code = 200, array $json_data = null)
    {
        $args = [];
        if (!is_null($json_data) && !empty($json_data)) {
            $args['json'] = $json_data;
        }
        try {
            $resp = $this->http_client->request($method, $path, $args);
        } catch (ClientException $err) {
            throw new BadResponse(sprintf("Invalid response code (%d)",
                $err->getCode()), $err->getCode(), $err);
        }
        if ($resp->getStatusCode() != $code) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new BadResponse("Invalid response code");
        }
        return $resp;
    }

    /**
     * @return void
     * @throws BadResponse
     */
    public function ping()
    {
        $this->make_request("POST", "ping", 200, ['ping' => "ping"]);
    }

    /**
     * The keys that we actually want to update from our struct
     * Some default values could be valid values so we cannot rely on empty values alone
     * Keys not listed are NOT updated even if a value is set in the struct
     *
     * UpdateKeys                 []config.Key `json:"update_keys"`
     * TrackerAnnounceInterval    string       `json:"tracker_announce_interval,omitempty"`
     * TrackerAnnounceIntervalMin string       `json:"tracker_announce_interval_min,omitempty"`
     * TrackerReaperInterval      string       `json:"tracker_reaper_interval,omitempty"`
     * TrackerBatchUpdateInterval string       `json:"tracker_batch_update_interval,omitempty"`
     * TrackerMaxPeers            int          `json:"tracker_max_peers,omitempty"`
     * TrackerAutoRegister        bool         `json:"tracker_auto_register,omitempty"`
     * TrackerAllowNonRoutable    bool         `json:"tracker_allow_non_routable,omitempty"`
     * GeodbEnabled               bool         `json:"geodb_enabled"`
     *
     * @throw BadResponse
     *
     * @param TrackerConfig $config
     * @throws BadResponse
     */
    public function configUpdate(TrackerConfig $config) {
        $this->make_request("PATCH", "config", 200, $config->json());
    }

    /**
     * @return mixed
     * @throws BadResponse
     */
    public function configGet() {
        return $this->decode($this->make_request("GET", "config"));
    }

    /**
     * @param ResponseInterface $resp
     * @return mixed
     * @throws BadResponse
     * @noinspection PhpUndefinedClassInspection
     */
    private function decode(ResponseInterface $resp) {
        try {
            return json_decode($resp->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            throw new BadResponse("Failed to decode json", $resp->getStatusCode(), $err);
        }
    }

    /**
     * @param string $passkey
     * @return bool
     * @throws BadResponse
     */
    public function userDelete(string $passkey)
    {
        if ($passkey == "") {
            return false;
        }
        $resp = $this->make_request("DELETE", "user/pk/${passkey}");
        return $resp->getStatusCode() == 200;
    }

    /**
     * @param string $info_hash
     * @return void
     * @throws BadResponse|BadRequest
     */
    public function torrentDelete(string $info_hash)
    {
        if (strlen($info_hash) != 40) {
            throw new BadRequest("Invalid info_hash passed into deleteTorrent");
        }
        $this->make_request("DELETE", "torrent/${info_hash}");
    }
}