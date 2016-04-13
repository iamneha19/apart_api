<?php

namespace Api\Traits;

use Illuminate\Http\Request;
use PharException;
use Psy\Exception\ParseErrorException;

/**
 * Job which need to response as API
 *
 * @author Mohammed Mudasir
 */
trait JobApiTrait
{
    /**
     * Fields which hold request data
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Current Request
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Set Request property with field property
     *
     * @param \Illuminate\Http\Request | Other request instance $request
     * @throw \ErrorException
     * @return self
     */
    protected function setRequestAndFields($request)
    {
        $this->request = $request;

        if (! isset($this->rules))
        {
            throw new ParseErrorException('Rules need to be set in given class.');
        }

        $this->fields = $this->request->only(array_keys($this->rules));

        return $this;
    }

    /**
     * Get value from fields or request
     *
     * @param  string $name
     * @throw \PharException
     * @return mixed
     */
    protected function get($name, $defaultValue = null)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name];
        }

        if (array_key_exists($name, $this->request->all()))
        {
            return $this->request->get($name, $defaultValue);
        }

        throw new PharException("$name does not exist in fields or request variable.");
    }

    protected function only()
    {
        $keys = [];

        foreach (func_get_args() as $arg)
        {
            $keys[$arg] = $this->get($arg);
        }

        return $keys;
    }

    /**
     * Make response for error
     *
     * @param  string | array $error
     * @param  integer $statusCode
     * @param  bool $return
     * @return bool
     */
    protected function makeErrorResponse($error, $statusCode, $return  = false)
    {
        $this->error = $error;

        $this->statusCode = $statusCode;

        return $return;
    }

    /**
     * Make Success response
     *
     * @param  string | array  $message
     * @param  integer $statusCode
     * @param  bool  $return
     * @return bool
     */
    protected function makeSuccessResponse($message, $statusCode = 200, $return = true)
    {
        $this->message = $message;

        $this->statusCode = $statusCode;

        return $return;
    }
}
