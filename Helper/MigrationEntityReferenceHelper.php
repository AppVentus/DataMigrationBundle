<?php
namespace AppVentus\DataMigrationBundle\Helper;

use AppVentus\DataMigrationBundle\Entity\Migration;
use AppVentus\DataMigrationBundle\Entity\MigrationVersion;
use AppVentus\DataMigrationBundle\Entity\MigrationEntityReference;
use Doctrine\ORM\EntityManager;

/**
 * Helper for the migration version
 *
 * @author Thomas Beaujean <thomas@appventus.com>
 *
 * ref: appventus.data_migration.helper.migration_entity_reference_helper
 */
class MigrationEntityReferenceHelper
{
    protected $em = null;

    /**
     * Constructor
     *
     * @param EntityManager $entityConverter
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Get the reference of an entity is it exists
     *
     * @param unknown $entity
     *
     * @return string The reference
     */
    public function getReferenceByEntity($entity)
    {
        $entityClass = get_class($entity);
        $entityId = $entity->getId();

        $reference = null;

        $em = $this->em;
        //the repo
        $repo = $em->getRepository('AppVentusDataMigrationBundle:MigrationEntityReference');

        //get the migration entity reference
        $migrationEntityReference = $repo->findOneByClassAndEntityId($entityClass, $entityId);

        //if one was found
        if ($migrationEntityReference !== null) {
            $reference = $migrationEntityReference->getReference();
        }

        return $reference;
    }

    /**
     * Get the reference of an entity is it exists
     *
     * @param string $entityClass The entity class
     * @param string $reference   The reference
     *
     * @return string The reference
     */
    public function getEntityIdByClassAndReference($entityClass, $reference)
    {
        $entityId = null;

        $em = $this->em;
        //the repo
        $repo = $em->getRepository('AppVentusDataMigrationBundle:MigrationEntityReference');

        //get the migration entity reference
        $migrationEntityReference = $repo->findOneByClassAndReference($entityClass, $reference);

        //if one was found
        if ($migrationEntityReference !== null) {
            $entityId = $migrationEntityReference->getEntityId();
        }

        return $entityId;
    }

    /**
     * Get the reference of an entity is it exists
     *
     * @param string $entityClass The entity class
     * @param string $reference   The reference
     *
     * @return string The reference
     */
    public function getEntityIdByClassesAndReference($entityClasses, $reference)
    {
        $entityId = null;

        $em = $this->em;
        //the repo
        $repo = $em->getRepository('AppVentusDataMigrationBundle:MigrationEntityReference');

        //get the migration entity reference
        $migrationEntityReference = $repo->findOneByClassesAndReference($entityClasses, $reference);

        //if one was found
        if ($migrationEntityReference !== null) {
            $entityId = $migrationEntityReference->getEntityId();
        }

        return $entityId;
    }

    /**
     * Create the migration entity reference
     *
     * @param unknown $entity
     * @param string  $reference
     */
    public function createMigrationEntityReference($entity, $reference)
    {
        //the entity manager
        $em = $this->em;

        $entityClass = get_class($entity);
        $entityId = $entity->getId();

        $migrationEntityReference = new MigrationEntityReference();
        $migrationEntityReference->setClass($entityClass);
        $migrationEntityReference->setEntityId($entityId);
        $migrationEntityReference->setReference($reference);

        $em->persist($migrationEntityReference);
        $em->flush();
    }


    /**
     * Get the entity by its class and id
     *
     * @param string class
     * @param string id
     *
     * @return Entity The entity
     */
    public function getEntityByClassAndId($class, $id)
    {
        $reference = null;

        $em = $this->em;
        //the repo
        $repo = $em->getRepository($class);

        //get the migration entity reference
        $entity = $repo->findOneBy(array('id' => $id));

        return $entity;
    }

}
