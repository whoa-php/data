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

namespace Whoa\Data\Migrations;

use Whoa\Contracts\Data\ModelSchemaInfoInterface;
use Whoa\Data\Contracts\MigrationContextInterface;

/**
 * @package Whoa\Data
 */
class MigrationContext implements MigrationContextInterface
{
    /**
     * @var string
     */
    private string $modelClass;

    /**
     * @var ModelSchemaInfoInterface
     */
    private ModelSchemaInfoInterface $modelSchemas;

    /**
     * @param string $modelClass
     * @param ModelSchemaInfoInterface $modelSchemas
     */
    public function __construct(string $modelClass, ModelSchemaInfoInterface $modelSchemas)
    {
        $this->modelClass = $modelClass;
        $this->modelSchemas = $modelSchemas;
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * @return ModelSchemaInfoInterface
     */
    public function getModelSchemas(): ModelSchemaInfoInterface
    {
        return $this->modelSchemas;
    }
}
