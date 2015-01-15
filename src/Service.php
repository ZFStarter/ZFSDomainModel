<?php

namespace ZFS\DomainModel;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\Feature\AbstractFeature;
use ZFS\DomainModel\Exception\RuntimeException;
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

    //region TableGateway creation
    /**
     * @param array $data
     *
     * @return TableGateway
     */
    protected function getTableGatewayFromFactory($data)
    {
        if (!isset($data[Options::OPTION_TABLE_GATEWAY])) {
            return null;
        }

        if (!$this->getServiceLocator()->has($data[Options::OPTION_TABLE_GATEWAY])) {
            return null;
        }

        $tableGateway = $this->getServiceLocator()->get($data[Options::OPTION_TABLE_GATEWAY]);

        if ($tableGateway instanceof TableGateway) {
            return $tableGateway;
        } else {
            throw new RuntimeException(
                'TableGateway has to be an instance of \ZFS\DomainModel\Gateway\TableGateway class'
            );
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getTableGatewayClassName($data)
    {
        if (!isset($data[Options::OPTION_TABLE_GATEWAY])) {
            return self::DOMAIN_MODEL_TABLE_GATEWAY;
        }

        if ($data[Options::OPTION_TABLE_GATEWAY] == self::DOMAIN_MODEL_TABLE_GATEWAY) {
            return self::DOMAIN_MODEL_TABLE_GATEWAY;
        }

        $tableGatewayClassReflection = new \ReflectionClass($data[Options::OPTION_TABLE_GATEWAY]);

        if ($tableGatewayClassReflection->isSubclassOf(self::DOMAIN_MODEL_TABLE_GATEWAY)) {
            return $data[Options::OPTION_TABLE_GATEWAY];
        } else {
            throw new RuntimeException(
                'TableGateway has to be an instance of \ZFS\DomainModel\Gateway\TableGateway class'
            );
        }
    }

    /**
     * @param mixed $data
     *
     * @return TableGateway
     * @throws \RuntimeException
     */
    public function getTableGateway($data)
    {
        $tableGateway = $this->getTableGatewayFromFactory($data);

        if ($tableGateway instanceof TableGateway) {
            return $tableGateway;
        } else {
            $tableGatewayClassName = $this->getTableGatewayClassName($data);

            /** @var TableGateway $tableGateway */
            $tableGateway = new $tableGatewayClassName(
                $this->getTableName($data),
                $this->getAdapter($data),
                $this->getTableFeatures($data),
                $this->getResultSetPrototype($data),
                $this->getSql($data)
            );

            if ($tableGateway instanceof ServiceLocatorAwareInterface) {
                /** @var ServiceLocatorAwareInterface $tableGateway */
                $tableGateway->setServiceLocator($this->getServiceLocator());
            }

            return $tableGateway;
        }
    }
    //endregion

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getTableName($data)
    {
        if (!isset($data[Options::OPTION_TABLE_NAME])) {
            throw new \RuntimeException('Empty ' . Options::OPTION_TABLE_NAME . ' field');
        }

        return $data[Options::OPTION_TABLE_NAME];
    }

    /**
     * @param array $data
     *
     * @return Adapter
     */
    protected function getAdapter($data)
    {
        if (isset($data[Options::OPTION_ADAPTER])) {
            $adapter = $data[Options::OPTION_ADAPTER];
        } else {
            $adapter = self::DOMAIN_MODEL_ADAPTER;
        }

        if (!$this->getServiceLocator()->has($adapter)) {
            throw new RuntimeException($adapter . ' not found in Service Locator');
        }

        $adapter = $this->getServiceLocator()->get($adapter);

        if ($adapter instanceof Adapter) {
            return $adapter;
        } else {
            throw new RuntimeException('Adapter has to be an instance of \Zend\Db\Adapter\Adapter class');
        }
    }

    //region TableFeatures creation
    /**
     * @param array $data
     *
     * @return array|AbstractFeature[]
     */
    protected function getTableFeatures($data)
    {
        if (!isset($data[Options::OPTION_TABLE_FEATURES])) {
            return null;
        }

        if (is_array($data[Options::OPTION_TABLE_FEATURES])) {
            foreach ($data[Options::OPTION_TABLE_FEATURES] as &$feature) {
                if (is_string($feature)) {
                    if ($this->getServiceLocator()->has($feature)) {
                        $feature = $this->getServiceLocator()->get($feature);
                    } else {
                        /** @var AbstractFeature $feature */
                        $feature = new $feature;

                        if ($feature instanceof ServiceLocatorAwareInterface) {
                            /** @var ServiceLocatorAwareInterface $feature */
                            $feature->setServiceLocator($this->getServiceLocator());
                        }
                    }
                }

                if (!$feature instanceof AbstractFeature) {
                    throw new RuntimeException(
                        'Feature has to be an instance of \Zend\Db\TableGateway\Feature\AbstractFeature class'
                    );
                }
            }
        } else {
            return null;
        }
    }
    //endregion

    //region ResultSetPrototype creation
    /**
     * @param array $data
     *
     * @return null|ResultSet
     */
    protected function getResultSetPrototypeFromFactory($data)
    {
        if (!isset($data[Options::OPTION_RESULT_SET_PROTOTYPE])) {
            return null;
        }

        if (!$this->getServiceLocator()->has($data[Options::OPTION_RESULT_SET_PROTOTYPE])) {
            return null;
        }

        $resultSetPrototype = $this->getServiceLocator()->get($data[Options::OPTION_RESULT_SET_PROTOTYPE]);

        if ($resultSetPrototype instanceof ResultSet) {
            return $resultSetPrototype;
        } else {
            throw new RuntimeException(
                'ResultSet has to be an instance of \ZFS\DomainModel\ResultSet\ResultSet class'
            );
        }
    }

    protected function getResultSetPrototypeClassName($data)
    {
        if (!isset($data[Options::OPTION_RESULT_SET_PROTOTYPE])) {
            return self::DOMAIN_MODEL_RESULT_SET;
        }

        if ($data[Options::OPTION_RESULT_SET_PROTOTYPE] == self::DOMAIN_MODEL_RESULT_SET) {
            return self::DOMAIN_MODEL_RESULT_SET;
        }

        $resultSetPrototypeReflection = new \ReflectionClass($data[Options::OPTION_RESULT_SET_PROTOTYPE]);

        if ($resultSetPrototypeReflection->isSubclassOf(self::DOMAIN_MODEL_RESULT_SET)) {
            return $data[Options::OPTION_RESULT_SET_PROTOTYPE];
        } else {
            throw new RuntimeException('Result has to be an instance of \ZFS\DomainModel\ResultSet\ResultSet class');
        }
    }

    /**
     * @param array $data
     *
     * @return ResultSet
     */
    protected function getResultSetPrototype($data)
    {
        $resultSetPrototype = $this->getResultSetPrototypeFromFactory($data);

        if (!$resultSetPrototype) {
            $resultSetPrototypeClassName = $this->getResultSetPrototypeClassName($data);

            /** @var ResultSet $resultSetPrototype */
            $resultSetPrototype = new $resultSetPrototypeClassName();
            $resultSetPrototype->setObjectPrototype($this->getObjectPrototype($data));

            if ($resultSetPrototype instanceof ServiceLocatorAwareInterface) {
                /** @var ServiceLocatorAwareInterface $resultSetPrototype */
                $resultSetPrototype->setServiceLocator($this->getServiceLocator());
            }
        }

        return $resultSetPrototype;
    }
    //endregion

    //region ObjectPrototype creation
    /**
     * @param array $data
     *
     * @return null|ObjectInterface
     */
    protected function getObjectPrototypeFromFactory($data)
    {
        if (!isset($data[Options::OPTION_OBJECT_PROTOTYPE])) {
            return null;
        }

        if (!$this->getServiceLocator()->has($data[Options::OPTION_OBJECT_PROTOTYPE])) {
            return null;
        }

        $objectPrototype = $this->getServiceLocator()->get($data[Options::OPTION_OBJECT_PROTOTYPE]);

        if ($objectPrototype instanceof ObjectInterface) {
            return $objectPrototype;
        } else {
            throw new RuntimeException(
                'ObjectPrototype has to be an instance of \ZFS\DomainModel\Object\ObjectInterface class'
            );
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getObjectPrototypeClassName($data)
    {
        if (!isset($data[Options::OPTION_OBJECT_PROTOTYPE])) {
            return self::DOMAIN_MODEL_OBJECT_MAGIC;
        }

        if ($data[Options::OPTION_OBJECT_PROTOTYPE] == self::DOMAIN_MODEL_OBJECT_MAGIC) {
            return self::DOMAIN_MODEL_OBJECT_MAGIC;
        }

        $objectPrototypeReflection = new \ReflectionClass($data[Options::OPTION_OBJECT_PROTOTYPE]);

        if ($objectPrototypeReflection->isSubclassOf(self::DOMAIN_MODEL_OBJECT_INTERFACE)) {
            return $data[Options::OPTION_OBJECT_PROTOTYPE];
        } else {
            throw new RuntimeException(
                'ObjectPrototype has to be an instance of \ZFS\DomainModel\Object\ObjectInterface class'
            );
        }
    }

    /**
     * @param array $data
     *
     * @return ObjectInterface
     */
    protected function getObjectPrototype($data)
    {
        $objectPrototype = $this->getObjectPrototypeFromFactory($data);

        if (!$objectPrototype) {
            $objectPrototypeClassName = $this->getObjectPrototypeClassName($data);
            /** @var ObjectInterface $objectPrototype */
            $objectPrototype = new $objectPrototypeClassName();

            if ($objectPrototype instanceof ServiceLocatorAwareInterface) {
                /** @var ServiceLocatorAwareInterface $objectPrototype */
                $objectPrototype->setServiceLocator($this->getServiceLocator());
            }
        }

        return $objectPrototype;
    }
    //endregion

    //region Sql creation
    /**
     * @param array $data
     *
     * @return null|Sql
     */
    protected function getSqlFromFactory($data)
    {
        if (!isset($data[Options::OPTION_SQL])) {
            return null;
        }

        if (!$this->getServiceLocator()->has($data[Options::OPTION_SQL])) {
            return null;
        }

        $sql = $this->getServiceLocator()->get($data[Options::OPTION_SQL]);

        if ($sql instanceof Sql) {
            return $sql;
        } else {
            throw new RuntimeException('Sql has to be an instance of \Zend\Db\Sql\Sql class');
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getSqlClassName($data)
    {
        if (!isset($data[Options::OPTION_SQL])) {
            return '\Zend\Db\Sql\Sql';
        }

        $sqlClassReflection = new \ReflectionClass($data[Options::OPTION_SQL]);

        if ($sqlClassReflection->isSubclassOf('\Zend\Db\Sql\Sql')) {
            return $data[Options::OPTION_SQL];
        } else {
            throw new RuntimeException('Sql has to be an instance of \Zend\Db\Sql\Sql class');
        }
    }

    /**
     * @param array $data
     *
     * @return Sql
     */
    protected function getSql($data)
    {
        $sql = $this->getSqlFromFactory($data);

        if ($sql instanceof Sql) {
            return $sql;
        } else {
            $sqlClassName = $this->getSqlClassName($data);

            /** @var Sql $sql */
            $sql = new $sqlClassName(
                $this->getAdapter($data),
                $this->getTableName($data)
            );

            if ($sql instanceof ServiceLocatorAwareInterface) {
                /** @var ServiceLocatorAwareInterface $sql */
                $sql->setServiceLocator($this->getServiceLocator());
            }

            return $sql;
        }
    }
    //endregion
}
