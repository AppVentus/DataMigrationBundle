<?php

namespace AppVentus\DataMigrationBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a dumpable entity.
 *
 * @author Thomas Beaujean
 *
 * ref: appventus.data_migration.serializer.migration_normalizer
 */
class MigrationNormalizer implements NormalizerInterface
{
    /**
     * Normalize the entity.
     *
     * @param unknown $object
     * @param string  $format
     * @param array   $context
     *
     * @throws \Exception
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     *
     * @return array
     */
    public function normalize($migration, $format = null, array $context = [])
    {
        $array = [];

        $array['id'] = $migration->getId();
        $array['action'] = $migration->getAction();
        $array['reference'] = $migration->getReference();
        $array['class'] = $migration->getClass();
        $array['entityId'] = $migration->getEntityId();
        $array['date'] = $migration->getDate()->format('Y-m-d H:i:s');
        $array['data'] = $migration->getData();

        return $array;
    }

    /**
     * Does the normalizer support this data.
     *
     * @param unknown $data
     * @param string  $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}
