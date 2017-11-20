<?php

namespace Discodian\Core\Database;

use Discodian\Core\Resources\Part;
use Illuminate\Support\Str;

class SaveResource
{
    public function __invoke(Part $part): bool
    {
        $model = new Resource($part->toArray());
        $model->setTable($this->table());

        return $model->save();
    }

    protected function table(Part $part): string
    {
        $class = get_class($part);
        $basename = basename($class);

        return Str::lower(Str::plural($basename));
    }
}
