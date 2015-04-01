<?php

namespace ZFS\DomainModel\Gateway;

use ZFS\DomainModel\Object\ObjectInterface;
use Zend\Db\TableGateway\TableGateway as BaseTableGateway;

/**
 * Class TableGateway
 * @package ZFS\DomainModel\Gateway
 */
class TableGateway extends BaseTableGateway
{
    /**
     * @param string|null $name
     *
     * @return int
     */
    public function getLastInsertValue($name = null)
    {
        return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue($name);
    }

    /**
     * @param ObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function insertObject(ObjectInterface $object)
    {
        $insertResult = $this->insert($object->toArray());

        if ($insertResult) {
            $primary = $object->toArrayPrimary();

            foreach ($primary as $key => &$value) {
                $insertedValue = $this->getLastInsertValue($key);

                if ($insertedValue) {
                    $value = $insertedValue;
                }
            }

            unset($value);

            $object->fromArray($primary);
            $object->isNew(false);
        }

        return $insertResult;
    }

    /**
     * @param ObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function updateObject(ObjectInterface $object)
    {
        return $this->update($object->toArray(), $object->toArrayPrimary());
    }

    /**
     * @param ObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function saveObject(ObjectInterface $object)
    {
        if ($object->isNew()) {
            return $this->insertObject($object);
        } else {
            return $this->updateObject($object);
        }
    }

    /**
     * @param ObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function deleteObject(ObjectInterface $object)
    {
        return $this->delete($object->toArrayPrimary());
    }
}
