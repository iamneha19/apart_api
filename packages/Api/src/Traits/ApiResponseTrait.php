<?php

namespace Api\Traits;

/**
 * Trait for API responses
 * Note: This should only be get used when creating an API responses.
 *
 * @author Mohammed Mudasir
 */
trait ApiResponseTrait
{
    /**
     * Make Response with message for API
     *
     * @param type $message
     * @param type $status
     * @param type $code
     * @return type array
     */
    public function makeResponse($message, $status = 'success', $code = 200)
    {
        return [
            'message' => $message,
            'status'  => $status,
            'code'    => $code
        ];
    }

    public function makeCustomResponse(array $array)
    {
        return $array;
    }

    /**
     * Get 200 response
     *
     * @param type $message
     * @param type $results
     * @return type
     */
    public function make200Response($message, $results = [])
    {
        return $this->appendResults($this->makeResponse($message), $results);
    }

    /**
     * Get 400 response
     *
     * @param type $message
     * @param array $results
     * @return type array
     */
    public function make400Response($message, array $results = [])
    {
        return $this->appendResults($this->makeResponse($message, 'validation_failed', 400), $results);
    }

    /**
     * Get 404 response
     *
     * @param type $message
     * @param array $results
     * @return type array
     */
    public function make404Response($message, array $results = [])
    {
        return $this->appendResults($this->makeResponse($message, 'not_found', 404), $results);
    }

    /**
     * Get 422 response
     *
     * @param type $message
     * @param array $results
     * @return type array
     */
    public function make422Response($message, array $results = [])
    {
        return $this->appendResults($this->makeResponse($message, 'error', 422), $results);
    }

    /**
     * Get 500 response
     *
     * @param string $message
     * @param array $results
     * @return type array
     */
    public function make500Response($message, array $results = [])
    {
        return $this->appendResults($this->makeResponse($message, 'error', 500), $results);
    }

    /**
     * Determine response by code
     *
     * @param  string $message
     * @param  integer $code
     * @param  array $results
     * @return mixed
     */
    public function makeResponseByCode($message, $code, array $results = [])
    {
        switch ($code) {
            case 200:
                return $this->make200Response($message, $results);

            case 400:
                return $this->make400Response($message, $results);

            case 404:
                return $this->make404Response($message, $results);

            case 422:
                return $this->make422Response($message, $results);

            case 500:
                return $this->make500Response($message, $results);

            default:
                return $this->make500Response($message, $results);
        }
    }

    /**
     * Append results to given response
     *
     * @param type $response
     * @param type $results
     * @return type array
     */
    protected function appendResults($response, $results = [])
    {
        return count($results) > 0 ? array_merge($response, ['results' => $results]) : $response;
    }

    /**
     * Invalid data
     *
     * @param type $results
     * @return type array
     */
    public function invalidDataResponse($results = [])
    {
        return $this->make422Response('Invalid parameter\'s given.', $results);
    }

}
