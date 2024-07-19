<?php

declare(strict_types=1);

namespace App\Repairers\Voters;

use App\Entity\RepairerOpeningHours;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template T of string
 * @template TSubject of mixed
 *
 * @template-extends Voter<T, TSubject>
 */
class RepairerVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'WRITE_REPAIRER_OPENING_HOURS' === $attribute && $subject instanceof RepairerOpeningHours;
    }

    /**
     * @param RepairerOpeningHours $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();

        return $currentUser && $currentUser->isBoss() && $currentUser->isAssociatedWithRepairer($subject->repairer);
    }
}
