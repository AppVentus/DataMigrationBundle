<?php

namespace AppVentus\DataMigrationBundle\Entity;

/**
 * A migration.
 */
class Migration
{
    protected $id;
    protected $reference;
    protected $entityId;
    protected $date;
    protected $data;
    protected $action;
    protected $class;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = $this->generateIdentifier();
        $this->date = new \DateTime('now');
    }

    /**
     * Get the identifier for the new migration.
     *
     * @return string
     */
    protected function generateIdentifier()
    {
        //we do not want a comma in the reference
        $reference = microtime(true) * 10000;

        return $reference;
    }

    /**
     * Get the id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the date.
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the date.
     *
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Set the reference.
     *
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get the reference.
     *
     * @return string the reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the action.
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get the action.
     *
     * @return string The action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the data.
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get the data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the class.
     *
     * @return string The class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the class.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get the entity id.
     *
     * @return string The entity id
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set the entity id.
     *
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }
}
