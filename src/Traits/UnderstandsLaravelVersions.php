<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

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
