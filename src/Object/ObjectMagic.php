<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 19/02/14
 * Time: 18:34
 */

namespace ZFS\DomainModel\Object;

/**
 * Class ObjectMagic
 * @package ZFS\DomainModel\Object
 */
class ObjectMagic extends Object
{
    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (method_exists($this, 'get' . $name)) {
            return $this->{'get' . $name}();
        } else {
            return $this->get($name);
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . $name)) {
            $this->{'set' . $name}($value);
        } else {
            $this->set($name, $value);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->$name !== null;
    }
}
