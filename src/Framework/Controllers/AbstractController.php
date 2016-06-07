<?php

namespace Hyn\Framework\Controllers;

use Hyn\Framework\Models\AbstractModel;
use Hyn\ManagementInterface\Form\Generator;
use Illuminate\Routing\Controller;
use View;

abstract class AbstractController extends Controller
{
    /**
     * Sets a variable into the views.
     *
     * @param $key
     * @param $value
     */
    protected function setViewVariable($key, $value)
    {
        View::share($key, $value);
    }

    /**
     * Parses requests to the controller for interactions with models.
     *
     * @param AbstractModel $model
     * @param Generator     $form
     *
     * @return $this|bool|AbstractModel|null
     */
    protected function catchFormRequest($closure, Generator $form)
    {
        $processedRequest = $form->processRequest();

        $this->setViewVariable('form', $form);

        return $processedRequest ?: $closure();
    }

    /**
     * Shows a confirmation page.
     *
     * @param AbstractModel $model
     * @param null|string   $view
     *
     * @return View
     */
    protected function showConfirmMessage(AbstractModel $model, Generator $form, $view = null)
    {
        return $this->catchFormRequest(function () use ($view, $model) {
            return view($view ?: 'management-interface::template.forms.confirm-delete', [
                'model' => $model,
            ]);
        }, $form);
    }
}
