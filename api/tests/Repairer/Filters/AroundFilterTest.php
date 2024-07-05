<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Filters;

use App\Tests\AbstractTestCase;

class AroundFilterTest extends AbstractTestCase
{
    public function testAroundFilter5Km(): void
    {
        $response = static::createClient()->request('GET', '/repairers?around[lille]=50.621917,3.063398,5000');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertEquals(22, $response->toArray()['hydra:totalItems']);
    }

    public function testCityLommeAroundFilter5Km(): void
    {
        $response = static::createClient()->request('GET', '/repairers?around[lomme]=50.643554,2.988918,5000');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertEquals(2, $response->toArray()['hydra:totalItems']);
    }

    public function testCityHellemesAroundFilter5Km(): void
    {
        $response = static::createClient()->request('GET', '/repairers?around[hellemes]=50.626699,3.111498,5000');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertEquals(10, $response->toArray()['hydra:totalItems']);
    }

    public function testCityHellemesAroundFilter30Km(): void
    {
        $response = static::createClient()->request('GET', '/repairers?around[hellemes]=50.626699,3.111498,30000');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertEquals(25, $response->toArray()['hydra:totalItems']);
    }

    public function testCityNieppeIfRepairerHasCities(): void
    {
        $response = static::createClient()->request('GET', '/repairers?around[Nieppe]=50.6992987531,2.83247310085,2000');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseContent = $response->toArray();
        // The third repairer (benoit broutchoux) has another repairer city of intervention : Nieppe
        $this->assertEquals(1, $responseContent['hydra:totalItems']);
        $this->assertEquals('Chez Benoit Broutchoux', $responseContent['hydra:member'][0]['name']);
    }

    public function testCityArmentieresIfRepairerHasCities(): void
    {
        $response = static::createClient()->request('GET', '/repairers?around[Armentières]=50.6913797537,2.8797155328,2000');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseContent = $response->toArray();
        // The 4th repairer (Au réparateur de bicyclettes) has another repairer city of intervention : Armentières
        $this->assertEquals(1, $responseContent['hydra:totalItems']);
        $this->assertEquals('Au réparateur de bicyclettes', $responseContent['hydra:member'][0]['name']);
    }
}
