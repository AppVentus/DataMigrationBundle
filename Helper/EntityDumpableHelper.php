<?php
namespace AppVentus\DataMigrationBundle\Helper;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use AppVentus\DataMigrationBundle\Entity\Migration;
use AppVentus\DataMigrationBundle\Entity\MigrationVersion;
use AppVentus\DataMigrationBundle\Helper\MigrationVersionHelper;
use AppVentus\DataMigrationBundle\Converter\MigrationConverter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use AppVentus\DataMigrationBundle\Entity\MigrationEntityReference;
use AppVentus\DataMigrationBundle\Serializer\Normalizer\MigrationNormalizer;
use AppVentus\DataMigrationBundle\Serializer\Normalizer\MigrationDenormalizer;

/**
 * Helper for the dumpable entities
 *
 * @author Thomas Beaujean <thomas@appventus.com>
 *
 * ref: appventus.data_migration.helper.dump_helper
 */
class EntityDumpableHelper
{
    protected $em = null;
    protected $migrationFilePath = null;
    protected $migrationHelper = null;
    protected $migrationEntityReferenceHelper = null;
    protected $migrationVersionHelper = null;
    protected $migrationNormalizer = null;
    protected $migrationDenormalizer = null;
    protected $dumpableEntities = null;

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * Constructor
     *
     * @param EntityManager                  $entityManager
     * @param unknown                        $migrationHelper
     * @param MigrationEntityReferenceHelper $migrationEntityReferenceHelper
     * @param MigrationVersionHelper         $migrationVersionHelper
     * @param MigrationNormalizer            $migrationNormalizer
     * @param MigrationDenormalizer          $migrationDenormalizer
     * @param array                          $dumpableEntities               The list of dumpable entity classes
     * @param string                         $migrationFilePath              The path to the migration file
     *
     * @SuppressWarnings functionMaxParameters
     */
    public function __construct(EntityManager $entityManager,
        $migrationHelper,
        MigrationEntityReferenceHelper $migrationEntityReferenceHelper,
        MigrationVersionHelper $migrationVersionHelper,
        MigrationNormalizer $migrationNormalizer,
        MigrationDenormalizer $migrationDenormalizer,
        $dumpableEntities,
        $migrationFilePath)
    {
        $this->em = $entityManager;
        $this->migrationHelper = $migrationHelper;
        $this->migrationVersionHelper = $migrationVersionHelper;
        $this->migrationEntityReferenceHelper = $migrationEntityReferenceHelper;
        $this->migrationFilePath = $migrationFilePath;
        $this->migrationNormalizer = $migrationNormalizer;
        $this->migrationDenormalizer = $migrationDenormalizer;
        $this->dumpableEntities = $dumpableEntities;
    }

    /**
     * Get the list of entities that are dumpable
     *
     * @return array The list of dumpable entities
     */
    public function getDumpableEntities()
    {
        $classes = $this->dumpableEntities;

        return $classes;
    }


    /**
     * Dump an entity to the migration file
     *
     * @param String $action
     * @param Entity $entity
     *
     * @throws \Exception The action is not handeld
     */
    public function dumpEntity($action, $entity)
    {
        switch ($action) {
            case self::ACTION_CREATE:
                $this->dumpCreateEntity($entity);
                break;
            case self::ACTION_UPDATE:
                $this->dumpUpdateEntity($entity);
                break;
            case self::ACTION_DELETE:
                $this->dumpDeleteEntity($entity);
                break;
            default:
                throw new \Exception('The action '.$action.' is not handeld by the EntityDumpableHelper. See the EntityDumpableHelper ACTION constants availables.');
        }
    }

    /**
     * Dump an entity to the yml file
     *
     * @param unknown $entity
     */
    protected function dumpCreateEntity($entity)
    {
        $migrationHelper = $this->migrationHelper;
        $migrationEntityReferenceHelper = $this->migrationEntityReferenceHelper;

        $migration = $migrationHelper->generateMigration(self::ACTION_CREATE, $entity);

        //on creation we create a migration entity reference
        $reference = $migration->getReference();

        $migrationEntityReferenceHelper->createMigrationEntityReference($entity, $reference);
        $this->createMigrationVersion($migration);

        $this->dumpMigration($migration);
    }

    /**
     * Dump an entity that is deleted
     *
     * @param unknown $entity
     */
    protected function dumpDeleteEntity($entity)
    {
        $migrationHelper = $this->migrationHelper;

        $migration = $migrationHelper->generateMigration(self::ACTION_DELETE, $entity);

        $this->createMigrationVersion($migration);

        $this->dumpMigration($migration);
    }

    /**
     * Dump an entity that is updated
     *
     * @param unknown $entity
     */
    protected function dumpUpdateEntity($entity)
    {
        $migrationHelper = $this->migrationHelper;

        $migration = $migrationHelper->generateMigration(self::ACTION_UPDATE, $entity);

        $this->createMigrationVersion($migration);

        $this->dumpMigration($migration);
    }

    /**
     * Dump a migration to the file
     *
     * @param Migration $migration
     */
    protected function dumpMigration(Migration $migration)
    {
        $dumper = new Dumper();
        //the migration converter
        $migrationNormalizer = $this->migrationNormalizer;

        //convert the migration as an array
        $migrationArray = $migrationNormalizer->normalize($migration);

        //add the id of the migration as the entry
        $this->appendToMigrationFile($migration->getId().":\n");

        //dump the array
        $yaml = $dumper->dump($migrationArray, 4, 4);
        $this->appendToMigrationFile($yaml);
    }

    /**
     * Append the string to the migration file
     *
     * @param string $string
     */
    protected function appendToMigrationFile($string)
    {
        $path = $this->migrationFilePath;
        //open the file
        $fp = fopen($path, 'a+');
        //write the string to the file
        fwrite($fp, $string);
        //close the handler
        fclose($fp);
    }

    /**
     * Create the migration version
     *
     * @param Migration $migration The migration
     *
     */
    protected function createMigrationVersion(Migration $migration)
    {
        //services
        $migrationVersionHelper = $this->migrationVersionHelper;
        //create the migration
        $migrationVersionHelper->createMigrationVersion($migration);
    }

    /**
     * Get the content of the migration file
     *
     * @return string
     */
    protected function getMigrationFileContent()
    {
        $path = $this->migrationFilePath;

        $content = file_get_contents($path);

        return $content;
    }

    /**
     * Get the content of the migration as an array
     *
     * @return array
     */
    protected function getMigrationFileAsArray()
    {
        $content = $this->getMigrationFileContent();

        $array = Yaml::parse($content);

        return $array;
    }

    /**
     * Get the migrations that have not been runned yet
     *
     * @return array:migration
     */
    public function getNewMigrations()
    {
        //the list of migrations
        $migrations = array();

        //services
        $migrationDenormalizer = $this->migrationDenormalizer;
        $migrationHelper = $this->migrationHelper;

        $migrationsArray = $this->getMigrationFileAsArray();

        //avoid error if the file is empty
        if ($migrationsArray !== null) {
            //parse the list of migrations
            foreach ($migrationsArray as $migrationArray) {
                $migration = $migrationDenormalizer->denormalize($migrationArray, 'AppVentus\DataMigrationBundle\Entity\Migration');

                //the id of the migration
                $migrationId = $migration->getId();

                //look for this migration in the database
                $new = $migrationHelper->isNewMigration($migrationId);

                //we just want the new migrations
                if ($new) {
                    $migrations[] = $migration;
                }
            }
        }

        return $migrations;
    }

}
