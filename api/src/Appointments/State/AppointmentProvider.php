<?php

namespace App\Appointments\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Appointment;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @implements ProviderInterface<Appointment> */
class AppointmentProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,

        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $readProvider,
    ) {
    }

    /**
     * @return iterable<Appointment>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null|object
    {
        $user = $this->security->getUser();

        if ($user instanceof User && $this->security->isGranted('IS_AUTHENTICATED_FULLY') && $user->isAssociatedWithRepairer($uriVariables['repairer_id'])) {
            return $this->readProvider->provide($operation, $uriVariables, $context);
        }

        throw new AccessDeniedHttpException('Access Denied.');
    }
}
