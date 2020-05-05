# Magento2: prevent session creation for search engines bots/crawlers and clean up expired sessions for humans

There is a [problem](https://magento.stackexchange.com/questions/18276/magento-generating-aprox-20-session-files-per-minute) with search engines bots and crawlers in Magento - new session is created for each request from the bots. So, there are a lot of "dead" sessions in DB/filesystem when any bot scans Magento pages - each page request creates new session. This module [prevents](https://github.com/flancer32/mage2_ext_bot_sess/blob/master/Plugin/Session/SessionManager.php#L29) session creation for search engines bots and crawlers.

Another problem is that if sessions are stored in DB then Magento does not clean up expired sessions. This module does it.

## Configuration

Go to `Stores / Configuration / General / Web / Bots Sessions Settings`:

![Configuration](./docs/img/config.png "Configuration")

* **Bots Signatures**: All parts will be concatenated into one regex "/^alexa|^blitz\.io|...|yandex/i" to lookup for the bots.
* **Bots Sessions Max Lifetime**: note, that Magento cron runs clean up job [every hour](./etc/crontab.xml).

## Sessions Cleanup

### DB Saved Sessions

Magento saves own sessions in DB (`./app/etc/env.php`):
```php
  'session' => 
  array (
    'save' => 'db',
  )
```

Console command to clean up bot's existing sessions & user's expired sessions from DB:
```bash
$ ./bin/magento fl32:botsess:clean
```



### Filesystem Saved Sessions

Magento saves own sessions in filesystem (`./app/etc/env.php`):
```php
  'session' => 
  array (
    'save' => 'files',
  )
```

Sessions are cleaned up using PHP garbage collector (see `session.gc_maxlifetime`). Magento in this mode cannot control sessions lifetime. Use this route to cleanup files sessions for inactive users: `http://your.shop.com/fl32botsess/clean/files` and [this](./etc/bin/root_cron_clean_files.sh) template to create shell-script for cron.

This is bad solution for bad practice. Don't use files for Magento sessions at all.


## Logging

See logs in `MAGENTO_ROOT/var/log/fl32.botsess.log`.

## Install


```bash
$ cd ${DIR_MAGE_ROOT}
$ composer require flancer32/mage2_ext_bot_sess
$ bin/magento module:enable Flancer32_BotSess
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
$ bin/magento setup:static-content:deploy
$ bin/magento cache:clean
$ # set filesystem permissions to your files
```

## Uninstall

You need an authentication keys for `https://repo.magento.com/` to uninstall any Magento 2 module. Go to your [Magento](https://marketplace.magento.com/customer/accessKeys/) account, section (My Profile / Marketplace / Access Keys) and generate pair of keys to connect to Magento 2 repository. Then place composer authentication file `auth.json` besides your `composer.json` as described [here](https://getcomposer.org/doc/articles/http-basic-authentication.md) and put your authentication keys for `https://repo.magento.com/` into the authentication file:
```json
{
  "http-basic": {
    "repo.magento.com": {
      "username": "...",
      "password": "..."
    }
  }
}
```

Then run these commands to completely uninstall `Flancer32_BotSess` module: 
```bash
$ cd ${DIR_MAGE_ROOT}   
$ bin/magento module:uninstall Flancer32_BotSess
$ composer remove flancer32/mage2_ext_bot_sess
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
$ bin/magento setup:static-content:deploy
$ bin/magento cache:clean
$ # set filesystem permissions to your files
```

Be patient, uninstall process (`bin/magento module:uninstall ...`) takes about 2-4 minutes. Remove `auth.json` file at the end:

 ```bash
$ rm ./auth.json
```
