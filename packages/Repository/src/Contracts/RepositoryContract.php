<?php

namespace Repository\Contracts;

/**
 * Contract for every repository
 *
 * @author Mohammed Mudasir
 */
interface RepositoryContract
{
    /**
     * Map selection on model
     *
     * @return Repository
     */
    public function select();

    /**
     * Will return who many records exist in DB
     *
     * @param  array  $data Fields which need to be checked
     * @return integer       total number of records
     */
    public function exists(array $data);

    /**
     * Run get query
     *
     * @return Model
     */
    public function get();

    /**
     * Find Record by model
     *
     * @param  integer $id
     * @param  bool $returnModel
     * @return Repository | Model
     */
    public function find($id, $returnModel = true);

    /**
     * Show list
     *
     * @return Model
     */
    public function lists();

    /**
     * Create Record
     * @param  array  $data
     * @return bool
     */
    public function create(array $data);

    /**
     * Update record
     *
     * @param  array  $data
     * @return bool
     */
    public function update(array $data);

    /**
     * Delete Record
     * @param  integer $id
     * @return bool
     */
    public function delete($id);

    /**
     * Get model
     * @return Model
     */
    public function getModel();

    /**
     * Get record with pagination
     *
     * @return Model
     */
    public function paginate();

    /**
     * Orderby record
     * @param  string $name
     * @param  string $type ASC | DESC
     * @return Repository
     */
    public function orderBy($name, $type);

    /**
     * Where clause
     *
     * @param  array  $data
     * @return Repository
     */
    public function where(array $data);

    /**
     * Where like Closure
     *
     * @param  string $key
     * @param  string $value
     * @return Repository
     */
    public function whereLike($key, $value);

    /**
     * Where like Closure
     *
     * @param  string $key
     * @param  string $value
     * @return Repository
     */
    public function orWhereLike($key, $value);
}
