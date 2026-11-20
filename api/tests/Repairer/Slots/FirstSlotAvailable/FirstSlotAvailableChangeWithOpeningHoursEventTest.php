<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Slots\FirstSlotAvailable;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Repairer;
use App\Repository\RepairerOpeningHoursRepository;
use App\Tests\Repairer\Slots\SlotsTestCase;

/**
 * The slots are computed from the current time, so these tests must not depend on the hour they run at.
 * Adding 09:00-21:00 to a repairer already open 21:00-23:00 only moved the first slot when a slot was
 * left before 21:00 today: the tests failed every evening. They now go from no opening hours (no slot)
 * to opening hours (a slot), and back, which holds at any hour.
 */
class FirstSlotAvailableChangeWithOpeningHoursEventTest extends SlotsTestCase
{
    /**
     * if there is a new opening hours added, firstSlotAvailable should be updated.
     */
    public function testFirstSlotAvailableIsUpdatedAfterAddingOpeningHours(): void
    {
        $repairer = $this->getRepairerWithSlotsAvailable();
        $client = $this->createClientAuthAsAdmin();

        $this->deleteOpeningHours($client, $repairer);
        $firstResponse = $client->request('GET', sprintf('/repairers/%d', $repairer->id))->toArray();
        self::assertNull($firstResponse['firstSlotAvailable'] ?? null);

        $this->addOpeningHours($client, $repairer);

        $secondResponse = $client->request('GET', sprintf('/repairers/%d', $repairer->id))->toArray();
        self::assertNotNull($secondResponse['firstSlotAvailable'] ?? null);
    }

    /**
     * if we remove opening hours, firstSlotAvailable should be updated.
     */
    public function testFirstSlotAvailableIsUpdatedAfterOpeningHoursDeletion(): void
    {
        $repairer = $this->getRepairerWithSlotsAvailable();
        $client = $this->createClientAuthAsAdmin();

        $firstResponse = $client->request('GET', sprintf('/repairers/%d', $repairer->id))->toArray();
        self::assertNotNull($firstResponse['firstSlotAvailable'] ?? null);

        $this->deleteOpeningHours($client, $repairer);

        $secondResponse = $client->request('GET', sprintf('/repairers/%d', $repairer->id))->toArray();
        self::assertNull($secondResponse['firstSlotAvailable'] ?? null);
    }

    private function addOpeningHours(Client $client, Repairer $repairer): void
    {
        foreach (SlotsTestCase::DAYS as $day) {
            $client->request('POST', 'repairer_opening_hours', [
                'json' => [
                    'repairer' => sprintf('/repairers/%d', $repairer->id),
                    'day' => $day,
                    'startTime' => '09:00',
                    'endTime' => '21:00',
                ],
            ]);
            self::assertResponseIsSuccessful();
        }
    }

    /**
     * Deletes through the API, so that the event updating firstSlotAvailable runs.
     */
    private function deleteOpeningHours(Client $client, Repairer $repairer): void
    {
        $openingHours = static::getContainer()->get(RepairerOpeningHoursRepository::class)->findBy(['repairer' => $repairer]);
        self::assertNotEmpty($openingHours);

        foreach ($openingHours as $openingHour) {
            $client->request('DELETE', sprintf('repairer_opening_hours/%d', $openingHour->id));
            self::assertResponseIsSuccessful();
        }
    }
}
