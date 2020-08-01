# Magento 2.4 Module Ld Changeorderemail

    ``ld/module-changeorderemail``

 - [Installation](#markdown-header-installation)
 - [Specifications](#markdown-header-specifications)



### Instaltion: Zip file

 - Unzip the zip file in `app/code/Ld`
 - Enable the module by running `php bin/magento module:enable Ld_Changeorderemail`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

## Specifications

 - Console Command
	- ResetOrderEmail
        - This command reset all orders based on order_id email or by an email given
