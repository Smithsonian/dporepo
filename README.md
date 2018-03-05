# DPO's 3D Repository

A Symfony project created on November 30, 2017, 8:53 pm.

A port from the [PHP Slim-based project](https://github.com/Smithsonian/dporepo_slim) to [Symfony 3.4](https://symfony.com/).

## Installation

### Clone the Repository

```
git clone git@github.com:Smithsonian/dporepo.git
```

### Parameters (app/config/parameters.yml)

If you have a filled-out `parameters.yml` file, move it into the app/config directory.

If not, you will be promted during the installation to provide these settings.

### Install

```
cd dporepo

composer install
```

### Start the Server (if running locally during development)

```
php bin/console server:start
```