<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

/**
 * Search term in multiple ressource fields.
 */
final class OrSearchFilter extends AbstractFilter
{
    private const FILTER_KEY = 'search';

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if (self::FILTER_KEY !== $property || !$value) {
            return;
        }

        $orX = $queryBuilder->expr()->orX();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        foreach ($this->getProperties() as $fieldName => $strategy) {
            if (!$this->isPropertyEnabled($fieldName, $resourceClass) || !$this->isPropertyMapped($fieldName, $resourceClass, true)) {
                continue;
            }

            $field = $fieldName;

            if ($this->isPropertyNested($fieldName, $resourceClass)) {
                [$nestedAlias, $field, $associations] = $this->addJoinsForNestedProperty(
                    $fieldName, $rootAlias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::LEFT_JOIN
                );

                $metadata = $this->getNestedMetadata($resourceClass, $associations);

                if (!$metadata->hasField($field)) {
                    continue;
                }
            }

            $this->applyStrategy($orX, $strategy, $nestedAlias ?? $rootAlias, $field, $value, $queryBuilder);
        }

        if ($orX->count() > 0) {
            $queryBuilder->andWhere($orX);
        }
    }

    private function applyStrategy($orX, string $strategy, string $alias, string $field, $value, QueryBuilder $queryBuilder): void
    {
        $likeExpression = sprintf('%s.%s LIKE :value', $alias, $field);

        switch ($strategy) {
            case SearchFilterInterface::STRATEGY_EXACT:
                $orX->add(sprintf('%s.%s = :value', $alias, $field));
                break;
            case SearchFilterInterface::STRATEGY_START:
                $orX->add($likeExpression);
                $queryBuilder->setParameter('value', "$value%");
                break;
            case SearchFilterInterface::STRATEGY_END:
                $orX->add($likeExpression);
                $queryBuilder->setParameter('value', "%$value");
                break;
            case SearchFilterInterface::STRATEGY_WORD_START:
                $orX->add($likeExpression);
                $orX->add(sprintf('%s.%s LIKE :value_word', $alias, $field));
                $queryBuilder->setParameter('value_word', "% $value%");
                break;
            case 'i'.SearchFilterInterface::STRATEGY_EXACT:
                $orX->add(sprintf('LOWER(%s.%s) = LOWER(:value)', $alias, $field));
                break;
            case 'i'.SearchFilterInterface::STRATEGY_PARTIAL:
            case 'i'.SearchFilterInterface::STRATEGY_START:
            case 'i'.SearchFilterInterface::STRATEGY_END:
            case 'i'.SearchFilterInterface::STRATEGY_WORD_START:
                $orX->add(sprintf('LOWER(%s.%s) LIKE LOWER(:value)', $alias, $field));
                break;
            default:
                $orX->add($likeExpression);
                break;
        }

        if (!in_array($strategy, [SearchFilterInterface::STRATEGY_EXACT, 'i'.SearchFilterInterface::STRATEGY_EXACT], true)) {
            $queryBuilder->setParameter('value', "%$value%");

            return;
        }

        $queryBuilder->setParameter('value', $value);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $properties = implode(', ', array_keys($this->getProperties()));
        $description[self::FILTER_KEY] = [
            'property' => $properties,
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'description' => "Filter by $properties using Or condition",
            'openapi' => [
                'example' => '?search=foo',
                'allowReserved' => false,
                'allowEmptyValue' => true,
                'explode' => false,
            ],
        ];

        return $description;
    }
}
