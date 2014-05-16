
# How it works #

A migration file contains all the data migration to run.

* The file is automatically filled.
* The data are restored on your datase using command.


# Entities used by the bundle #

The av_dm_ prefix stands for AppVentus Data Migration -> av_dm

## MigrationVersion (table av_dm_migration) ##

This table contains the list of the migration.

It has one column:

* Id: Id of the migration

A runned migration has its id listed in this table



## MigrationEntityReference (table av_dm_entity_reference) ##

This table allows us to identify an entity using a reference.

The identifiers being generated independantly on the datases, we needed a common reference between databases.

The columns:

* entityId: Id of the entity
* class: The class of the entity
* reference: The reference of the entity


# Record data #

* We use the doctrine event subscriber (DumpableSubscriber)

* An action is done on an dumpable entity (DumpableSubscriber)

* We create a migration (EntityDumpableHelper)
	* A migration is created (MigrationHelper)
	* The entity is normalized into an array (DumpableEntityNormalizer)
		* Using the doctrine metadata
		* The retrieve the data of the attributes of the entity using get
		* We transform the value into a string (datetime/integer/string to string)
		* For the foreign entities
		* We construct an array of id/reference for each entity
			* We look in the entity reference table if this entity has a reference
	* The migration get the array 
* The migration is normalized into an array (MigrationNormalizer)
* The array is added to the migration file.


# Restore data #

* We load the migration file (EntityDumpableHelper)
* We get the migration that have not yet been runned
	* We look for the migration id in the migration version table
	* A new migration is not listed in this table
* We get the migration object using denormalizer (MigrationDenormalizer)
* We get the entity using the denormalizer (DumpableEntityDenormalizer)
* Switch the action, we create update or delete the entity
* We store the migration id in the migration version table

## Create entity ##

* We check that the entity reference has not been used yet (it would mean that the migration has already been runned)
	* The reference are store in the ap_dm_entity_reference table
* We create the entity
* The link entity id/reference is stored in the ap_dm_entity_reference table.


## Update entity ##

* If the entity has a reference, we retrieve the ID linked to the reference
* Otherwise, we use directly the Id of the entity
* We update all the attributes of the entity
* We saved the entity

## Delete entity ##

* If the entity has a reference, we retrieve the ID linked to the reference
* Otherwise, we use directly the Id of the entity
* We deleted the entity

# The foreign entities #
An entity can have foreign entities. 

These foreign entities are also identified using their id or reference.

It uses the same system to identify all the entities (see MigrationEntityReferenceHelper)

