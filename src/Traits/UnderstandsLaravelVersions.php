<?php

namespace Hyn\Tenancy\Traits;

trait UnderstandsLaravelVersions
{
    /**
     * @param string $is
     * @param string $operator
     * @return bool
     */
    public function laravelVersionCompare(string $is, $operator = 'eq'): bool
    {
        return version_compare($this->baseLaravelVersion(), $is, $operator);
    }

    /**
     * Loads major, minor version combination.
     *
     * @return string
     */
    protected function baseLaravelVersion(): string
    {
        return substr(app()->version(), 0, 3);
    }
}
