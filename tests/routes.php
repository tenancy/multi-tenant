<?php

Route::get('/', function()
{
    return Response::json(App::make('tenant.view'));
});