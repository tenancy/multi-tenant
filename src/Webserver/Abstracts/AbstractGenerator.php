<?php

namespace Hyn\Webserver\Abstracts;

/**
 * Class AbstractGenerator.
 */
abstract class AbstractGenerator
{
    /**
     * @return string
     */
    abstract public function name();

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    abstract public function onRename($from, $to);

    /**
     * @return bool
     */
    abstract public function onCreate();

    /**
     * @return bool
     */
    abstract public function onUpdate();

    /**
     * @return bool
     */
    abstract public function onDelete();
}
