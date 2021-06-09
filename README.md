
# rpicloud

Wordpress Plugin which connects to a public NextCloud (DAV) share and provides a list with files to download from, without showing the user the NextCloud Interface.

### Features
* display shared nextcloud foldertree in wp posts without seeing the nc link or password
* ability to upload files
* ability to create folders 
* ability to delete file folders
* Log and display user activities
* fine granulare permissons for anon and loggedin users
* display office files in Browser
* add via Gutenberg Block
* add via shortcode if Blockeditor is disabled
* filterhooks

### Screenshot
![grafik](https://user-images.githubusercontent.com/307023/121306807-919af780-c8ff-11eb-8cf1-c127bd1528ed.png)


### Installation

1. Required: Install [lazy blocks](https://wordpress.org/plugins/lazy-blocks/) (Activation not needed)
1. Download the plugin and extract in the /wp-content/plugins/ directory
1. Activate the plugin.

### Usage

* Use the shortcode `[rpicloud url="<nectcloud-share>[:password]"]`
* Use the "Nextcloud" Block  in the Blockeditor 

### Built With

* [WordPress](https://github.com/WordPress/WordPress)
* [sabre/dav](http://sabre.io) - sabre/dav is a CalDAV, CardDAV and WebDAV framework for PHP
* [fancytree](https://github.com/mar10/fancytree) JavaScript tree view / tree grid plugin
* [parsedown](https://github.com/erusev/parsedown) Markdown Parser in PHP

### Dependencies 
need to be installed:
* [lazy blocks](https://github.com/nk-crew/lazy-blocks) Gutenberg blocks visual constructor 

## License

This project is licensed under the GNU GPL License - see the [LICENSE](LICENSE) file for details.

### ToDo
* [x] add multisite support
* [ ] documentation
* [ ] add media insertion from tree selection 
* [ ] add autocreate shared folders for groups
* [ ] buddypress support
