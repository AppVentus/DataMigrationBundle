<?php

namespace AppVentus\DataMigrationBundle\Helper;


use AppVentus\DataMigrationBundle\Entity\Migration;
use AppVentus\DataMigrationBundle\Serializer\Normalizer\DumpableEntityDenormalizer;
use AppVentus\DataMigrationBundle\Serializer\Normalizer\DumpableEntityNormalizer;

/**
 * Helper for the dumpable entities.
 *
 * @author Thomas Beaujean <thomas@appventus.com>
 *
 * ref: appventus.data_migration.helper.migration_helper
 */
class MigrationHelper
{
    protected $em = null;
    protected $normalizer = null;
    protected $migrationEntityReferenceHelper = null;
    protected $migrationVersionHelper = null;

    /**
     * Constructor.
     *
     * @param EntityManager                  $entityManager
     * @param DumpableEntityNormalizer       $normalizer
     * @param DumpableEntityDenormalizer     $entityDenormalizer
     * @param MigrationEntityReferenceHelper $migrationEntityReferenceHelper
     * @param MigrationVersionHelper         $migrationVersionHelper
     */
    public function __construct(
        $entityManager,
        DumpableEntityNormalizer $normalizer,
        DumpableEntityDenormalizer $entityDenormalizer,
        MigrationEntityReferenceHelper $migrationEntityReferenceHelper,
        MigrationVersionHelper $migrationVersionHelper)
    {
        $this->em = $entityManager;
        $this->normalizer = $normalizer;
        $this->entityDenormalizer = $entityDenormalizer;
        $this->migrationEntityReferenceHelper = $migrationEntityReferenceHelper;
        $this->migrationVersionHelper = $migrationVersionHelper;
    }

    /**
     * Generate a migration.
     *
     * @param string $action
     * @param Entity $entity
     *
     * @throws \Exception
     *
     * @return Migration
     */
    public function generateMigration($action, $entity)
    {
        //the converter for the entity
        $converter = $this->normalizer;
        $migrationEntityReferenceHelper = $this->migrationEntityReferenceHelper;

        $migration = new Migration();
        $migration->setAction($action);

        $entityClass = get_class($entity);
        $migration->setClass($entityClass);
        $migration->setEntityId($entity->getId());

        unset($entityClass);

        $data = null;

        switch ($action) {
            case EntityDumpableHelper::ACTION_CREATE:
                $reference = $this->generateReference();
                $data = $converter->normalize($entity);
                break;
            case EntityDumpableHelper::ACTION_UPDATE:
                $reference = $migrationEntityReferenceHelper->getReferenceByEntity($entity);
                $data = $converter->normalize($entity);
                break;
            case EntityDumpableHelper::ACTION_DELETE:
                $reference = $migrationEntityReferenceHelper->getReferenceByEntity($entity);
                break;
            default:
                throw new \Exception('The action '.$action.' is not handeld by the EntityDumpableHelper. See the EntityDumpableHelper ACTION constants availables.');
        }

        $migration->setReference($reference);
        $migration->setData($data);

        return $migration;
    }

    /**
     * Generate a reference for the entity.
     *
     * @return string The new reference
     */
    protected function generateReference()
    {
        //we do not want a comma in the reference
        $reference = microtime(true) * 10000;

        return $reference;
    }

    /**
     * Run a migration.
     *
     * @param Migration $migration
     *
     * @SuppressWarnings cyclomaticComplexity
     *
     * @throws \Exception
     */
    public function runMigration(Migration $migration)
    {
        //services
        $em = $this->em;
        $entityDenormalizer = $this->entityDenormalizer;
        $migrationEntityReferenceHelper = $this->migrationEntityReferenceHelper;
        $migrationVersionHelper = $this->migrationVersionHelper;

        $action = $migration->getAction();
        $entityId = $migration->getEntityId();
        $reference = $migration->getReference();//the entity reference
        $class = $migration->getClass();
        $data = $migration->getData();

        switch ($action) {

            case EntityDumpableHelper::ACTION_CREATE:
                //check that this entity has never been inserted
                $entityId = $migrationEntityReferenceHelper->getEntityIdByClassAndReference($class, $reference);
                if ($entityId !== null) {
                    throw new \Exception('The entity '.$class.' with the reference ['.$reference.'] has already been inserted in the database. It can not be inserted a second time.');
                }

                //get an entity from the data
                $entity = $entityDenormalizer->denormalize($data, $class);

                //for a creation the id is auto generated
                $entity->setId(null);

                //persist the entity
                $em->persist($entity);

                //flush the entity
                $em->flush();

                //save the reference/id in the database
                $migrationEntityReferenceHelper->createMigrationEntityReference($entity, $reference);
                break;
            case EntityDumpableHelper::ACTION_UPDATE:
                //if there is a reference, the entity id will be override by the link reference/entityId
                if ($reference !== null) {
                    //get the entity id by the reference
                    $entityId = $migrationEntityReferenceHelper->getEntityIdByClassAndReference($class, $reference);
                    if ($entityId === null) {
                        throw new \Exception('The entity '.$class.' with the reference ['.$reference.'] was not found.');
                    }
                }

                //retrieve the entity
                $entity = $migrationEntityReferenceHelper->getEntityByClassAndId($class, $entityId);

                //test entity
                if ($entity === null) {
                    throw new \Exception('The entity '.$class.' with the id ['.$entityId.'] was not found.');
                }

                //give the entity to the denormalizer
                $context = [];
                $context['entity'] = $entity;

                //get an entity from the data
                $entity = $entityDenormalizer->denormalize($data, $class, null, $context);

                //persist the entity
                $em->persist($entity);

                //flush the entity
                $em->flush();
                break;

            case EntityDumpableHelper::ACTION_DELETE:
                //if there is a reference, the entity id will be override by the link reference/entityId
                if ($reference !== null) {
                    //get the entity id by the reference
                    $entityId = $migrationEntityReferenceHelper->getEntityIdByClassAndReference($class, $reference);
                    if ($entityId === null) {
                        throw new \Exception('The entity '.$class.' with the reference ['.$reference.'] was not found.');
                    }
                }

                //retrieve the entity
                $entity = $migrationEntityReferenceHelper->getEntityByClassAndId($class, $entityId);

                //test entity
                if ($entity === null) {
                    throw new \Exception('The entity '.$class.' with the id ['.$entityId.'] was not found.');
                }

                //persist the entity
                $em->remove($entity);

                //flush the entity
                $em->flush();

                break;

            default:
                throw new \Exception('The action ['.$action.'] is not handeld. The allowed action are listed in the file EntityDumpableHelper');
        }

        //mark the migration as runned create the migration version in the database
        $migrationVersionHelper->createMigrationVersion($migration);
    }

    /**
     * Is the migration new.
     *
     * @param int $migrationId
     *
     * @return bool is the migration a new one
     */
    public function isNewMigration($migrationId)
    {
        $isNew = true;
        //services
        $em = $this->em;

        //the repo
        $repo = $em->getRepository('AppVentusDataMigrationBundle:MigrationVersion');

        //get the migration version
        $migrationVersion = $repo->findOneBy(['id' => $migrationId]);

        //if one was found
        if ($migrationVersion !== null) {
            $isNew = false;
        }

        return $isNew;
    }
}
