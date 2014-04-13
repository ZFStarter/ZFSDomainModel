<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 19/02/14
 * Time: 12:04
 */

namespace DomainModel\Gateway;

use DomainModel\Object\DomainObject;
use Zend\Db\TableGateway\TableGateway as BaseTableGateway;

/**
 * Class DomainTableGateway
 * @package DomainModel\Gateway
 */
class DomainTableGateway extends BaseTableGateway
{
    /**
     * @param DomainObject $object
     *
     * @return int
     */
    public function insertObject(DomainObject $object)
    {
        return $this->insert($object->toArray());
    }

    /**
     * @param DomainObject $object
     *
     * @return int
     */
    public function updateObject(DomainObject $object)
    {
        return $this->update($object->toArray(), $object->toArrayPrimary());
    }

    /**
     * @param DomainObject $object
     *
     * @return int
     */
    public function saveObject(DomainObject $object)
    {
        if ($object->new) {
            $result = $this->insertObject($object);

            if ($result) {
                $object->new = false;
            }

            return $result;
        } else {
            return $this->updateObject($object);
        }
    }

    /**
     * @param DomainObject $object
     *
     * @return int
     */
    public function deleteObject(DomainObject $object)
    {
        return $this->delete($object->toArrayPrimary());
    }
}
