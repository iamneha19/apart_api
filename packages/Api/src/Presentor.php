<?php

namespace Api;

use Api\Traits\ApiResponseTrait;
use Illuminate\Bus\Dispatcher;

/**
 * Api Response Loader
 *
 * @author Mohammed Mudasir
 */
class Presentor
{
    use ApiResponseTrait;

    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function defaultResponse()
    {
        return $this->get404Response('No Data');
    }

    public function defaultJobBehaviour($job)
    {
        if ($this->dispatcher->dispatch($job))
        {
            return $this->makeResponseByCode($job->getMessage(), $job->getStatusCode(), $job->getResults());
        }

        $error = $job->getError();

        if (is_array($error))
        {
            return $this->makeResponseByCode('Validation failed.', $job->getStatusCode(), $error);
        }

        return $this->makeResponseByCode($error, $job->getStatusCode());
    }
}
