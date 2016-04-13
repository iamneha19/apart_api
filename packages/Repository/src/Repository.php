<?php

namespace Repository;

use Repository\Contracts\RepositoryContract;
use Repository\Repository;
use Psy\Exception\ErrorException;
use Psy\Exception\FatalErrorException;

/**
 * Repository Abstraction
 *
 * @author Mohammed Mudasir
 */
abstract class Repository implements RepositoryContract
{
    /**
     * Fields which will be manipulated
     *
     * @var array
     */
    protected $fields;

    /**
     * Fields which will be default loaded in every layer of request
     *
     * @var array
     */
    protected $defaultSelection;

    /**
     * Per page how many record should be shown
     * Note: This attribute will only work when paginate method is called
     *
     * @var integer
     */
    protected $perPage = 10;

    /**
     * Set Fields if any overwrite needed
     *
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Select only few column
     *
     * @return Repository
     */
    public function fewSelection()
    {
        $selection = func_num_args() > 0 ? func_get_args() : $this->fewSelection;

        return call_user_func_array([$this, 'select'], $selection);
    }

    /**
     * Get default selection
     *
     * @return array
     */
    protected function getDefaultSelection()
    {
        if (! is_array($this->defaultSelection))
        {
            throw new ErrorException('Default selection should be set as array in Child Repository.');
        }

        return is_array($this->defaultSelection) ? array_reverse($this->defaultSelection) : [];
    }

    /**
     * get selected arguments
     *
     * @param  array  $args
     * @return array
     */
    protected function getSelectArguments(array $args)
    {
        // If default selection is not presents in arguments then add them in arguments
        foreach ($this->getDefaultSelection() as $selection)
        {
            in_array($selection, $args) ?: array_unshift($args, $selection);
        }

        return $args;
    }

    /**
     * {@inheritdoc}
     */
    public function select()
    {
        $args = $this->getSelectArguments(func_get_args());

        $this->model = call_user_func_array([$this->model, 'select'], $args);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return func_num_args() > 0 ?
                $this->model->get(func_get_args()):
                $this->model->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $returnModel = false)
    {
        $this->model = $this->model->find($id);

        if ($returnModel)
        {
            return $this->model;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(array $data)
    {
        return $this->model
                       ->select('id')
                       ->where($data)
                       ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data)
    {
        return $this->model->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->model->whereId($id)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function lists()
    {
        $args = func_num_args() > 0 ? func_get_args() : ['name'];

        return call_user_func_array([$this->model, 'lists'], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($toArray = true)
    {
        $paginate = $this->model->paginate($this->perPage);

        if ($toArray) {
            return $paginate->toArray();
        }

        return $paginate;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy($name, $type)
    {
        $this->model = $this->model->orderBy($name, $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function where(array $data)
    {
        $this->model = $this->model->where($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function whereLike($key, $value)
    {
        if ($value) {
            $this->model = $this->model->where("$key", 'LIKE', "%$value%");
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orWhere($key, $value)
    {
        if ($value) {
            $this->model = $this->model->orWhere($key, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orWhereLike($key, $value)
    {
        if ($value) {
            $this->model = $this->model->orWhere("$key", 'LIKE', "%$value%");
        }

        return $this;
    }

    public function first()
    {
        return $this->model->first();
    }

    /**
     * This will help to remove all current relationship attached to a repository
     *
     * @return \Repository\Repository
     */
    public function resetModel()
    {
        $this->model = $this->model->getModel();

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (str_contains($name, 'with')) {
            return $this->handleWithMethod(method_name($name, 'with'), $arguments);
        }

        throw new FatalErrorException("method $name does not exists on repository.");
    }

    public function handleWithMethod($name, $arguments = ['*'])
    {
        $model = $this->model;

        list($isCallable, $callback) = $this->validCallback($arguments);

        if ($isCallable) {
            $model = $model->with([$name => $callback]);
        } else {
            // Default behaviour of magic with method will be attach in array case
            $model = $model->with([$name => function ($q) use ($arguments) {
                call_user_func_array([$q, 'select'], $arguments);
            }]);
        }

        return $this;
    }

    protected function validCallback($arguments)
    {
        $isCallable = false;
        $callback   = null;

        // Handling first argument as a callback
        if(is_callable($arguments)) {
            $isCallable = true;
            $callback = $arguments;
        }
        // Handling first pocket of an array as callback
        elseif (isset($arguments[0]) and is_callable($arguments[0])) {
            $isCallable = true;
            $callback = $arguments[0];
        }

        return [$isCallable, $callback];
    }
}
