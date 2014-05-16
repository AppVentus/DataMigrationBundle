<?php

namespace AppVentus\DataMigrationBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use AppVentus\DataMigrationBundle\Helper\MigrationEntityReferenceHelper;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author Thomas Beaujean
 *
 */
class DumpableEntityDenormalizer implements DenormalizerInterface
{
    protected $doctrine = null;
    protected $migrationEntityReferenceHelper = null;

    /**
     * Constructor
     *
     * @param Doctrine                       $doctrine
     * @param MigrationEntityReferenceHelper $migrationEntityReferenceHelper
     *
     * @throws Exception The doctrine service has not been provided
     */
    public function __construct($doctrine, MigrationEntityReferenceHelper $migrationEntityReferenceHelper)
    {
        $this->doctrine = $doctrine;
        $this->migrationEntityReferenceHelper = $migrationEntityReferenceHelper;
    }

    /**
     * Denormalize dumpable entity
     *
     * @param unknown $data
     * @param string  $class
     * @param string  $format
     * @param array   $context
     *
     * @return Entity
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        //create or retrieve the entity
        if (isset($context['entity'])) {
            $entity = $context['entity'];
        } else {
            $entity = new $class();
        }

        //the entity manager
        $em = $this->getEntityManager();

        //the metadata of the object
        $metadata = $em->getMetadataFactory()->getMetadataFor($class);

        //the fields metadata
        $fieldMappings = $metadata->fieldMappings;

        $this->setEntityData($entity, $fieldMappings, $data);

        //the associations
        $associationMappings = $metadata->associationMappings;

        $this->setForeignEntityData($entity, $associationMappings, $data);

        return $entity;
    }

    /**
     * Is this class supported
     *
     * @param array $data
     * @param String $type
     * @param string $format
     *
     * @return boolean
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return true;
    }

    /**
     * Get the entity data (the primary attributes)
     *
     * @param unknown $object
     * @param unknown $fieldMappings
     * @param array   $data
     *
     * @return multitype:Ambigous <string, unknown>
     */
    protected function setEntityData($object, $fieldMappings, $data)
    {
        //parse the fields
        foreach ($fieldMappings as $fieldMapping) {
            //the name of the field
            $fieldName = $fieldMapping['fieldName'];

            //get the attribute
            $attributeValue = $data[$fieldName];

            //revert the value from a string
            $convertedAttributeValue = $this->revertAttributeValue($attributeValue, $fieldMapping);

            //add the value to the object
            $object = $this->setAttributeValue($object, $fieldName, $convertedAttributeValue);
        }

        return $object;
    }

    /**
     * Get the value of the attribute
     *
     * @param unknown $object
     * @param string  $fieldName
     * @param unknown $attribute
     *
     * @throws \Exception
     *
     * @return unknown
     */
    protected function setAttributeValue($object, $fieldName, $attribute)
    {
        //the method to get the field value
        $method = 'set'.ucfirst($fieldName);

        //give more information to developer in case of method lacking
        try {
            $reflexionMethod = new \ReflectionMethod($object, $method);
        } catch (\Exception $ex) {
            throw new \Exception('The dumpable object can not be restored by AppVentus because there is no public method. Please provide this method:'.$ex->getMessage());
        }

        //invoke the method on the object
        $reflexionMethod->invoke($object, $attribute);

        return $object;
    }


    /**
     * Revert an attribute from a string
     *
     * @param unknown $attribute
     * @param FieldMapping $fieldMapping
     *
     * @return string The attribute converted in a string
     */
    protected function revertAttributeValue($attribute, $fieldMapping)
    {
        //the field type
        $fieldType = $fieldMapping['type'];

        switch ($fieldType) {
            case 'datetime':
                $convertedValue = \DateTime::createFromFormat('Y-m-d H:i:s', $attribute);
                break;
            case 'array':
                $convertedValue = json_decode($attribute, true);
                break;
            default:
                $convertedValue = $attribute;
                break;
        }

        return $convertedValue;
    }


