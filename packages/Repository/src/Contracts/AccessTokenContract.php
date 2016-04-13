<?php

namespace Repository\Contracts;

interface AccessTokenContract
{
    /**
     * Check whether access token is valid or not
     *
     * @return boolean
     */
    public function isAccessTokenValid($token);
}
