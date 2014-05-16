<?php
namespace AppVentus\DataMigrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MigrationEntityReference
 *
 * @ORM\Entity(repositoryClass="AppVentus\DataMigrationBundle\Repository\MigrationEntityReferenceRepository")
 * @ORM\Table(name="av_dm_entity_reference")
 */
class MigrationEntityReference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_id", type="integer", nullable=false)
     */
    protected $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    protected $class;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255, unique=true, nullable=false)
     */
    protected $reference;


    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     */
    protected $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Set id
     *
     * @param integer
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entityId
     *
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set entityId
     *
     * @param integer
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return UserAlert
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set class
     *
     * @param string
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the reference
     *
     * @return string The reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the reference
     *
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }
}
