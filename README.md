
# rpicloud

Wordpress Plugin which connects to a public NextCloud (DAV) share and provides a list with files to download from, without showing the user the NextCloud Interface.

### Screenshot
![grafik](https://user-images.githubusercontent.com/307023/116627108-cef98600-a94c-11eb-8657-819e18fd3db3.png)


### Installation

1. Clone the plugin on your server (in the `/wp-content/plugins/` directory).
   1. `git clone https://github.com/johappel/rpicloud.git`
2. `composer install`
4. Activate the plugin.
5. Use the shortcode `[rpicloud url="<nectcloud-share>"]`

## Built With

* [WordPress](https://github.com/WordPress/WordPress)
* [Composer](https://github.com/composer/composer) - Dependency Manager for PHP
* [sabre/dav](http://sabre.io) - sabre/dav is a CalDAV, CardDAV and WebDAV framework for PHP

## License

This project is licensed under the GNU GPL License - see the [LICENSE](LICENSE) file for details.

## Improvements

This plugin is a frist rough working draft and could be heavily improved!

* Add comments
* Use Wordpress Options API
* Cache the directory listing in order to improve loading time
* Use more than one share link
* Create a namespace for objects, create own files for classes, use properly imports ...
* Abstract class for File and Directory
* Use Wordpress coding standards :-)
* ...
