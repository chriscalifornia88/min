<?php
/**
 * User: Christian Augustine
 * Date: 10/3/15
 * Time: 12:26 PM
 */

namespace Min\Exceptions;

class AccessDeniedException extends \Exception
{
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct("You do not have permission to access this $name", ErrorCode::ACCESS_DENIED, $previous);
    }
}
