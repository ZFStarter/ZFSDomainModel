<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 19/02/14
 * Time: 12:04
 */

namespace DomainModel\Gateway;

use DomainModel\Object\DomainObjectInterface;
use Zend\Db\TableGateway\TableGateway as BaseTableGateway;

/**
 * Class DomainTableGateway
 * @package DomainModel\Gateway
 */
class DomainTableGateway extends BaseTableGateway
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
     * @param DomainObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function insertObject(DomainObjectInterface $object)
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
     * @param DomainObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function updateObject(DomainObjectInterface $object)
    {
        return $this->update($object->toArray(), $object->toArrayPrimary());
    }

    /**
     * @param DomainObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function saveObject(DomainObjectInterface $object)
    {
        $isNew = false;

        $primary = $object->toArrayPrimary();

        foreach ($primary as &$value) {
            if (is_null($value)) {
                $isNew = true;
                break;
            }
        }

        if ($isNew) {
            $result = $this->insertObject($object);

            return $result;
        } else {
            return $this->updateObject($object);
        }
    }

    /**
     * @param DomainObjectInterface $object
     *
     * @return int Affected rows amount
     */
    public function deleteObject(DomainObjectInterface $object)
    {
        return $this->delete($object->toArrayPrimary());
    }
}
