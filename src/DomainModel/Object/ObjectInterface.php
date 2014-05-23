<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 14/04/14
 * Time: 11:40
 */

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
}
