<?php

declare(strict_types=1);

namespace App\RepairerIntervention\Voter;

use App\Entity\RepairerIntervention;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template T of string
 * @template TSubject of mixed
 *
 * @template-extends Voter<T, TSubject>
 */
class RepairerInterventionVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'REPAIRER_INTERVENTION_WRITE' === $attribute && $subject instanceof RepairerIntervention;
    }

    /**
     * @param RepairerIntervention $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return (bool) $subject->intervention->isAdmin;
    }
}
