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

namespace Whoa\Tests\Data;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Whoa\Data\Migrations\EnumType;
use Mockery;

/**
 * @package Whoa\Tests\Data
 */
class EnumTypeTest extends TestCase
{
    /**
     * Test SQL declaration.
     * @throws DBALException
     */
    public function testSqlDeclaration()
    {
        if (Type::hasType(EnumType::TYPE_NAME) === false) {
            Type::addType(EnumType::TYPE_NAME, EnumType::class);
        }

        /** @var EnumType $type */
        $type = Type::getType(EnumType::TYPE_NAME);
        $this->assertEquals(EnumType::TYPE_NAME, $type->getName());

        $platform = Mockery::mock(AbstractPlatform::class);
        $quoteValue = function (string $value): string {
            return "'$value'";
        };
        $platform->shouldReceive('quoteStringLiteral')->zeroOrMoreTimes()->withAnyArgs()->andReturnUsing($quoteValue);
        /** @var AbstractPlatform $platform */
        $sqlDeclaration = $type->getSQLDeclaration([EnumType::TYPE_NAME => ['value1', 'value2']], $platform);

        $this->assertEquals("ENUM('value1','value2')", $sqlDeclaration);
    }
}
