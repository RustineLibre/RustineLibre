<?php

declare(strict_types=1);

namespace App\User\Voters;

use App\Entity\User;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template T of string
 * @template TSubject of mixed
 *
 * @template-extends Voter<T, TSubject>
 */
class CustomerVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
        private readonly AppointmentRepository $appointmentRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'CUSTOMER_READ' === $attribute && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();

        // Should be at least repairer's boss or employee
        if (!$currentUser) {
            return false;
        }

        return (bool) $this->appointmentRepository->findOneByCustomerAndUserRepairer($subject, $currentUser);
    }
}
