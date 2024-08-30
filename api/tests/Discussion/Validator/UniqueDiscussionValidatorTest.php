<?php

declare(strict_types=1);

namespace App\Tests\Discussion\Validator;

use App\Entity\Discussion;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniqueDiscussionValidatorTest extends AbstractTestCase
{
    private UserRepository $userRepository;

    private RepairerRepository $repairerRepository;

    private TranslatorInterface $translator;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
    }

    public function testCannotCreateTwoDiscussionForSameRepairerAndCustomer(): void
    {
        $repairer = $this->repairerRepository->findOneBy([]);

        $queryBuilderIds = $this->userRepository->createQueryBuilder('uid')
            ->select('uid.id')
            ->innerJoin(Discussion::class, 'd', Join::WITH, 'd.customer = uid.id')
            ->where('d.repairer = :repairer')
            ->addGroupBy('uid.id');

        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        $customer = $queryBuilder
            ->where($queryBuilder->expr()->notIn('u.id', $queryBuilderIds->getDQL()))
            ->setMaxResults(1)
            ->setParameter('repairer', $repairer)
            ->getQuery()
            ->getOneOrNullResult();

        $client = $this->createClientWithUser($repairer->owner);

        $client->request('POST', '/discussions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairer->id),
                'customer' => sprintf('/users/%s', $customer->id),
            ],
        ]);
        self::assertResponseIsSuccessful();

        $client->request('POST', '/discussions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairer->id),
                'customer' => sprintf('/users/%s', $customer->id),
            ],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertJsonContains([
            'hydra:description' => sprintf($this->translator->trans('discussion.unique', domain: 'validators')),
        ]);
    }
}