    /**
     * Get the array data for the foreign entities of the entity
     *
     * @param Entity  $object              The entity
     * @param unknown $associationMappings The association mapping
     * @param Array   $data                The data
     *
     * @return array
     */
    protected function setForeignEntityData($object, $associationMappings, $data)
    {
        $normalizedObject = array();

        //parse the fields
        foreach ($associationMappings as $associationMapping) {
            //the name of the field
            $fieldName = $associationMapping['fieldName'];

            //get the attribute
            $attributeValue = $data[$fieldName];

            //revert the value from a string
            $foreignIdentifiers = $this->revertForeignIdentifiers($attributeValue, $associationMapping);

            $object = $this->setAttributeValue($object, $fieldName, $foreignIdentifiers);
        }

        return $normalizedObject;
    }


    /**
     * Get the array of identifiers for a foreign entity
     *
     * @param unknown            $foreignEntity
     * @param AssociationMapping $associationMapping
     *
     * @throws \Exception
     *
     * @return array The id/reference for the foreign entities
     */
    protected function revertForeignIdentifiers($attributeValue, $associationMapping)
    {
        $foreignIdentifiers = null;

        //association type
        $type = $associationMapping['type'];


        switch ($type) {
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
                //get the entity
                $entity = $this->getEntityByAttributeValue($attributeValue, $associationMapping);

                $foreignIdentifiers = $entity;
                break;
            case ClassMetadataInfo::ONE_TO_MANY:
            case ClassMetadataInfo::MANY_TO_MANY:
                //no id were provided, so there is no identifier
                $foreignIdentifiers = new ArrayCollection();
                foreach ($attributeValue as $attr) {
                    $entity = $this->getEntityByAttributeValue($attr, $associationMapping);
                    $foreignIdentifiers[] = $entity;
                }
                break;
            default:
                throw new \Exception('The association type ['.$type.'] is not handeld');
        }

        return $foreignIdentifiers;
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager The entity manager
     */
    protected function getEntityManager()
    {
        $em = $this->doctrine->getManager();

        return $em;
    }

    /**
     * Get the id by the class (parent and sub classes included) and the reference
     * The reference being unique, there is no problem looking into a list of classes
     *
     * @param string $class
     * @param string $reference
     * @return integer The id
     */
    protected function getEntityIdByClassAndReference($class, $reference)
    {
        //services
        $em = $this->getEntityManager();
        $migrationEntityReferenceHelper = $this->migrationEntityReferenceHelper;

        //the metadata of the class
        $metadata = $em->getMetadataFactory()->getMetadataFor($class);

        //we look in the current class and the parent classes and sub classes
        $subClasses = $metadata->subClasses;
        $parentClasses = $metadata->parentClasses;

        //the list of classes
        $classes = array();
        $classes[] = $class;
        $classes = array_merge($classes, $subClasses);
        $classes = array_merge($classes, $parentClasses);

        $id = $migrationEntityReferenceHelper->getEntityIdByClassesAndReference($classes, $reference);

        return $id;
    }

    /**
     * Get an entity by the attribute value
     *
     * @param array $attributeValue
     * @param unknown $associationMappings The association mapping
     *
     * @throws \Exception
     *
     * @return Entity|null
     */
    protected function getEntityByAttributeValue($attributeValue, $associationMapping)
    {
        //services
        $migrationEntityReferenceHelper = $this->migrationEntityReferenceHelper;

        $entity = null;

        //association type
        $class = $associationMapping['targetEntity'];

        $id = $attributeValue['id'];
        $reference = $attributeValue['reference'];

        if ($id !== null) {

            //if the reference exists
            if ($reference !== null) {

                //get the entity id by the reference
                $this->getEntityIdByClassAndReference($class, $reference);

                //did we found the id
                if ($id === null) {
                    throw new \Exception('The entity id for the reference ['.$reference.'] and the class ['.$class.'] was not found.');
                }
            }

            //get the entity
            $entity = $migrationEntityReferenceHelper->getEntityByClassAndId($class, $id);
        }

        return $entity;
    }
}
