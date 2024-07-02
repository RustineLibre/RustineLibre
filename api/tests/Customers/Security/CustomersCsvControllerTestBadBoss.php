<?php

declare(strict_types=1);

namespace App\Tests\Customers\Security;

use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomersCsvControllerTestBadBoss extends AbstractTestCase
{
    private RepairerRepository $repairerRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
    }

    public function testBadBoss(): void
    {
        /** @var Repairer[] $repairer */
        $repairers = $this->repairerRepository->findBy([]);

        $response = $this->createClientWithUser($repairers[0]->owner)->request('GET', sprintf('/export_customers_csv/%d', $repairers[1]->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
