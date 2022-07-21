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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

use function array_key_exists;
use function assert;

/**
 * The type could be used for referring custom database types in table columns.
 * @package Whoa\Data
 */
class RawNameType extends Type
{
    /** Type name */
    public const TYPE_NAME = 'RawName';

    /**
     * @inheritdoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        assert(
            array_key_exists(static::TYPE_NAME, $column),
            'Raw type name is not set. Use `Column::setCustomSchemaOption` to set the name.'
        );
        $rawName = $column[static::TYPE_NAME];
        assert(empty($rawName) === false);

        return $rawName;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return self::TYPE_NAME;
    }
}
