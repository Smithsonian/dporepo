# Packrat: Platform for Complex Resource Administration (3DRepo)

A Symfony project created on November 30, 2017

Packrat provides a web-based user interface for ingesting assets and metadata pertaining to 3D models, and processing them using the Cook 3D model processing service. https://github.com/Smithsonian/dpo-cook

Packrat uses the Voyager 3D model viewer. https://github.com/Smithsonian/dpo-voyager

## Installation
### Requirements
- PHP 7.2 *This is critical. If using XAMPP be sure to download the version with PHP 7.2*
- MySQL 5.7
- Symfony Framework 3.4 (provided by installation)
- jQuery 1.12 (provided by installation)

### Assumptions
- LAMP or WAMP environment has been installed.
- Git has been installed.
- Composer has been installed.

### PHP Settings
To support uploads of large files, set the following within php.ini.
```
max_file_uploads = 100
memory_limit = 4096
post_max_size = 5G
upload_max_filesize = 5G
upload_tmp_dir = C:\tmp
max_execution_time = 3600
```

### Prepare the website
#### Create a directory and clone dporepo into it

```
cd (into the LAMP or WAMP environment main webroot, e.g. htdocs)
mkdir dporepo
git clone https://github.com/Smithsonian/dporepo.git dporepo/
```

#### Create the json directory and clone dporepo-schemas into it

```
cd dporepo/web/
mkdir json
git clone https://github.com/Smithsonian/dporepo-schemas.git json/
```

#### Prepare database

- Create empty MySQL database, and database user account.
- Enable ldap extension and PDO extension, if not enabled, in php.ini

#### Parameters (app/config/parameters.yml)

If you have a filled-out `parameters.yml` file, move it into the app/config directory.

If not, there are 2 options:
1. Copy parameters.yml.dist to `parameters.yml`. Fill in the missing values for the database and other settings.
2. Do nothing. You will be prompted during the installation to provide these settings via the command line, one at a time.

The database settings must match the database and database user account created in step Prepare database.

#### Install Symfony and Third Party Libraries using Composer

- Change directory into the web root. Run composer.

```
php composer update
```

- If PHP runs out of memory you can brute-force it to use unlimited memory.

```
php -d memory_limit=-1 composer update
```

#### Create a vhosts entry to point to the dporepo web root

[TODO: better info]

Make sure Apache host config files includes the vhosts files.

Ensure that the `DocumentRoot` and `Directory` point to your filesystem location of the "web" directory of the application.

##### Vhost Example File

```
NameVirtualHost *:8080

<VirtualHost *:8080>
    DocumentRoot "C:/xampp/htdocs/dporepo/web/"
    ServerName localhost:8080
    ErrorLog "logs/error.log" 
    CustomLog "logs/access.log" common
    <Directory "C:/xampp/htdocs/dporepo/web">
        AllowOverride All
        Order Allow,Deny
        Allow from All
    </Directory>
</VirtualHost>
```

### Launch UI
#### Using a browser navigate to the homepage.
If you see PDO errors (can't find file), uncomment and set the `unix_socket` parameter underneath `doctrine: > dbal:` within app/config/config.yml

#### Install the Application
Go to http://localhost:8000/ (Windows/XAMPP) http://127.0.0.1:8000/ (Mac) and click the "Install" button (switch the port number if need be).

If installation says it succeeded but you have no database, the most likely culprit is that your version of MySQL doesn't support json fields.

[TODO: Temp fix for wrong MySQL version]

Open the `database_create.sql` file, and change the 2 JSON fields in the `authoring_item` and `authoring_presentation` tables to `varchar(8000)`.

#### Register, and create a new user account.
Go to http://localhost:8000/login (Windows/XAMPP) http://127.0.0.1:8000/login (Mac) and click on "Register for an Account"

Set the Username to admin.  

You should now have access to all repo functions.  

### Smithsonian-specific Instructions

#### Install the DPO EDAN Bundle

Following the installation instructions out on GitHub. 

https://github.com/Smithsonian/DpoEdanBundle

##### Test endpoints (switch the port if need be)

http://127.0.0.1:8000/admin/edan/space%20shuttle. 

http://127.0.0.1:8000/admin/edan/nmnhinvertebratezoology_957944. 

#### Configure remote file storage

## Usage
https://github.com/Smithsonian/dporepo/wiki/Using-Packrat

## License Information

Copyright 2019 Smithsonian Institution.

Licensed under the Apache License, Version 2.0 (the "License"); you may not use the content of this repository except in compliance with the License. You may obtain a copy of the License at:

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.

## Pre-Release Software

This software is pre-release and provided "as is". Breaking changes can and will happen during development.
