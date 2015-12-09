<?php
/**
 * User: Christian Augustine
 * Date: 10/3/15
 * Time: 11:17 AM
 */

namespace Min;

use Illuminate\Database\Eloquent\Collection;
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
     * @param string $relationship
     * @param int $id
     * @return $this|\Illuminate\Database\Eloquent\Relations\Relation
     */
    public function fetchRelationship($relationship, $id = null)
    {
        $relationship = str_plural(lcfirst($relationship));

        if(!is_null($id)) {
            // Fetch by id
            $instance = $this->$relationship()->where('id', $id)->first();
            if (is_null($instance)) {
                throw new ModelNotFoundException(static::getRelativeClassName() . "::" . str_singular($relationship));
            }
            
            return $instance;
        } else {
            // Fetch collection
            $collection = $this->$relationship();
            
            return $collection;
        }
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
