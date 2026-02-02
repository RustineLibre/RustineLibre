<?php

declare(strict_types=1);

namespace App\Controller;

use App\CSV\CsvExporter;
use App\Entity\Repairer;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
class CustomerCsvController
{
    public function __construct(
        private readonly CsvExporter $csvExporter,
        private readonly UserRepository $userRepository,
        private readonly AppointmentRepository $appointmentRepository,
    ) {
    }

    #[Route(path: '/export_customers_csv/{id}', name: 'export_customers_csv', defaults: ['_format' => 'csv'], methods: ['GET'])]
    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_ADMIN") or user === subject'),
        subject: new Expression('args["repairer"].owner'),
    )]
    public function index(Repairer $repairer): Response
    {
        $customersIdsQueryBuilder = $this->appointmentRepository->getAppointmentCustomersIdsQueryBuilder();
        $customersQueryBuilder = $this->userRepository->getCustomersInfosInQbIdsByRepairer(idsQb: $customersIdsQueryBuilder, repairer: $repairer);
        $dataCustomers = $customersQueryBuilder->getQuery()->getArrayResult();
        $now = (new \DateTime())->format('d-m-Y_H_i');

        return $this->csvExporter->export($dataCustomers, "export_clients_$now.csv");
    }
}
