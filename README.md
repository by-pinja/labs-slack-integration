# What is this

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

Simple application to send a messages to Slack and listen outgoing messages
from Slack.

## Table of Contents

* [What is this?](#what-is-this)
  * [Table of Contents](#table-of-contents)
  * [Installation](#installation)
      * [1. Clone repository](#1-clone-repository)
      * [2. Configuration](#2-configuration)
      * [3. Dependencies installation](#3-dependencies-installation)
      * [4. File permissions](#4-file-permissions)
      * [5. Environment checks](#5-environment-checks)
        * [CLI environment](#cli-environment)
        * [Web-server environment](#web-server-environment)
      * [6. Slack configuration](#6-slack-configuration)
        * [Incoming WebHooks](#incoming-webhooks)
        * [Outgoing WebHooks](#outgoing-webhooks)
  * [Development](#development)
    * [IDE](#ide)
    * [PHP Code Sniffer](#php-code-sniffer)
    * [Docker](#docker)
  * [Testing](#testing)
  * [Metrics](#metrics)
  * [Links / resources](#links--resources)

## Requirements

* PHP 7.1.3 or higher
* [Composer](https://getcomposer.org/)

## Installation

### 1. Clone repository

Use your favorite IDE and get checkout from GitHub or just use following 
command

```bash
git clone https://github.com/protacon/labs-slack-integration.git
```

### 2. Configuration

Next you need to create `.env` file, which contains all the necessary
environment variables that application needs. You can create it by following
command (in folder where you cloned this project):

```bash
cp .env.dist .env
```

Then open that file and make necessary changes to it. Note that this `.env`
file is ignored on VCS.

### 3. Dependencies installation

Next phase is to install all needed dependencies. This you can do with 
following command, in your project folder:

```bash
composer install
```

Or if you haven't installed composer globally

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### 4. File permissions

Next thing is to make sure that application `var` directory has correct
permissions. Instructions for that you can find
[here](https://symfony.com/doc/3.4/setup/file_permissions.html).

_I really recommend_ that you use `ACL` option in your development environment.

### 5. Environment checks

To check that your environment is ready for this application. You need to make
two checks; one for CLI environment and another for your web-server environment.

#### CLI environment

You need to run following command to make all necessary checks.

```bash
./vendor/bin/requirements-checker
```

#### Web-server environment

Open terminal and go to project root directory and run following command to
start standalone server.

```bash
./bin/console server:start
```

Open your favorite browser with `http://127.0.0.1:8000/check.php` url and
check it for any errors.

### 6. Slack configuration

You need to configure your [Slack](https://slack.com/) to connect to this 
application. And to do that open https://[your_slack].slack.com/customize/
in your favorite browser and click `Configure Apps` on left hand side menu.

And after that click `Custom Integrations`

#### Incoming WebHooks

Create new hook as you like, and copy that `Webhook URL` to your `.env` file
`SLACK_WEBHOOK_URL` value.

#### Outgoing WebHooks

Create new hook as you like, and set this application URL to `URL(s)` section
on configuration. Then copy `token` to your `.env` file `SLACK_TOKEN` value.

## Development

* [Coding Standards](http://symfony.com/doc/current/contributing/code/standards.html)

### IDE

I highly recommend that you use "proper"
[IDE](https://en.wikipedia.org/wiki/Integrated_development_environment)
to development your application. Below is short list of some popular IDEs that
you could use.

* [PhpStorm](https://www.jetbrains.com/phpstorm/)
* [NetBeans](https://netbeans.org/)
* [Sublime Text](https://www.sublimetext.com/)
* [Visual Studio Code](https://code.visualstudio.com/)

Personally I recommend PhpStorm, but just choose one which is the best for you.
Also note that project contains `.idea` folder that holds default settings for
PHPStorm.

### PHP Code Sniffer

It's highly recommended that you use this tool while doing actual development
to application. PHP Code Sniffer is added to project ```dev``` dependencies, so
all you need to do is just configure it to your favorite IDE. So the `phpcs`
command is available via following example command.

```bash
./vendor/bin/phpcs -i
```

If you're using [PhpStorm](https://www.jetbrains.com/phpstorm/) following links
will help you to get things rolling.

* [Using PHP Code Sniffer Tool](https://www.jetbrains.com/help/phpstorm/10.0/using-php-code-sniffer-tool.html)
* [PHP Code Sniffer in PhpStorm](https://confluence.jetbrains.com/display/PhpStorm/PHP+Code+Sniffer+in+PhpStorm)

### Docker

Todo

## Testing

Project contains bunch of tests _(Functional, Integration, Unit)_ which you can
run simply by following command:

```bash
./bin/phpunit
```

Note that you need to create `.env.test` file to define your testing
environment. This file has the same content as the main `.env` file, just
change it to match your testing environment.

* [PHPUnit](https://phpunit.de/)

Or you could easily configure your IDE to run these for you.

## Metrics

Project also contains
[PhpMetrics](https://github.com/phpmetrics/phpmetrics)
to make some analyze of your code. You can run this by following command:

```
./vendor/bin/phpmetrics --junit=build/logs/junit.xml --report-html=build/phpmetrics .
```

And after that open `build/phpmetrics/index.html` with your favorite browser.

## Links / resources

* [Symfony Flex set to enable RAD (Rapid Application Development)](https://www.symfony.fi/entry/symfony-flex-to-enable-rad-rapid-application-development)
* [Symfony 4: A quick Demo](https://medium.com/@fabpot/symfony-4-a-quick-demo-da7d32be323)
* [Symfony Development using PhpStorm](http://blog.jetbrains.com/phpstorm/2014/08/symfony-development-using-phpstorm/)
* [Symfony Plugin plugin for PhpStorm](https://plugins.jetbrains.com/plugin/7219-symfony-plugin)
* [PHP Annotations plugin for PhpStorm](https://plugins.jetbrains.com/plugin/7320)
* [Php Inspections (EA Extended) plugin for PhpStorm](https://plugins.jetbrains.com/idea/plugin/7622-php-inspections-ea-extended-)
* [EditorConfig](https://plugins.jetbrains.com/plugin/7294-editorconfig)
* [composer-version](https://github.com/vutran/composer-version)
* [Symfony Recipes Server](https://symfony.sh/)

## Authors

[Tarmo Leppänen](https://github.com/tarlepp)

## License

[The MIT License (MIT)](LICENSE)

Copyright (c) 2018 Protacon Solutions Ltd
