# pingu
Application for Queue , using swoole and Lamins // Mezzio

# Installing DotKernel `pingu`

- [Installing DotKernel `pingu`](#installing-dotkernel-pingu)
    - [Installation](#installation)
        - [Composer](#composer)
    - [Choose a destination path for DotKernel `pingu` installation](#choose-a-destination-path-for-dotkernel-pingu-installation)
    - [Installing the `pingu` Composer package](#installing-the-pingu-composer-package)
        - [Installing DotKernel pingu](#installing-dotkernel-pingu)
    - [Configuration - First Run](#configuration---first-run)
    - [Testing (Running)](#testing-running)

### Composer

Installation instructions:

- [Composer Installation -  Linux/Unix/OSX](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
- [Composer Installation - Windows](https://getcomposer.org/doc/00-intro.md#installation-windows)

> If you have never used composer before make sure you read the [`Composer Basic Usage`](https://getcomposer.org/doc/01-basic-usage.md) section in Composer's documentation

## Choosing an installation path for DotKernel `pingu` 

Example:

- absolute path `/var/www/pingu`
- or relative path `pingu` (equivalent with `./pingu`)

## Installing DotKernel `pingu`

After choosing the path for DotKernel (`pingu` will be used for the remainder of this example) it must be installed. 

#### Installing DotKernel `pingu` using git clone

This method requires more manual input, but it ensures that the default branch is installed, even if it is not released. Run the following command:

```bash
$ git clone https://github.com/dotkernel/pingu.git .
```

The dependencies have to be installed separately, by running this command
```bash
$ composer install
```

The setup asks for configuration settings regarding injections (type `0` and hit `enter`) and a confirmation to use this setting for other packages (type `y` and hit `enter`)

## Configuration - First Run

- Remove the `.dist` extension from the files `config/autoload/local.php.dist`, `config/autoload/mail.local.php.dist`, `config/autoload/notification.local.php.dist`, `config/autoload/redis.local.php.dist`, `config/autoload/swoole.local.php.dist`
- Edit `config/autoload/local.php` according to your dev machine and fill in the `database` configuration
- Edit `config/autoload/notification.php` by filling the 'protocol' and 'host' configuration
- Add smtp credentials in `config/autoload/mail.local.php` if you want your application to send mails on registration etc.
- Create `data/logs` folder and leave it empty

> Charset recommendation: utf8mb4_general_ci  

## Testing (Running)

Note: **Do not enable dev mode in production**

- Run the following commands in your project's directory ( in different tabs ):

```bash
$ redis-cli
$ php bin/dot-swoole start
$ vendor/bin/qjitsu work
$ vendor/bin/qjitsu scheduler:run --interval=1
```

> Tip: use --interval=1 on dev only

**NOTE:**
If you are still getting exceptions or errors regarding some missing services, try running the following command

```bash
$ php bin/clear-config-cache.php
```

> If `config-cache.php` is present that config will be loaded regardless of the `ConfigAggregator::ENABLE_CACHE` in `config/autoload/mezzio.global.php`

## Daemons (services) files content
```bash
app-main.service
[Unit]
Description=pingu startup service
StartLimitIntervalSec=1

[Service]
#The dummy program will exit
Type=oneshot
ExecStart=/bin/true
RemainAfterExit=yes

[Install]
WantedBy=multi-user.target
```

```bash
app-queue.service
[Unit]
Description=Queue startup service
After=mysqld.service
PartOf=app-main.service
StartLimitIntervalSec=1

[Service]
Type=simple
Restart=always
RestartSec=1
User=root
ExecStart=/usr/bin/php /var/www/html/app-directory/vendor/bin/qjitsu work

[Install]
WantedBy=app-main.service
```

```bash
app-queue-scheduler.service
[Unit]
Description=Queue scheduler startup service
After=mysqld.service
PartOf=app-main.service
StartLimitIntervalSec=1

[Service]
Type=simple
Restart=always
RestartSec=1
User=root
ExecStart=/usr/bin/php /var/www/html/app-directory/vendor/bin/qjitsu scheduler:run --interval=1

[Install]
WantedBy=app-main.service
```

```bash
app-swoole.service
[Unit]
Description=pingu startup service
After=mysqld.service
PartOf=app-main.service
StartLimitIntervalSec=1

[Service]
Type=simple
Restart=always
RestartSec=1
User=root
ExecStart=/usr/bin/php /var/www/html/app-directory/bin/dot-swoole start
ExecStop=/usr/bin/php /var/www/html/app-directory/bin/dot-swoole stop

[Install]
WantedBy=app-main.service
```
