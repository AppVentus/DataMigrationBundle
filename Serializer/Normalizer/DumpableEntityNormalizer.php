<?php

namespace AppVentus\DataMigrationBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use AppVentus\DataMigrationBundle\Helper\MigrationEntityReferenceHelper;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Normalize a dumpable entity
 *
 * @author Thomas Beaujean
 *
 */
class DumpableEntityNormalizer implements NormalizerInterface
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
     * Normalize the entity
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
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedObject = array();
        //we want the columns
        $attributeReflectionObject = new \ReflectionObject($object);

        //the entity manager
        $em = $this->getEntityManager();

        //the metadata of the object
        $metadata = $em->getMetadataFactory()->getMetadataFor($attributeReflectionObject->getName());

        //the associations
        $associationMappings = $metadata->associationMappings;

        $normalizedForeignData = $this->getForeignEntityData($object, $associationMappings);

        //the fields metadata
        $fieldMappings = $metadata->fieldMappings;

        $normalizedData = $this->getEntityData($object, $fieldMappings);

        //unify the foreign data and the data of the entity
        $normalizedObject = array_merge($normalizedData, $normalizedForeignData);

        return $normalizedObject;
    }

    /**
     * Get the array data for the foreign entities of the entity
     *
     * @param Entity  $object
     * @param unknown $associationMappings
     *
     * @return array
     */
    protected function getForeignEntityData($object, $associationMappings)
    {
        $normalizedObject = array();

        //parse the fields
        foreach ($associationMappings as $associationMapping) {
            //the name of the field
            $fieldName = $associationMapping['fieldName'];

            //get the foreign entity
            $foreignEntity = $this->getAttributeValue($object, $fieldName);

            //get the identifiers of the foreign entity
            $foreignIdentifiers = $this->getForeignIdentifiers($foreignEntity, $associationMapping);

            //add the id of the foreign entity
            $normalizedObject[$fieldName] = $foreignIdentifiers;
        }

        return $normalizedObject;
    }

    /**
     * Get the entity data (the primary attributes)
     *
     * @param  unknown            $object
     * @param  unknown            $fieldMappings
     * @return multitype:Ambigous <string, unknown>
     */
    protected function getEntityData($object, $fieldMappings)
    {
        $normalizedObject = array();

        //parse the fields
        foreach ($fieldMappings as $fieldMapping) {
            //the name of the field
            $fieldName = $fieldMapping['fieldName'];

            //get the attribute
            $attributeValue = $this->getAttributeValue($object, $fieldName);

            //convert the value to a string
            $convertedAttributeValue = $this->convertAttributeValue($attributeValue, $fieldMapping);

            //add the value to the array
            $normalizedObject[$fieldName] = $convertedAttributeValue;
        }

        return $normalizedObject;
    }

    /**
     *
     * @param  unknown $data
     * @param  string  $format
     * @return boolean
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function supportsNormalization($data, $format = null)
    {
        return true;
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
     * Convert an attribute to a string
     *
     * @param unknown      $attribute
     * @param FieldMapping $fieldMapping
     *
     * @return string The attribute converted in a string
     */
    protected function convertAttributeValue($attribute, $fieldMapping)
    {
        if ($attribute !== null) {
            //the field type
            $fieldType = $fieldMapping['type'];

            switch ($fieldType) {
                case 'datetime':
                    $convertedValue = $attribute->format('Y-m-d H:i:s');
                    break;
                case 'array':
                    $convertedValue = json_encode($attribute);
                    break;
                default:
                    $convertedValue = $attribute;
                    break;
            }
        } else {
            $convertedValue = null;
        }

        return $convertedValue;
    }

    /**
     * Get the array of identifiers for a foreign entity
     *
     * @param Entity  $foreignEntity
     * @param Mapping $associationMapping
     *
     * @throws \Exception
     *
     * @return array The id/reference for the foreign entities
     */
    protected function getForeignIdentifiers($foreignEntity, $associationMapping)
    {
        $foreignIdentifiers = array();

        //the type of association
        $type = $associationMapping['type'];

        switch ($type) {
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
                if ($foreignEntity === null) {
                    $foreignIdentifiers['id'] = null;
                    $foreignIdentifiers['reference'] = null;
                } else {
                    $foreignIdentifiers['id'] = $foreignEntity->getId();

                    //get the foreign entity reference
                    $foreignEntityReference = $this->migrationEntityReferenceHelper->getReferenceByEntity($foreignEntity);
                    $foreignIdentifiers['reference'] = $foreignEntityReference;
                }
                break;
            case ClassMetadataInfo::ONE_TO_MANY:
            case ClassMetadataInfo::MANY_TO_MANY:
                if ($foreignEntity !== null) {
                    foreach ($foreignEntity as $entity) {
                        $foreignIdentifier = array();
                        if ($foreignEntity === null) {
                            $foreignIdentifier['id'] = null;
                            $foreignIdentifier['reference'] = null;
                        } else {
                            $foreignIdentifier['id'] = $entity->getId();

                            //get the foreign entity reference
                            $foreignEntityReference = $this->migrationEntityReferenceHelper->getReferenceByEntity($entity);
                            $foreignIdentifier['reference'] = $foreignEntityReference;
                        }

                        $foreignIdentifiers[] = $foreignIdentifier;
                    }
                }
                break;

            default:
                throw new \Exception('The association type ['.$type.'] is not handeld');
        }

        return $foreignIdentifiers;
    }

    /**
     * Get the value of the attribute
     *
     * @param unknown $object
     * @param string  $fieldName
     *
     * @throws \Exception
     *
     * @return unknown
     */
    protected function getAttributeValue($object, $fieldName)
    {
        //the method to get the field value
        $getMethod = 'get'.ucfirst($fieldName);

        //give more information to developer in case of method lacking
        try {
            $reflexionMethodGet = new \ReflectionMethod($object, $getMethod);
        } catch (\Exception $ex) {
            $reflexionMethodGet = null;
        }
        $isMethod = 'is'.ucfirst($fieldName);
        try {
            $reflexionMethodIs = new \ReflectionMethod($object, $isMethod);
        } catch (\Exception $ex) {
            $reflexionMethodIs = null;
        }

        if ($reflexionMethodIs) {
            $reflexionMethod = $reflexionMethodIs;
        } elseif ($reflexionMethodGet) {
            $reflexionMethod = $reflexionMethodGet;
        } else {
            throw new \Exception('The dumpable object can not be dumped by AppVentus because there is no public method. Please provide one of these methods: ' . $getMethod . ' or ' . $isMethod);
        }

        //invoke the method on the object
        $attributeValue = $reflexionMethod->invoke($object);

        return $attributeValue;
    }
}
