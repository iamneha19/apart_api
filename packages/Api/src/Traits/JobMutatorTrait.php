<?php

namespace Api\Traits;

/**
 * Getter and setter of jobs
 *
 * @author Garima Singh, Mohammed Mudasir
 */
trait JobMutatorTrait
{
    /**
     * Get error
     *
     * @return string | array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get message
     *
     * @return string | array
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get status code of current results
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return isset($this->statusCode) ? $this->statusCode : 200;
    }

    /**
     * Get results
     *
     * @return array
     */
    public function getResults()
    {
        return isset($this->results) ? $this->results : [];
    }

    /**
     * Set error
     *
     * @return self instance
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Set message
     *
     * @return self instance
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set status code
     *
     * @return self instance
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Set results
     *
     * @return self instance
     */
    public function setResults(array $result)
    {
        $this->results = $result;

        return $this;
    }

    /**
     * Set the scope of the job
     *
     * @param  $job
     */
    public function setScope($job)
    {
        $this->setError($job->getError())
             ->setMessage($job->getMessage())
             ->setStatusCode($job->getStatusCode())
             ->setResults($job->getResults());
    }
}
