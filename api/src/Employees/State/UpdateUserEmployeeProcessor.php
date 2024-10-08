<?php

declare(strict_types=1);

namespace App\Employees\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Employees\Dto\CreateUserEmployeeDto;
use App\Entity\RepairerEmployee;
use App\Entity\User;
use App\Repository\RepairerEmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template-implements ProcessorInterface<int>
 */
final readonly class UpdateUserEmployeeProcessor implements ProcessorInterface
{
    public function __construct(
        private RepairerEmployeeRepository $repairerEmployeeRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private Security $security,
    ) {
    }

    /**
     * @param CreateUserEmployeeDto $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): RepairerEmployee
    {
        if (!array_key_exists('id', $uriVariables)) {
            throw new BadRequestHttpException($this->translator->trans('400_badRequest.update.employee', domain: 'validators'));
        }

        $repairerEmployee = $this->repairerEmployeeRepository->find($uriVariables['id']);
        if (!$repairerEmployee) {
            throw new NotFoundHttpException($this->translator->trans('404_notFound.repairer.employee', domain: 'validators'));
        }

        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User && ($currentUser->isAdmin() || $currentUser->isAssociatedWithRepairer($data->repairer->id))) {
            $repairerEmployee->repairer = $data->repairer;
        }
        $repairerEmployee->enabled = boolval($data->enabled);
        $this->validator->validate($repairerEmployee);

        /** @var User $user */
        $user = $repairerEmployee->employee;

        foreach (['firstName', 'lastName', 'email', 'plainPassword'] as $property) {
            if (!empty($data->$property)) {
                $user->$property = $data->$property;
            }
        }

        $this->validator->validate($user);
        $this->entityManager->flush();

        return $repairerEmployee;
    }
}
