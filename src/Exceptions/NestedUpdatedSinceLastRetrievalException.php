<?php

namespace Mavericks\NovaNestedForm\Exceptions;

use Exception;

class NestedUpdatedSinceLastRetrievalException extends Exception
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 409;

}
