<?php

namespace Api\Commands;

use Illuminate\Contracts\Bus\SelfHandling;
use Api\Traits\JobMutatorTrait;
use Api\Traits\ApiResponseTrait;
use Api\Traits\JobApiTrait;
use Api\Traits\JobValidationTrait;
use Api\Traits\InstantiableTrait;

abstract class Command implements SelfHandling
{
    use JobMutatorTrait,
        JobApiTrait,
        JobValidationTrait,
        ApiResponseTrait,
        InstantiableTrait;
}
