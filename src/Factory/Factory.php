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

use Carbon\Carbon;
use Discodian\Core\Events\Parts\Deleted;
use Discodian\Core\Requests\ResourceRequest;
use Discodian\Parts\Contracts\Registry;
use Discodian\Parts\Part;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @method Part|null get(string $class, string $id)
 * @method void set(Part $part)
 * @method void delete(Part $part)
 * @method void deleteIds(string $class, array $ids)
 * @method Collection all(string $class)
 */
class Factory
{
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var Dispatcher
     */
    protected $events;
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Registry $registry, Dispatcher $events, Repository $repository)
    {
        $this->registry = $registry;
        $this->events = $events;
        $this->repository = $repository;
    }

    public function create(string $class, $data)
    {
        if (is_object($data) && isset($data->d)) {
            $data = (array) $data->d;
        }

        if (Str::startsWith($class, 'Discodian\\Parts\\')) {
            return $this->part($class, $data);
        }

        throw new \InvalidArgumentException("Cannot instantiate $class");
    }

    public function part(string $class, array $data): Part
    {
        /** @var Part $part */
        $part = new $class();

        foreach ($data as $property => $value) {
            if (!$this->carbon($part, $property, $value)
                && !$this->relations($part, $property, $value)
            ) {
                $part->{$property} = $value;
            }
        }

        $this->set($part);

        return $part;
    }

    /**
     * @param Part $part
     * @param string $property
     * @param $value
     * @return bool
     */
    protected function relations(Part $part, string $property, $value): bool
    {
        if ($class = $this->registry->get(Str::singular($property))) {
            if (is_object($value)) {
                $part->{$property} = $this->part($class, (array) $value);
            } elseif (is_array($value)) {
                $set = new Collection();
                foreach ($value as $item) {
                    if (is_object($item)) {
                        $set->push($this->part($class, (array)$item));
                    } elseif (is_string($item)) {
                        $set->push($this->get($class, $item));
                    }
                }
                $part->{$property} = $set;
            }
        } elseif (preg_match('/(?<part>[a-z_]+?)(_id)?$/', $property, $m) &&
            $class = $this->registry->get($m['part'])) {
            if (is_array($value) || is_object($value)) {
                $part->{$m['part']} = $this->part($class, (array)$value);
            } else {
                $part->{$m['part']} = $this->get($class, $value);
            }
        } else {
            return false;
        }

        return true;
    }

    protected function carbon(Part $part, $property, $value): bool
    {
        if (is_string($value) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) {
            $part->{$property} = new Carbon($value);

            return true;
        }

        return false;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->repository, $name)) {
            return call_user_func_array([$this->repository, $name], $arguments);
        }
    }
}
