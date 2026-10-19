<?php

declare(strict_types=1);

namespace App\Repairers\Serializer;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Entity\Repairer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RepairerEnabledContextBuilder implements SerializerContextBuilderInterface
{
    private SerializerContextBuilderInterface $decorated;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Repairer::class === $resourceClass && isset($context['groups']) && $this->authorizationChecker->isGranted('ROLE_ADMIN') && false === $normalization) {
            $context['groups'][] = 'admin:input';
        }

        // Owner e-mail and last connection are only meant for the back-office list.
        if (Repairer::class === $resourceClass && true === $normalization && in_array(Repairer::REPAIRER_COLLECTION_READ, $context['groups'] ?? [], true) && $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $context['groups'][] = Repairer::REPAIRER_ADMIN_COLLECTION_READ;
        }

        return $context;
    }
}
