<?php

declare(strict_types=1);

namespace App\Tests\Intervention;

use App\Entity\Intervention;
use App\Entity\User;
use App\Repository\InterventionRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;

class InterventionAbstractTestCase extends AbstractTestCase
{
    /** @var User[] */
    protected array $users = [];

    private InterventionRepository $interventionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getContainer()->get(UserRepository::class);
        $this->users = $userRepository->findAll();

        /** @var InterventionRepository $interventionRepository */
        $interventionRepository = self::getContainer()->get(InterventionRepository::class);
        $this->interventionRepository = $interventionRepository;
    }

    protected function getAdminIntervention(): Intervention
    {
        $intervention = $this->interventionRepository->findOneBy(['isAdmin' => true]);

        if (!$intervention) {
            self::fail('No admin intervention found');
        }

        return $intervention;
    }

    /**
     * @return array{User, Intervention}
     */
    public function getBossAndHisIntervention(): array
    {
        foreach ($this->users as $user) {
            if ($user->isBoss()) {
                foreach ($user->repairers as $repairer) {
                    foreach ($repairer->repairerInterventions as $repairerIntervention) {
                        if (false === $repairerIntervention->intervention->isAdmin) {
                            return [$user, $repairerIntervention->intervention];
                        }
                    }
                }
            }
        }
        self::fail('No user with intervention found');
    }

    /**
     * @return array{User, Intervention}
     */
    public function getBossAndOtherBossIntervention(): array
    {
        [$user, $intervention] = $this->getBossAndHisIntervention();
        foreach ($this->users as $userIteration) {
            if ($userIteration !== $user && $userIteration->isBoss()) {
                return [$userIteration, $intervention];
            }
        }
        self::fail('No user or intervention found for the test');
    }
}
