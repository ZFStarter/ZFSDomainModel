<?php

namespace ZFS\DomainModel\Object;

/**
 * Interface ObjectInterface
 * @package ZFS\DomainModel\Object
 */
interface ObjectInterface
{
    /**
     * @return array
     */
    public function toArrayPrimary();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @param array $array
     */
    public function fromArray(array $array);

    /**
     * @param bool|null $new
     *
     * @return bool
     */
    public function isNew($new = null);
}
