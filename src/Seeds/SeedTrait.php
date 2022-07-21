<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare (strict_types=1);

namespace Whoa\Data\Seeds;

use Closure;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Data\ModelSchemaInfoInterface;
use Whoa\Contracts\Data\SeedInterface;
use Whoa\Doctrine\Traits\UuidTypeTrait;
use PDO;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function assert;

/**
 * @package Whoa\Data
 */
trait SeedTrait
{
    use UuidTypeTrait;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @inheritdoc
     */
    public function init(ContainerInterface $container): SeedInterface
    {
        $this->container = $container;

        /** @var SeedInterface $self */
        return $this;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Connection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConnection(): Connection
    {
        assert($this->getContainer()->has(Connection::class) === true);

        return $this->getContainer()->get(Connection::class);
    }

    /**
     * @return ModelSchemaInfoInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getModelSchemas(): ModelSchemaInfoInterface
    {
        assert($this->getContainer()->has(ModelSchemaInfoInterface::class) === true);

        return $this->getContainer()->get(ModelSchemaInfoInterface::class);
    }

    /**
     * @return AbstractSchemaManager
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getSchemaManager(): AbstractSchemaManager
    {
        return $this->getConnection()->getSchemaManager();
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function now(): string
    {
        $format = $this->getSchemaManager()->getDatabasePlatform()->getDateTimeFormatString();
        return (new DateTimeImmutable())->format($format);
    }

    /**
     * @param string $tableName
     * @param null|int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     * @throws DBALDriverException
     */
    protected function readTableData(string $tableName, int $limit = null): array
    {
        assert($limit === null || $limit > 0);

        $builder = $this->getConnection()->createQueryBuilder();
        $builder
            ->select('*')
            ->from($tableName);

        $limit === null ?: $builder->setMaxResults($limit);

        return $builder->execute()->fetchAllAssociative();
    }

    /**
     * @param string $modelClass
     * @param null|int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws DBALDriverException
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    protected function readModelsData(string $modelClass, int $limit = null): array
    {
        return $this->readTableData($this->getModelSchemas()->getTable($modelClass), $limit);
    }

    /**
     * @param int $records
     * @param string $tableName
     * @param Closure $dataClosure
     * @param array $columnTypes
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    protected function seedTableData(
        int $records,
        string $tableName,
        Closure $dataClosure,
        array $columnTypes = []
    ): void {
        $attributeTypeGetter = $this->createAttributeTypeGetter($columnTypes);

        $connection = $this->getConnection();
        for ($i = 0; $i !== $records; $i++) {
            $this->insertRow($tableName, $connection, $dataClosure($this->getContainer()), $attributeTypeGetter);
        }
    }

    /**
     * @param int $records
     * @param string $modelClass
     * @param Closure $dataClosure
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    protected function seedModelsData(int $records, string $modelClass, Closure $dataClosure): void
    {
        $attributeTypes = $this->getModelSchemas()->getAttributeTypes($modelClass);

        $this->seedTableData($records, $this->getModelSchemas()->getTable($modelClass), $dataClosure, $attributeTypes);
    }

    /**
     * @param string $tableName
     * @param array $data
     * @param array $columnTypes
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    protected function seedRowData(string $tableName, array $data, array $columnTypes = []): void
    {
        $attributeTypeGetter = $this->createAttributeTypeGetter($columnTypes);

        $this->insertRow($tableName, $this->getConnection(), $data, $attributeTypeGetter);
    }

    /**
     * @param string $modelClass
     * @param array $data
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws NotFoundExceptionInterface
     */
    protected function seedModelData(string $modelClass, array $data): void
    {
        $attributeTypes = $this->getModelSchemas()->getAttributeTypes($modelClass);

        $this->seedRowData($this->getModelSchemas()->getTable($modelClass), $data, $attributeTypes);
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getLastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * @param string $tableName
     * @param Connection $connection
     * @param array $data
     * @param Closure $getColumnType
     * @return void
     * @throws DBALException
     */
    private function insertRow(string $tableName, Connection $connection, array $data, Closure $getColumnType): void
    {
        $types = [];
        $quotedFields = [];
        foreach ($data as $column => $value) {
            $name = $connection->quoteIdentifier($column);
            $quotedFields[$name] = $value;
            $types[$name] = $getColumnType($column);
        }

        try {
            $result = $connection->insert($tableName, $quotedFields, $types);
            assert($result !== false, 'Insert failed');
        } catch (UniqueConstraintViolationException $e) {
            // ignore non-unique records
        }
    }

    /**
     * @param array $attributeTypes
     * @return Closure
     */
    private function createAttributeTypeGetter(array $attributeTypes): Closure
    {
        return function (string $attributeType) use ($attributeTypes): string {
            return array_key_exists($attributeType, $attributeTypes) === true ?
                $attributeTypes[$attributeType] : Type::STRING;
        };
    }
}
