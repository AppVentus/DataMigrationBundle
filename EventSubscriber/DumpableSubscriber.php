<?php

namespace AppVentus\DataMigrationBundle\EventSubscriber;

use AppVentus\DataMigrationBundle\Helper\EntityDumpableHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * ref: appventus.data_migration.subscriber.dumpable_subscriber.
 */
class DumpableSubscriber implements EventSubscriber
{
    protected $container = null;

    protected $entitiesNotTracked = [
        'AppVentus\DataMigrationBundle\Entity\MigrationEntityReference',
        'AppVentus\DataMigrationBundle\Entity\MigrationVersion',
    ];

    /**
     * Constructor.
     *
     * @param EntityDumpableHelper $dumpHelper
     */
    public function __construct($container)
    {
        //we use the container to avoid circular references
        $this->container = $container;
    }

    /**
     * bind to LoadClassMetadata method.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        //the tracked events
        $events = [
            'postPersist',
            'postUpdate',
            'preRemove', ];

        return $events;
    }

    /**
     * On persist on entities, check if the entity is dumpable.
     *
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->testDumpableEntity($event, EntityDumpableHelper::ACTION_CREATE);
    }

    /**
     * On update on entities, check if the entity is dumpable.
     *
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->testDumpableEntity($event, EntityDumpableHelper::ACTION_UPDATE);
    }

    /**
     * On delete on entities, check if the entity is dumpable.
     *
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $this->testDumpableEntity($event, EntityDumpableHelper::ACTION_DELETE);
    }

    /**
     * Test if the entity is dumpable and dumps it if it is the case.
     *
     * @param LifecycleEventArgs $event
     * @param string             $action
     */
    protected function testDumpableEntity(LifecycleEventArgs $event, $action)
    {
        $entity = $event->getEntity();

        //is a dumpable entity
        $dumpable = $this->isAppVentusDumpableEntity($entity);
        if ($dumpable) {
            $dumpHelper = $this->container->get('appventus.data_migration.helper.dump_helper');
            $dumpHelper->dumpEntity($action, $entity);
        }
    }

    /**
     * Is the entity a dumpable one.
     *
     * @param Entity $entity
     *
     * @return bool
     */
    protected function isAppVentusDumpableEntity($entity)
    {
        $isDumpableEntity = false;
        $trackAll = false;

        $dumpHelper = $this->container->get('appventus.data_migration.helper.dump_helper');

        $dumpableEntities = $dumpHelper->getDumpableEntities();

        $dumpableInstanceEntities = $dumpHelper->getDumpableInstanceEntities();

        //no entities are listed, so we track all entities
        if ((count($dumpableEntities) === 0) && (count($dumpableInstanceEntities) === 0)) {
            $trackAll = true;
        }

        $entityClass = get_class($entity);

        //we do not track entities of the data migration bundle
        if (!in_array($entityClass, $this->entitiesNotTracked)) {
            //we track all entities
            if ($trackAll) {
                $isDumpableEntity = true;
            } else {
                //is the class of the entity in the array
                if (in_array($entityClass, $dumpableEntities)) {
                    //yes so let us dump it
                    $isDumpableEntity = true;
                }

                //is the class an instance of dumpableInstanceEntities
                foreach ($dumpableInstanceEntities as $dumpableInstanceEntity) {
                    //get the list of parents of the class and the class itself
                    $parents = class_parents($entity);
                    $parents[$entityClass] = $entityClass;

                    //is the class of the entity in the array
                    if (in_array($dumpableInstanceEntity, $parents)) {
                        //yes so let us dump it
                        $isDumpableEntity = true;
                    }
                }
            }
        }

        return $isDumpableEntity;
    }
}
