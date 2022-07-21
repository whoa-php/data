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

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Container\ContainerInterface;

/**
 * @package Whoa\Tests\Templates
 */
class TestContainer implements ContainerInterface
{
    private array $data = [];

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $valueOrClosure = $this->data[$id];

        if ($valueOrClosure instanceof Closure) {
            $value = $valueOrClosure($this);
            $this->offsetSet($id, $value);
        } else {
            $value = $valueOrClosure;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @inheritdoc
     * @param $offset
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
