<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 19/02/14
 * Time: 12:04
 */

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
     * @param ObjectInterface $object
     *
     * @return bool
     */
    public function isNew(ObjectInterface $object)
    {
        $isNew = false;

        $primary = $object->toArrayPrimary();

        foreach ($primary as &$value) {
            if (is_null($value)) {
                $isNew = true;
                break;
            }
        }

        return $isNew;
    }

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
                $value = $this->getLastInsertValue($key);
            }

            $object->fromArray($primary);
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
        if ($this->isNew($object)) {
            $result = $this->insertObject($object);

            return $result;
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
