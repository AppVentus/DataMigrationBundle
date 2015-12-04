<?php

namespace AppVentus\DataMigrationBundle\Helper;

use AppVentus\DataMigrationBundle\Entity\Migration;
use AppVentus\DataMigrationBundle\Entity\MigrationVersion;
use Doctrine\ORM\EntityManager;

/**
 * Helper for the migration version.
 *
 * @author Thomas Beaujean <thomas@appventus.com>
 *
 * ref: appventus.data_migration.helper.migration_version_helper
 */
class MigrationVersionHelper
{
    protected $em = null;

    /**
     * Constructor.
     *
     * @param EntityManager $entityConverter
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Create the migration version.
     *
     * @param Migration $migration The migration
     */
    public function createMigrationVersion(Migration $migration)
    {
        //the entity manager
        $em = $this->em;

        //the id of the migration
        $migrationId = $migration->getId();

        $migrationVersion = new MigrationVersion();
        $migrationVersion->setId($migrationId);

        $em->persist($migrationVersion);
        $em->flush();
    }
}
