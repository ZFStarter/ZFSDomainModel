<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 13/04/14
 * Time: 22:04
 */

namespace ZFS\DomainModel;

use ZFS\DomainModel\Gateway\TableGateway;
use ZFS\DomainModel\Object\ObjectInterface;
use ZFS\DomainModel\ResultSet\ResultSet;
use ZFS\DomainModel\Service\Options;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Service
 * @package ZFS\DomainModel
 */
class Service implements ServiceLocatorAwareInterface
{
    const DOMAIN_MODEL_ADAPTER          = 'ZFS\DomainModel\Adapter';
    const DOMAIN_MODEL_TABLE_GATEWAY    = 'ZFS\DomainModel\Gateway\TableGateway';
    const DOMAIN_MODEL_RESULT_SET       = 'ZFS\DomainModel\ResultSet\ResultSet';
    const DOMAIN_MODEL_OBJECT_INTERFACE = 'ZFS\DomainModel\Object\ObjectInterface';
    const DOMAIN_MODEL_OBJECT_MAGIC     = 'ZFS\DomainModel\Object\ObjectMagic';

    /** @var ServiceLocatorInterface */
    protected $serviceLocator;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param mixed $data
     *
     * @return TableGateway
     * @throws \RuntimeException
     */
    public function createTableGateway($data)
    {
        //region table gateway
        $tableGatewayClassName =
            isset($data[Options::OPTION_TABLE_GATEWAY]) ? $data[Options::OPTION_TABLE_GATEWAY] : null;

        if ($tableGatewayClassName) {
            $tableGatewayClassReflection = new \ReflectionClass($tableGatewayClassName);

            if (!$tableGatewayClassReflection->isSubclassOf(self::DOMAIN_MODEL_TABLE_GATEWAY)) {
                throw new \RuntimeException('Table gateway class cannot be proceed automatically');
            }
        } else {
            $tableGatewayClassName = self::DOMAIN_MODEL_TABLE_GATEWAY;
        }
        //endregion

        //region tableName
        $tableName = isset($data[Options::OPTION_TABLE_NAME]) ? $data[Options::OPTION_TABLE_NAME] : null;

        if (!$tableName) {
            throw new \RuntimeException('Empty ' . Options::OPTION_TABLE_NAME . ' field');
        }
        //endregion

        //region adapter
        $adapterName =
            isset($data[Options::OPTION_ADAPTER]) ? $data[Options::OPTION_ADAPTER] : self::DOMAIN_MODEL_ADAPTER;
        $adapter     = $this->getServiceLocator()->get($adapterName);
        //endregion

        //region features
        $features = isset($data[Options::OPTION_TABLE_FEATURES]) ? $data[Options::OPTION_TABLE_FEATURES] : null;
        //endregion

        //region result set
        $resultSetPrototypeClassName =
            isset($data[Options::OPTION_RESULT_SET_PROTOTYPE]) ? $data[Options::OPTION_RESULT_SET_PROTOTYPE] : null;

        if ($resultSetPrototypeClassName) {
            $resultSetPrototypeReflection = new \ReflectionClass($resultSetPrototypeClassName);

            if (!$resultSetPrototypeReflection->isSubclassOf(self::DOMAIN_MODEL_RESULT_SET)) {
                throw new \RuntimeException('Result set class cannot be proceed automatically');
            }
        } else {
            $resultSetPrototypeClassName = self::DOMAIN_MODEL_RESULT_SET;
        }
        //endregion

        //region object prototype
        $objectPrototypeClassName =
            isset($data[Options::OPTION_OBJECT_PROTOTYPE]) ? $data[Options::OPTION_OBJECT_PROTOTYPE] : null;

        if ($objectPrototypeClassName) {
            $objectPrototypeReflection = new \ReflectionClass($objectPrototypeClassName);

            if (!$objectPrototypeReflection->isSubclassOf(self::DOMAIN_MODEL_OBJECT_INTERFACE)) {
                throw new \RuntimeException('Object prototype class cannot be proceed automatically');
            }
        } else {
            $objectPrototypeClassName = self::DOMAIN_MODEL_OBJECT_MAGIC;
        }
        //endregion

        //region sql
        $sql = isset($data[Options::OPTION_SQL]) ? $data[Options::OPTION_SQL] : null;

        if ($sql) {
            $sql = new $sql($adapter, $tableName);
        }
        //endregion

        //region creation
        /** @var ObjectInterface $objectPrototype */
        $objectPrototype = new $objectPrototypeClassName();

        if ($objectPrototype instanceof ServiceLocatorAwareInterface) {
            /** @var ServiceLocatorAwareInterface $objectPrototype */
            $objectPrototype->setServiceLocator($this->getServiceLocator());
        }

        /** @var ResultSet $resultSetPrototype */
        $resultSetPrototype = new $resultSetPrototypeClassName();
        $resultSetPrototype->setObjectPrototype($objectPrototype);

        if ($resultSetPrototype instanceof ServiceLocatorAwareInterface) {
            /** @var $resultSetPrototype $objectPrototype */
            $resultSetPrototype->setServiceLocator($this->getServiceLocator());
        }

        /** @var TableGateway $tableGateway */
        $tableGateway = new $tableGatewayClassName($tableName, $adapter, $features, $resultSetPrototype, $sql);
        if ($tableGateway instanceof ServiceLocatorAwareInterface) {
            /** @var ServiceLocatorAwareInterface $tableGateway */
            $tableGateway->setServiceLocator($this->getServiceLocator());
        }

        //endregion

        return $tableGateway;
    }
}
