<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Filters;

use App\Tests\AbstractTestCase;

class RandomFilterTest extends AbstractTestCase
{
    public function testRandomFilter(): void
    {
        $responses = [];

        // Check if the first id is not the same at each request
        for ($i = 0; $i < 3; ++$i) {
            $response = static::createClient()->request('GET', '/repairers?sort=random')->toArray();
            self::assertResponseIsSuccessful();
            $responses[] = $response['hydra:member'][0]['@id'];
        }

        $this->assertGreaterThan(1, count(array_unique($responses)));
    }

    public function testRandomFilterWorkWithFirstSlotAvailableFilter(): void
    {
        $idsFromResponses = [];
        $client = static::createClient();

        for ($i = 0; $i < 2; ++$i) {
            $response = $client->request('GET', '/repairers?sort=random&availability=ASC')->toArray()['hydra:member'];
            self::assertResponseIsSuccessful();

            $idsForThisResponse = [];
            foreach ($response as $key => $repairer) {
                if (array_key_exists($key + 1, $response)
                    && array_key_exists('firstSlotAvailable', $repairer)
                    && array_key_exists('firstSlotAvailable', $response[$key + 1])
                ) {
                    // Check that each result has a firstSlotAvailable less or equal to next result
                    $this->assertLessThanOrEqual($response[$key + 1]['firstSlotAvailable'], $repairer['firstSlotAvailable']);
                }
                $idsForThisResponse[] = $repairer['@id'];
            }
            // we tranform the array to string to compare the content order
            $idsFromResponses[] = implode('', $idsForThisResponse);
        }

        // we check if different orders are returned
        $idsFromResponses = array_unique($idsFromResponses);
        self::assertGreaterThanOrEqual(2, count($idsFromResponses));
    }

    public function testRandomFilterWorkWithSearchFilter(): void
    {
        $idsFromResponses = [];
        $firstResponse = null;

        for ($i = 0; $i < 5; ++$i) {
            $response = static::createClient()->request('GET', '/repairers?sort=random&repairerTypes.name=Réparateur%20itinérant')->toArray()['hydra:member'];
            self::assertResponseIsSuccessful();

            if (null === $firstResponse) {
                $firstResponse = $response;
            }

            $ids = [];
            foreach ($response as $repairer) {
                $ids[] = $repairer['@id'];
            }
            $idsFromResponses[] = implode('', $ids);
        }

        // we check if different orders are returned across multiple requests
        $this->assertGreaterThan(1, count(array_unique($idsFromResponses)));

        // Check repairer type
        $firstRepairerTypes = static::createClient()->request('GET', $firstResponse[0]['@id'])->toArray()['repairerTypes'];
        $repairerTypesForFirstResponse = array_map(function ($firstRepairerType) {
            return $firstRepairerType['name'];
        }, $firstRepairerTypes);
        self::assertContains('Réparateur itinérant', $repairerTypesForFirstResponse);
    }

    public function testRandomFilterWorkWithAroundFilter(): void
    {
        $responses = [];
        for ($i = 0; $i < 10; ++$i) {
            $response = static::createClient()->request('GET', '/repairers?sort=random&around[Lille]=50.67394149851168,3.0665240417922486')->toArray()['hydra:member'];
            if (count($response) < 2) {
                $this->markTestSkipped('He should have at least 2 repairers with same latitude and longitude to test the random filter with the around filter.');
            }
            self::assertResponseIsSuccessful();
            $responses[] = $response[0]['@id'];
        }

        // check if the first id is not the same at each request
        $this->assertGreaterThan(1, count(array_unique($responses)));
    }
}
