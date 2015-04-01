<?php

namespace ZFS\DomainModel\Feature;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\TableGateway\Feature\AbstractFeature;
use Zend\Db\TableGateway\Feature\MetadataFeature;

/**
 * Class FilterColumnsFeature
 * @package ZFS\DomainModel\Feature
 */
class FilterColumnsFeature extends AbstractFeature
{
    /**
     *
     */
    public function preInitialize()
    {
        if (!$this->tableGateway->featureSet->getFeatureByClassName('Zend\Db\TableGateway\Feature\MetadataFeature')) {
            $this->tableGateway->featureSet->addFeature(new MetadataFeature());
            $this->tableGateway->featureSet->setTableGateway($this->tableGateway);
        }
    }

    /**
     * @param Insert $insert
     */
    public function preInsert(Insert $insert)
    {
        $metaColumns = $this->tableGateway->getColumns();

        if (count($metaColumns)) {
            $metaColumns = array_flip($metaColumns);

            $columns = array_flip($insert->getRawState('columns'));
            $columns = array_flip(array_intersect_key($columns, $metaColumns));

            $values = $insert->getRawState('values');
            $values = array_intersect_key($values, $columns);

            $insert->values(array_values($values));
            $insert->columns(array_values($columns));
        }
    }

    /**
     * @param Update $update
     */
    public function preUpdate(Update $update)
    {
        $metaColumns = $this->tableGateway->getColumns();

        if (count($metaColumns)) {
            $metaColumns = array_flip($metaColumns);

            $set = $update->getRawState('set');
            $set = array_intersect_key($set, $metaColumns);

            $update->set($set);
        }
    }
}
