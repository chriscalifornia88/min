<?php
/**
 * User: Christian Augustine
 * Date: 10/3/15
 * Time: 11:17 AM
 */

namespace Min;

use Min\Exceptions\ModelNotFoundException;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model
 */
abstract class BaseModel extends Model
{
    /**
     * Return an instance of the model
     * @param $id
     * @return $this
     */
    public static function fetch($id)
    {
        $instance = static::find($id);

        if (is_null($instance)) {
            throw new ModelNotFoundException(static::getRelativeClassName());
        }

        return $instance;
    }

    /**
     * return this class name
     * @return string
     */
    public static function getClassName()
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getName();
    }

    /**
     * Return the class name without the base namespace
     * @return string
     */
    public static function getRelativeClassName()
    {
        $baseClassReflection = new \ReflectionClass(self::class);
        $reflection = new \ReflectionClass(static::class);
        
        // Remove base namespace from class name
        return substr($reflection->getName(), strlen($baseClassReflection->getNamespaceName()) + 1);
    }
}
