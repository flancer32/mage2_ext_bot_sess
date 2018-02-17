# mage2_ext_bot_sess
Magento2: prevent session creation for bots &amp; crawlers.



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
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
$ bin/magento setup:static-content:deploy
$ bin/magento cache:clean
```

Be patient, uninstall process (`bin/magento module:uninstall ...`) takes about 2-4 minutes. Remove `auth.json` file at the end:

 ```bash
$ rm ./auth.json
```