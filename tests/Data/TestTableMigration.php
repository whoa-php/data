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

namespace Whoa\Tests\Data\Data;

use Doctrine\DBAL\Exception as DBALException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Data\MigrationInterface;
use Whoa\Data\Migrations\MigrationTrait;

/**
 * @package Whoa\Tests\Data
 */
class TestTableMigration implements MigrationInterface
{
    use MigrationTrait;

    /**
     * @var string
     */
    private string $modelClass;

    /**
     * @var array
     */
    private array $columns;

    /**
     * @param string $modelClass
     * @param array $columns
     */
    public function __construct(string $modelClass, array $columns)
    {
        $this->modelClass = $modelClass;
        $this->columns = $columns;
    }

    /**
     * @inheritdoc
     * @throws DBALException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function migrate(): void
    {
        $this->createTable($this->modelClass, $this->columns);
    }

    /**
     * @inheritdoc
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function rollback(): void
    {
        $this->dropTableIfExists($this->modelClass);
    }
}
