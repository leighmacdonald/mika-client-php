<?php declare(strict_types=1);

use GuzzleHttp\Handler\MockHandler;
use Mika\BadResponse;
use Mika\TrackerConfig;
use PHPUnit\Framework\TestCase;
use Mika\Client;
use GuzzleHttp\Psr7\Response;

final class ClientTest extends TestCase
{
    private function makeClient(MockHandler $handlers)
    {
        return new Client("", "", ['handler' => $handlers]);
    }

    public function testPing()
    {
        $client = $this->makeClient(new MockHandler([
            new Response(200, [], json_encode(['pong' => "ping"])),
        ]));
        try {
            $this->assertNull($client->ping());
        } catch (BadResponse $err) {
            $this->fail($err->getMessage());
        }
    }

    public function testConfig()
    {
        $client = $this->makeClient(new MockHandler([
            new Response(200, []),
            new Response(200, [], json_encode([
                'tracker_announce_interval' => 65,
                'tracker_announce_interval_min' => 35,
                'tracker_reaper_interval' => 300,
                'tracker_batch_update_interval' => 10,
                'tracker_max_peers' => 200,
                'tracker_auto_register' => false,
                'tracker_allow_non_routable' => true,
                'geodb_enabled' => true,
            ])),
            new Response(400)
        ]));
        $config = new TrackerConfig();
        $config->set(TrackerConfig::ANN_INTERVAL, 65);
        $config->set(TrackerConfig::ANN_INTERVAL_MIN, 35);
        $config->set(TrackerConfig::REAPER_INTERVAL, 300);
        $config->set(TrackerConfig::BATCH_INTERVAL, 10);
        $config->set(TrackerConfig::MAX_PEERS, 200);
        $config->set(TrackerConfig::AUTO_REGISTER, false);
        $config->set(TrackerConfig::ALLOW_ROUTABLE, true);
        $config->set(TrackerConfig::GEODB_ENABLED, true);
        $state = $config->json();
        $this->assertCount(8, $state["update_keys"]);
        try {
            $client->configUpdate($config);
        } catch (BadResponse $err) {
            $this->fail($err->getMessage());
            return;
        }
        try {
            $newConfig = $client->configGet();
        } catch (BadResponse $err) {
            $this->fail($err->getMessage());
            return;
        }
        foreach (array_filter(array_keys($state), fn($k) => $k != "update_keys") as $key) {
            $this->assertEquals($state[$key], $newConfig[$key]);
        }

        $configBad = $config;
        $configBad->set(TrackerConfig::ANN_INTERVAL, "50s");
        $this->expectException("\Mika\BadResponse");
        $client->configUpdate($config);

    }
}