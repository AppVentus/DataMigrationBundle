<?php

namespace AppVentus\DataMigrationBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * MigrationEntityReferenceRepository
 */
class MigrationEntityReferenceRepository extends EntityRepository
{
    /**
     * Get on a migration entity reference by class and id
     *
     * @param string $class
     * @param sintrg $id
     *
     * @return MigrationEntityReference
     */
    public function findOneByClassAndEntityId($class, $id)
    {
        $migrationEntityReference = null;

        $qb = $this->createQueryBuilder('migrationEntityReference');

        $this->filterByClass($qb, $class);
        $this->filterByEntityId($qb, $id);

        $qb->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $migrationEntityReference = $results[0];
        }

        return $migrationEntityReference;
    }

    /**
     * Find a migration entity reference by the class and the reference
     *
     * @param string $class
     * @param string $reference
     * @return Ambigous <NULL, unknown>
     */
    public function findOneByClassAndReference($class, $reference)
    {
        $migrationEntityReference = null;

        $qb = $this->createQueryBuilder('migrationEntityReference');

        $this->filterByClass($qb, $class);
        $this->filterByReference($qb, $reference);

        $qb->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $migrationEntityReference = $results[0];
        }

        return $migrationEntityReference;
    }

    /**
     * Find a migration entity reference by the classes and the reference
     *
     * @param string $classes
     * @param string $reference
     * @return Ambigous <NULL, unknown>
     */
    public function findOneByClassesAndReference($classes, $reference)
    {
        $migrationEntityReference = null;

        $qb = $this->createQueryBuilder('migrationEntityReference');

        $this->filterByClasses($qb, $classes);
        $this->filterByReference($qb, $reference);

        $qb->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        if (count($results) > 0) {
            $migrationEntityReference = $results[0];
        }

        return $migrationEntityReference;
    }

    /**
     * Filter by class
     *
     * @param QueryBuilder $qb        The query builder
     * @param string       $class     The class
     * @param string       $tableName The name of the table
     *
     * @return QueryBuilder The query builder
     */
    public static function filterByClass(QueryBuilder $qb, $class, $tableName = 'migrationEntityReference')
    {
        //the name of the field
        $fieldName = 'class';

        //the name of the parameter
        $parameterName = $tableName.$fieldName;

        //filter on value
        $qb->andWhere($tableName.'.'.$fieldName.' = :'.$parameterName);

        //set the value
        $qb->setParameter($parameterName, $class);

        return $qb;
    }


    /**
     * Filter by filterByEntityId
     *
     * @param QueryBuilder $qb        The query builder
     * @param string       $class     The class
     * @param string       $tableName The name of the table
     *
     * @return QueryBuilder The query builder
     */
    public static function filterByEntityId(QueryBuilder $qb, $id, $tableName = 'migrationEntityReference')
    {
        //the name of the field
        $fieldName = 'entityId';

        //the name of the parameter
        $parameterName = $tableName.$fieldName;

        //filter on value
        $qb->andWhere($tableName.'.'.$fieldName.' = :'.$parameterName);

        //set the value
        $qb->setParameter($parameterName, $id);

        return $qb;
    }


    /**
     * Filter by filterByReference
     *
     * @param QueryBuilder $qb        The query builder
     * @param string       $reference The reference
     * @param string       $tableName The name of the table
     *
     * @return QueryBuilder The query builder
     */
    public static function filterByReference(QueryBuilder $qb, $reference, $tableName = 'migrationEntityReference')
    {
        //the name of the field
        $fieldName = 'reference';

        //the name of the parameter
        $parameterName = $tableName.$fieldName;

        //filter on value
        $qb->andWhere($tableName.'.'.$fieldName.' = :'.$parameterName);

        //set the value
        $qb->setParameter($parameterName, $reference);

        return $qb;
    }


    /**
     * Filter by class
     *
     * @param QueryBuilder $qb        The query builder
     * @param array        $classes   The classes
     * @param string       $tableName The name of the table
     *
     * @return QueryBuilder The query builder
     */
    public static function filterByClasses(QueryBuilder $qb, $classes, $tableName = 'migrationEntityReference')
    {
        //the name of the field
        $fieldName = 'class';

        self::addOrxFilter($qb, $tableName, $fieldName, $fieldName, $classes);

        return $qb;
    }

    /**
     * Filter query builder using an orX
     *
     * @param QueryBuilder $qb            The query builder
     * @param string $tableName           The name of the table
     * @param string $parameterNameSuffix The suffix for the parameter name
     * @param string $columnName          The name of the column to filter
     * @param array  $values              The allowed values
     *
     * @return QueryBuilder
     */
    protected static function addOrxFilter(QueryBuilder $qb, $tableName, $parameterNameSuffix, $columnName, $values)
    {
        //we filter only if there are some values given
        if (count($values) > 0) {
            $baseParameterName = $tableName.$parameterNameSuffix;

            //the orX expression
            $orX = $qb->expr()->orx();
            $index = 0;

            //parse the signedBys
            foreach ($values as $value) {
                //create a new parameterName
                $parameterName = $baseParameterName.$index;
                $index++;//incremente index

                $orX->add($tableName.'.'.$columnName.' = :'.$parameterName);

                //set the parameter value
                $qb->setParameter($parameterName, $value);
            }

            //add the orx query
            $qb->andWhere($orX);
        }

        return $qb;
    }
}
