<?php

namespace ZFS\DomainModel\ResultSet;

use ZFS\DomainModel\Object\ObjectInterface;
use Zend\Db\ResultSet\AbstractResultSet;

/**
 * Class ResultSet
 * @package ZFS\DomainModel\ResultSet
 */
class ResultSet extends AbstractResultSet
{
    /** @var  ObjectInterface */
    protected $objectPrototype;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param ObjectInterface $objectPrototype
     */
    public function setObjectPrototype($objectPrototype)
    {
        $this->objectPrototype = $objectPrototype;
    }

    /**
     * @return array|mixed|ObjectInterface
     */
    public function current()
    {
        $data = parent::current();

        if (is_array($data)) {
            $instance = clone $this->objectPrototype;
            if ($instance instanceof ObjectInterface || method_exists($instance, 'fromArray')) {
                $instance->fromArray($data);
                $instance->isNew(false);

                return $instance;
            } else {
                return $data;
            }
        } else {
            return $data;
        }
    }

    /**
     * @return ObjectInterface[]
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
