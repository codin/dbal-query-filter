<?php

declare(strict_types=1);

namespace Codin\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use ReflectionClass;

/**
 * @method self callback(string $queryName, callable $callback)
 * @method self contains(string $queryName, ?string $columnName, bool $notIn)
 * @method self match(string $queryName, ?string $columnName)
 * @method self nullable(string $queryName, ?string $columnName, bool $notNull)
 * @method self range(string $queryName, ?string $columnName)
 */
class QueryFilter
{
    protected array $definitions = [];

    public function map(Filters\Filter $filter): self
    {
        $this->definitions[$filter->getQueryParam()] = $filter;

        return $this;
    }

    public function __call(string $method, array $args): self
    {
        $name = __NAMESPACE__ . '\\Filters\\'.ucfirst($method);
        if (class_exists($name) && (new ReflectionClass($name))->isInstantiable()) {
            return $this->map(new $name(...$args));
        }
        throw new \ErrorException('Filter does not exist: '.$name);
    }

    /**
     * Matched filters from input array
     */
    protected function getFilters(array $params): array
    {
        return array_filter(
            $this->definitions,
            static function (Filters\Filter $filter) use ($params): bool {
                return $filter->validate($params);
            }
        );
    }

    /**
     * Mutable query builder
     */
    public function build(QueryBuilder $query, array $params): void
    {
        foreach ($this->getFilters($params) as $filter) {
            $filter->build($query, $params);
        }
    }
}
