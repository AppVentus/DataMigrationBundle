
# Overview #

This bundle allows to automatically record actions on the doctrine entities (create/update/delete) and restore these entities on another database using a command.

Usage example: 

 * You are using a CMS
 
	* You are using a CMS, all the application interface is done using the web interface, and the data are recorded by the bundle. 
	* You commit and push the migration file.
	* The others developers get the file pulling the sources.
	* Others developers can then migrate their database data without doing a sql dump.

 * You are creating fixtures or tests data using your application
 	* The data are recorded
	* You commit and push the migration file.
	* The others developers get the file pulling the sources.
	* Others developers can then migrate their database data without doing a sql dump.

## Record actions ##

* The actions on the entities are dumped in an yml file
* You can track all entities or a list of entities.

## Restore actions ##
* A command will run the actions of the data migration file on your database






# Installation #

Install sources using composer:

	composer require appventus/datamigration:"dev-master"


We strongly recommend to enable this bundle only in a determined environment. (We created one named "record")

	if (in_array($this->getEnvironment(), array('record'))) {
       $bundles[] = new AppVentus\DataMigrationBundle\AppVentusDataMigrationBundle();
    }

The bundle store all modifications on the doctrine entities, so the migration file can quickly become huge. To avoid this problem, use a specific environment that allows you to record the data.

Update your database

	php app/console doctrine:schema:update --env=record

The bundle being loaded only in the record environment, the update of the database needs this environment.

# Configuration #

In your config_record.yml file, add the app_ventus_data_migration section

	app_ventus_data_migration:

Some configuration are availables:

 * migration_file_path
 * dumpable_entities

## migration_file_path (mandatory) ##
The path of the yaml file that will contains the migration saved by AppVentus 
## dumpable_entities (optional) ##
The list of entities that are tracked for the migration. 

If the list is empty, all the entities are tracked

## Exemple ##

	app_ventus_data_migration:
    	migration_file_path: %kernel.root_dir%/Resources/migration/migration.yml
	    dumpable_entities:
    	    - AcmeBundle\Entity\Widget
        	- AcmeBundle\Entity\Page
	        - AcmeBundle\Entity\Route

<b>Do not forget to clear the cache</b> in the record environment each time you modify the configuration

	php app/console ca:cl --env=record
# Usage #

## Record actions ##
The actions on the "dumpable entities" are recorded in the migration file
	
Only the database data are saved, so if you track the creation or modification of an image widget, you will also have to copy manually the assets (the images).

The migration file will automatically be filled with the modification of entites.

You can commit this file in your source repository.


## Import Data ##
A command imports automatically the entities of the migration file.

	php app/console appventus:data:update --env=record

This command is safe. A migration will never be imported twice.

If an error occured during the import, all modifications are rollbacked. Please correct the error given by the console.

The command will indicates you if there are required entities that are missing. If it the case, it is that your database is in a state too far from the person that did create the migration.


# Limitation #

Only the entities using an 'id' attribute as an identifier can be tracked.

The entities must have a public setter for each of its attributes.


