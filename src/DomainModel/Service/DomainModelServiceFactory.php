<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 13/04/14
 * Time: 22:46
 */

namespace DomainModel\Service;

use DomainModel\DomainModelService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DomainModelServiceFactory
 * @package DomainModel\Service
 */
class DomainModelServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DomainModelService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new DomainModelService();
        $service->setServiceLocator($serviceLocator);

        return $service;
    }
}
