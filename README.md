
# rpicloud

Wordpress Plugin which connects to a public NextCloud (DAV) share and provides a list with files to download from, without showing the user the NextCloud Interface.

### Screenshot
![grafik](https://user-images.githubusercontent.com/307023/116627108-cef98600-a94c-11eb-8657-819e18fd3db3.png)


### Installation

1. Install [lazy blocks](https://wordpress.org/plugins/lazy-blocks/) (Do not activate)
1. Clone the plugin on your server (in the `/wp-content/plugins/` directory). 
1. `git clone https://github.com/johappel/rpicloud.git`
1. `composer install`
1. Activate the plugin.
1. edit .htaccess

##### add this lines to your .htaccess file
```

###########################################
## Lines from rpicloud plugin
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^^cloud/([^/]*)/(.*)$ /wp-content/plugins/rpicloud/download.php?rpicloud_key=$1&file=$2 [QSA,L]
RewriteRule ^^cloudview/([^/]*)$ /wp-content/plugins/rpicloud/viewer.php?url=$1 [QSA,L]
</IfModule>
############################################
```

### Usage

* Use the shortcode `[rpicloud url="<nectcloud-share>[:password]"]`
* Use the "Nextcloud" Block  in the Blockeditor 

## Built With

* [WordPress](https://github.com/WordPress/WordPress)
* [Composer](https://github.com/composer/composer) - Dependency Manager for PHP
* [sabre/dav](http://sabre.io) - sabre/dav is a CalDAV, CardDAV and WebDAV framework for PHP
* [fancytree](https://github.com/mar10/fancytree) JavaScript tree view / tree grid plugin
* [parsedown](https://github.com/erusev/parsedown) Markdown Parser in PHP
* [lazy blocks](https://github.com/nk-crew/lazy-blocks) Gutenberg blocks visual constructor

## License

This project is licensed under the GNU GPL License - see the [LICENSE](LICENSE) file for details.

### TODO
* [ ] add Multisite support
* [ ] add Mediainsert from Tree selection 
