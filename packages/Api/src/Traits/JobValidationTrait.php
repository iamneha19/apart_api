<?php

namespace Api\Traits;

use Psy\Exception\ParseErrorException;

/**
 * Job Validation
 *
 * @author Mohammed Mudasir
 */
trait JobValidationTrait
{
    /**
     * Validation fails
     *
     * @param  \Validator $validator
     * @return mixed
     */
    protected function validationFails($validator)
    {
        $this->rulesShouldBeValid();

        $validator = $validator::make($this->request->all(), $this->rules);

        if ($validator->fails()) {
            return $this->makeErrorResponse($validator->getMessageBag()->all(), 422, true);
        }

        return false;
    }

    private function rulesShouldBeValid()
    {
        if (! isset($this->rules)) {
            throw new ParseErrorException('Rules not set given class.');
        }

        return true;
    }
}
