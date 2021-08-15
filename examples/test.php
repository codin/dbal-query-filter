<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$conn = Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => ':memory:']);
$query = $conn->createQueryBuilder()->from('tbl')->select('*');

parse_str('filters[product]=1&filters[category][]=games&filters[amount_gte]=2000&fields[]=id', $params);

$filter = (new Codin\DBAL\QueryFilter())
    ->match('product')
    ->range('amount')
    ->contains('category')
;

$filter->build($query, $params['filters']);

var_dump($query->getSQL(), $query->getParameters());
