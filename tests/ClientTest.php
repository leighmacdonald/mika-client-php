<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Mika\Client;

final class ClientTest extends TestCase {
    private function makeClient() {
        $logger = new Logger('mika');
        return new Client($logger);
    }
    public function testPing() {
        $this->assertTrue($this->makeClient()->ping());
    }
}