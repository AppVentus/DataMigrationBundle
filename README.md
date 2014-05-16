# DataMigrationBundle #

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
	

# Installation and Usage #
[Resources/documentation/technical](https://github.com/AppVentus/DataMigrationBundle/blob/master/Resources/documentation/usage.md)
    

# Technical Documentation #
[Resources/documentation/technical](https://github.com/AppVentus/DataMigrationBundle/blob/master/Resources/documentation/technical.md)

