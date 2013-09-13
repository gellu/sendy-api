# Sendy API #

RESTful API for [Sendy](www.sendy.co) build with [Slim](www.github.com/codeguy/Slim)

## Installation ##

1. Launch `php composer.phar install` to install Slim framework
2. Copy `app/config-env-dist.php` to `app/config-env.php`
3. Fill in `app/config-env.php` credentials to Sendy MySQL DB

## API Authorization ##

Api authorizes with Sendy App Key (tabel: apps, colum: app_key) which has to pass with every request as `GET` parameter `app_key`  
Example: `[your_api_uri]/subscribers/get/list?app_key=[your_app_key]&list=1`

## Methods ##

### Subscribers ###

**/subscribers/add/user**

description: Add user to subscribers list  
method: `POST`  
params: `email` - subscriber email, `list` - list id  
return: `number of created subscribers`

**/subscribers/get/list**

description: Get subscribers list by list id  
method: `GET`  
params: `list` - list id  
return: `list of subscribers`

**/subscribers/get/user**

description: Get subscribers by email 
method: `GET`  
params: `email` - subscriber email  
return: `subscriber`

**/subscribers/truncate/list**

description: Truncate list of subscribers  
method: `GET`  
params: `list` - list id  
return: `number of truncated subscribers`

**/subscribers/delete/user**

description: Delete user from subscribers (all subscriber lists)  
method: `GET`  
params: `email` - subscriber email  
return: `number of truncated subscribers`
 
### Lists ###

**/lists/get**

description: Get lists by name wildcard  
method: `GET`  
params: `name` - wildcard name  
return: `lists`
