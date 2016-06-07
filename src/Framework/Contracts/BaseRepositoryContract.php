<?php

namespace Hyn\Framework\Contracts;

use Closure;

interface BaseRepositoryContract
{
    /**
     * Creates and optionally saves an object.
     *
     * @param array $attributes
     * @param bool  $save
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes, $save = true);

    /**
     * Create a pagination object.
     *
     * @param int $per_page
     *
     * @return mixed
     */
    public function paginated($per_page = 20);

    /**
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newInstance($type = null);

    /**
     * Starts a querybuilder.
     *
     * @param $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryBuilder($type = null);

    /**
     * Query results for ajax.
     *
     * @param              $name
     * @param null         $type
     * @param Closure|null $additionalWhere
     *
     * @return mixed
     */
    public function ajaxQuery($name, $type = null, Closure $additionalWhere = null);

    /**
     * Finds an object by Id.
     *
     * @param int  $id
     * @param bool $softDeleted
     *
     * @return \Illuminate\Support\Collection|null|void|static
     */
    public function findById($id, $softDeleted = true);

    /**
     * Get all results from database.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();
}
