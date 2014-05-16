<?php

namespace AppVentus\DataMigrationBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use AppVentus\DataMigrationBundle\Entity\Migration;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: appventus.data_migration.serializer.migration_denormalizer
 */
class MigrationDenormalizer implements DenormalizerInterface
{
    /**
     * Denormalize a migration
     *
     * @param array $data
     * @param unknown $class
     * @param string $format
     * @param array $context
     * @return \AppVentus\DataMigrationBundle\Entity\Migration
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     *
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $migration = new Migration();

        $migration->setId($data['id']);
        $migration->setAction($data['action']);
        $migration->setReference($data['reference']);
        $migration->setClass($data['class']);
        $migration->setEntityId($data['entityId']);

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date']);
        $migration->setDate($date);
        $migration->setData($data['data']);

        return $migration;
    }

    /**
     * Does the normalizer support this class
     *
     * @param array  $data
     * @param string $type
     * @param string $format
     *
     * @return boolean
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return true;
    }
}
