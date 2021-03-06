<?php

namespace ZFS\DomainModel\Gateway;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractFactory
 * @package ZFS\DomainModel\Gateway
 */
class AbstractFactory implements AbstractFactoryInterface
{
    /** @var array */
    protected $instances = array();

    /** @var array */
    protected $provides = array();

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $name
     * @param string                  $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return isset($this->provides[$requestedName]);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $name
     * @param string                  $requestedName
     *
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!isset($this->instances[$requestedName])) {
            $this->instances[$requestedName] = $this->create($serviceLocator, $requestedName);
        }

        return $this->instances[$requestedName];
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $requestedName
     *
     * @return mixed
     */
    protected function create(ServiceLocatorInterface $serviceLocator, $requestedName)
    {
        return $serviceLocator->get('ZFS\DomainModel\Service')->getTableGateway($this->provides[$requestedName]);
    }
}
