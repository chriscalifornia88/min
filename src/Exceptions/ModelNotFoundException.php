<?php
/**
 * User: Christian Augustine
 * Date: 10/3/15
 * Time: 12:26 PM
 */

namespace Min\Exceptions;

use BaseModel;

class ModelNotFoundException extends \Illuminate\Database\Eloquent\ModelNotFoundException
{
    public function __construct($name, \Exception $previous = null)
    {
        parent::__construct("$name not found", ErrorCode::MODEL_NOT_FOUND, $previous);
    }
}
