<?php

/*
 * This file is part of the Discodian bot toolkit.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://discodian.com
 * @see https://github.com/discodian
 */

namespace Discodian\Core\Factory;

use Discodian\Core\Requests\ResourceRequest;
use Discodian\Parts\Contracts\Registry;
use Discodian\Parts\Part;
use Illuminate\Support\Str;

class Factory
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function create(string $class, array $data = [])
    {
        if (Str::startsWith($class, 'Discodian\\Parts\\')) {
            return $this->part($class, $data);
        }

        throw new \InvalidArgumentException("Cannot instantiate $class");
    }

    public function part(string $class, array $data)
    {
        /** @var Part $part */
        $part = new $class($data);

        foreach ($part->getAttributes() as $property => $value) {
            $this->relations($part, $property, $value);
        }
    }

    protected function relations(Part $part, string $property, $value)
    {
        if (\is_array($value)) {
            $set = [];
            foreach ($value as $multi) {
                $set[] = $this->relations($part, $property, $multi);
            }
            return $set;
        }

        if (preg_match('/(?<part>)_id$/', $property, $m) &&
            $part = $this->registry->get($m['part'])
        ) {
            return $this->get($part, $value);
        }
    }

    public function get(Part $part, $id)
    {
        $request = ($request = new ResourceRequest())
            ->setPart($part);
    }
}
