<?php

declare(strict_types=1);

namespace App\Repairers\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Repairer;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AroundFilter extends AbstractFilter
{
    public const PROPERTY_NAME = 'around';

    public function __construct(private readonly TranslatorInterface $translator, ManagerRegistry $managerRegistry, LoggerInterface $logger = null, array $properties = null, NameConverterInterface $nameConverter = null)
    {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (self::PROPERTY_NAME !== $property) {
            return;
        }

        if (!property_exists($resourceClass, 'latitude') || !property_exists($resourceClass, 'longitude')) {
            throw new BadRequestHttpException($this->translator->trans('400_badRequest.around.filter.resource.class', ['%resource%' => $resourceClass], domain: 'validators'));
        }

        if (!is_array($value) || empty($value)) {
            throw new BadRequestHttpException($this->translator->trans('400_badRequest.around.filter', domain: 'validators'));
        }

        $city = key($value);
        $coordinates = explode(',', $value[$city]);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('r.id')
            ->from(Repairer::class, 'r')
            ->leftJoin('r.repairerCities', 'rrc')
            ->andWhere('LOWER(rrc.name) = LOWER(:city)')
            ->setParameter('city', $city);

        $queryBuilder->andWhere($queryBuilder->expr()->eq(
            'LOWER(o.city) = LOWER(:searchCity) OR o.id in (:ids) OR ST_DWithin(
                    o.gpsPoint,
                    ST_SetSRID(ST_MakePoint(:around_latitude, :around_longitude), 4326),
                    :around_distance
                )', 'true'));
        $queryBuilder->setParameter('ids', $subQuery->getQuery()->getResult());
        $queryBuilder->setParameter('around_latitude', $coordinates[0]);
        $queryBuilder->setParameter('around_longitude', $coordinates[1]);
        $queryBuilder->setParameter('around_distance', array_key_exists(2, $coordinates) ? (int) $coordinates[2] : 5000);
        $queryBuilder->setParameter('searchCity', $city);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        return [
            'around' => [
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'description' => 'Filter to get points around given GPS coordinates and radius in meters.',
            'openapi' => [
                'example' => '/repairers?around[city]=latitude,longitude,radius',
                'allowReserved' => false,
                'allowEmptyValue' => true,
                'explode' => false,
            ],
        ]];
    }
}
