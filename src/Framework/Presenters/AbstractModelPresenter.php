<?php

namespace Hyn\Framework\Presenters;

use Laracasts\Presenter\Presenter;

abstract class AbstractModelPresenter extends Presenter
{
    /**
     * Required name.
     *
     * @return string
     */
    abstract public function name();

    /**
     * Name for use in url's.
     *
     * @return string
     */
    public function urlName()
    {
        return str_replace([' ', '/'], '+', $this->name());
    }

    /**
     * @return array
     */
    public function urlArguments()
    {
        return [
            'id'   => $this->id,
            'name' => $this->name(),
        ];
    }
}
