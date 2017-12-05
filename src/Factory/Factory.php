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
use Discodian\Core\Events\Parts\Loaded;
use Discodian\Core\Requests\Resource;
use Discodian\Core\Requests\ResourceRequest;
use Discodian\Parts\Contracts\Registry;
use Discodian\Parts\Part;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Factory
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Dispatcher
     */
    private $events;
    /**
     * @var Repository
     */
    private $cache;

    public function __construct(Registry $registry, Dispatcher $events, Repository $cache)
    {
        $this->registry = $registry;
        $this->events = $events;
        $this->cache = $cache;
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

        $this->events->dispatch(new Loaded($part));

        return $part;
    }

    public function delete(Part $part)
    {
        $this->events->dispatch(new Deleted($part));
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
                foreach ($value as $multi) {
                    $set->push($this->part($class, (array)$multi));
                }
                $part->{$property} = $set;
            }
        } elseif (preg_match('/(?<part>)(_id)?$/', $property, $m) &&
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

    public function get(string $class, $id)
    {
        if ($part = $this->cache->get("parts.{$class}.{$id}")) {
            return $part;
        }

        $request = (new Resource())
            ->setPart(new $part)
            ->get($id);

        dd($request);
    }

    protected function carbon(Part $part, $property, $value): bool
    {
        if (is_string($value) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) {
            $part->{$property} = new Carbon($value);

            return true;
        }

        return false;
    }
}
