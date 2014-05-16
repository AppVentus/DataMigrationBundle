<?php
namespace AppVentus\DataMigrationBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppVentus\CoreBundle\Generator\WidgetGenerator;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppVentus\Awesome\ShortcutsBundle\Command\BaseCommand;

/**
 * Import all the entities stored in the migration.yml file
 */
class DataUpdateCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();

        $this->setName('appventus:data:update')
            ->setDefinition(array())
            ->setDescription('Import the page, widget, etc. that are in the migration.yml file');
    }

    /**
     * Take arguments and options defined in $this->interact() and generate a new Widget
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @see Command
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->write('Begin import of entities of the migration');

        //the entity manager
        $em = $this->get('doctrine.orm.entity_manager');
        $dumpHelper = $this->get('appventus.data_migration.helper.dump_helper');
        $migrationHelper = $this->get('appventus.data_migration.helper.migration_helper');

        //inform user
        $this->write('Getting the new migrations');

        //get the migrations to run
        $migrations = $dumpHelper->getNewMigrations();

        //the number of migrations to run
        $nbMigrations = count($migrations);

        //inform user about the number of migration
        $this->write('There are '.$nbMigrations.' migration(s) to run');

        //the counter
        $index = 1;

        //begin the transaction
        $em->getConnection()->beginTransaction(); // suspend auto-commit

        //parse the migrations
        foreach ($migrations as $migration) {
            //inform user
            $this->write('Running migration ['.$index.'/'.$nbMigrations.'] :'.$migration->getId());
            $index++;//incremente counter

            $migrationHelper->runMigration($migration);
        }

        //commit the transaction
        $em->getConnection()->commit();


        $this->write('Import of entities of the migration finished successfully');
    }
}
