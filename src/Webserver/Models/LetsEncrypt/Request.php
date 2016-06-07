<?php

namespace Hyn\Webserver\Models\LetsEncrypt;

use Hyn\MultiTenant\Abstracts\Models\SystemModel;

class Request extends SystemModel
{
    /**
     * Database table.
     *
     * @var string
     */
    protected $table = 'ssl_lets_encrypt_requests';

    /**
     * The attributes on the model that should be Datetime Carbon objects.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'solved_at', 'expires_at'];

    /**
     * Hostname this Lets Encrypt request belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hostname() {
        return $this->belongsTo('Hyn\MultiTenant\Models\Hostname');
    }
}