<?php

namespace Hyn\Webserver\Generators\Unix;

use Hyn\Tenancy\Models\Website;
use Hyn\Webserver\Generators\AbstractUserGenerator;

class WebsiteUser extends AbstractUserGenerator
{
    /**
     * @var Website
     */
    protected $website;

    /**
     * @param Website $website
     */
    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * @return bool
     */
    public function onUpdate()
    {
        if (! $this->exists()) {
            return $this->onCreate();
        } elseif ($this->name() && $this->website->isDirty('identifier')) {
            return $this->onRename($this->website->getOriginal('identifier'), $this->website->name());
        }
    }

    /**
     * Tests whether a user exists.
     *
     * @return bool
     */
    public function exists()
    {
        if ($this->name()) {
            exec(sprintf('getent passwd %s', $this->name()), $out);

            return count($out) > 0;
        }
    }

    /**
     * Unique username.
     *
     * @return string
     */
    public function name()
    {
        return $this->website->websiteUser;
    }

    /**
     * Creates the user on the service.
     *
     * @return bool
     */
    public function onCreate()
    {
        if (!$this->exists() && $this->name()) {
            return exec(sprintf('adduser %s --home %s --ingroup %s --no-create-home --disabled-password --disabled-login --gecos "" --shell /bin/false',
                $this->name(),
                base_path(),
                config('webserver.group')));
        }
    }

    /**
     * Renames a user.
     *
     * @return bool
     */
    public function onRename($from, $to)
    {
        if ($this->name()) {
            return exec(sprintf('usermod -l %s %s', $to, $from));
        }
    }

    /**
     * Removes the user from the service.
     *
     * @return bool
     */
    public function onDelete()
    {
        if ($this->exists() && $this->name()) {
            return exec(sprintf('deluser %s', $this->name()));
        }
    }
}
