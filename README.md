# drupalcenter.de v8

## General system dependencies
This guide assumes you can setup your database and web server. 

* PHP 5.6
* MySQL > 5.5
* Apache

## Additional development dependencies
Examples in this readme are written for a posix compliant system like OSX and Linux. Windows works as well, but most 
commands will work differently, please consult the given links to external documentation.

* git
* drush
* composer

## Development setup

## Prepare local environment

- Create Database "dcd8"
- LocalHost: dcd8.dev

### Install Project
 
From root directory, execute:

    composer install

This will download all needed files.

Afterwards, execute to install drupal:
    
    ./bin/robo site:install local

## Update Project

After pulling the latest code from the repository, make sure to install the latest dependencies.
    
    ~/your-project-dir $ composer install
 
There might be changes to the make file.
    
    ./bin/robo site:update local