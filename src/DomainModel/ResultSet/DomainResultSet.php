<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 19/02/14
 * Time: 18:32
 */

namespace DomainModel\ResultSet;

use DomainModel\Object\DomainObjectInterface;
use Zend\Db\ResultSet\AbstractResultSet;

/**
 * Class DomainResultSet
 * @package DomainModel\ResultSet
 */
class DomainResultSet extends AbstractResultSet
{
    /** @var  DomainObjectInterface */
    protected $objectPrototype;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param DomainObjectInterface $objectPrototype
     */
    public function setObjectPrototype($objectPrototype)
    {
        $this->objectPrototype = $objectPrototype;
    }

    /**
     * @return array|mixed|DomainObjectInterface
     */
    public function current()
    {
        $data = parent::current();

        if (is_array($data)) {
            $instance = clone $this->objectPrototype;
            if ($instance instanceof DomainObjectInterface || method_exists($instance, 'fromArray')) {
                $instance->fromArray($data);

                return $instance;
            } else {
                return $data;
            }
        } else {
            return $data;
        }
    }

    /**
     * @return DomainObjectInterface[]
     */
    public function toObjectArray()
    {
        $result = array();

        while ($this->position < $this->count) {
            $result[] = $this->current();
            $this->next();
        }

        return $result;
    }
}
