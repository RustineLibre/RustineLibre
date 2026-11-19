<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Filters;

use App\Tests\AbstractTestCase;

class RandomFilterTest extends AbstractTestCase
{
    /**
     * Two random orders can legitimately be identical, so a test that compares only two or three
     * responses fails from time to time. Requesting until a different order shows up (or giving up
     * after this many requests) keeps the check meaningful without the flakiness.
     */
    private const MAX_REQUESTS = 20;

    public function testRandomFilter(): void
    {
        // Check if the first id is not the same at each request
        $firstIds = $this->collectUntilTwoDistinct(function (): string {
            $response = static::createClient()->request('GET', '/repairers?sort=random')->toArray();
            self::assertResponseIsSuccessful();

            return $response['hydra:member'][0]['@id'];
        });

        $this->assertGreaterThan(1, count($firstIds));
    }

    public function testRandomFilterWorkWithFirstSlotAvailableFilter(): void
    {
        $client = static::createClient();

        $orders = $this->collectUntilTwoDistinct(function () use ($client): string {
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
            return implode('', $idsForThisResponse);
        });

        // we check if different orders are returned
        self::assertGreaterThanOrEqual(2, count($orders));
    }

    public function testRandomFilterWorkWithSearchFilter(): void
    {
        $orders = $this->collectUntilTwoDistinct(function (): string {
            $response = static::createClient()->request('GET', '/repairers?sort=random&repairerTypes.name=Réparateur%20itinérant')->toArray()['hydra:member'];
            self::assertResponseIsSuccessful();

            $ids = array_map(fn (array $repairer): string => $repairer['@id'], $response);

            // Check repairer type of the first result
            $repairerTypes = static::createClient()->request('GET', $ids[0])->toArray()['repairerTypes'];
            self::assertContains('Réparateur itinérant', array_map(fn (array $repairerType): string => $repairerType['name'], $repairerTypes));

            return implode('', $ids);
        });

        self::assertGreaterThanOrEqual(2, count($orders));
    }

    public function testRandomFilterWorkWithAroundFilter(): void
    {
        $firstIds = $this->collectUntilTwoDistinct(function (): string {
            $response = static::createClient()->request('GET', '/repairers?sort=random&around[Lille]=50.67394149851168,3.0665240417922486')->toArray()['hydra:member'];
            if (count($response) < 2) {
                $this->markTestSkipped('He should have at least 2 repairers with same latitude and longitude to test the random filter with the around filter.');
            }
            self::assertResponseIsSuccessful();

            return $response[0]['@id'];
        });

        // check if the first id is not the same at each request
        $this->assertGreaterThan(1, count($firstIds));
    }

    /**
     * Calls $request until it returned two different values, at most MAX_REQUESTS times.
     *
     * @param callable(): string $request
     *
     * @return list<string> the distinct values returned
     */
    private function collectUntilTwoDistinct(callable $request): array
    {
        $values = [];
        for ($i = 0; $i < self::MAX_REQUESTS && count($values) < 2; ++$i) {
            $values[] = $request();
            $values = array_values(array_unique($values));
        }

        return $values;
    }
}
