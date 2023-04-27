<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Distance;

use App\Tests\AbstractTestCase;

class DistanceTest extends AbstractTestCase
{
    public function testCollectionWithDistance(): void
    {
        $response = self::createClient()->request('GET', '/repairers?around[5000]=50.621917,3.063398');
        $this->assertResponseIsSuccessful();
        $responseCollection = $response->toArray()['hydra:member'];

        $this->assertTrue($this->isIntOrFloat($responseCollection[0]['distance']));
        $this->assertTrue($this->isIntOrFloat($responseCollection[1]['distance']));
        $this->assertTrue($this->isIntOrFloat($responseCollection[2]['distance']));
        $this->assertTrue($this->isIntOrFloat($responseCollection[3]['distance']));
        $this->assertTrue(0 <= $responseCollection[0]['distance']);
        $this->assertTrue(0 <= $responseCollection[1]['distance']);
        $this->assertTrue(0 <= $responseCollection[2]['distance']);
        $this->assertTrue(0 <= $responseCollection[3]['distance']);
    }

    public function testCollectionWithNoAroundFilter(): void
    {
        $response = self::createClient()->request('GET', '/repairers');
        $this->assertResponseIsSuccessful();
        $responseCollection = $response->toArray()['hydra:member'];

        $this->assertArrayNotHasKey('distance', $responseCollection[0]);
        $this->assertArrayNotHasKey('distance', $responseCollection[1]);
        $this->assertArrayNotHasKey('distance', $responseCollection[2]);
        $this->assertArrayNotHasKey('distance', $responseCollection[3]);
    }

    private function isIntOrFloat($value): bool
    {
        return is_int($value) || is_float($value);
    }
}