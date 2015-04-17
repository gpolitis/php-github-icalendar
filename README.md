Overview
--------

This is a simple script that uses the public
[GitHub API](https://developer.github.com/v3/) to fetch issues in Json format
and then convert them to an iCalendar feed of VTODO items using the 
[sabre/vobject](https://github.com/fruux/sabre-vobject) library.

This project is heavily inspired by Daniel Pocock's Python based 
[github-icalendar](https://github.com/dpocock/github-icalendar), only this 
version is written in PHP, mainly because I have nginx/php already configured on
my box.

The official URL for this project is https://github.com/gpolitis/php-github-icalendar

Usage
-----

The dependencies for this script can be installed using
[composer](http://getcomposer.org/). If composer is not yet on your system,
follow the instructions on getcomposer.org to do so. To download and install the
script dependencies run

    php composer.phar install 

This script runs on any PHP-capable webserver. You will need a recent version of
PHP. Here's an example configuration file to get you started if you're using
nginx.


    server {
            listen   80;
    
            root /srv/www/github-icalendar.example.com;
            index index.php;
    
            server_name github-icalendar.example.com;
    
            # pass the PHP scripts to FastCGI server listening on the php-fpm socket
            location ~ \.php$ {
                    try_files $uri =404;
                    fastcgi_pass unix:/var/run/php5-fpm.sock;
                    fastcgi_index index.php;
                    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                    include fastcgi_params;
            }
    }


To access the GitHub API you'll need to feed the script with an access 
token. If you don't already have an access token for use with this script, check
out [this](https://help.github.com/articles/creating-an-access-token-for-command-line-use/)
page that guides you through the process of creating one. After you've set this
up correctly, you can access your GitHub calendar from here:

    http://github-icalendar.example.com/?access_token=YOUR_TOKEN&filter=assigned 

The filter parameter is passed directly to the GitHub API. It can be one of:

* assigned: Issues assigned to you
* created: Issues created by you
* mentioned: Issues mentioning you
* subscribed: Issues you’re subscribed to updates for
* all: All issues the authenticated user can see, regardless of participation or
  creation

The default is assigned.
    
Using GET parameters instead of having a static configuration file makes it easy
to share the script between many users. In that case you might want to protect
the service with SSL.

Copyright notice
----------------

Copyright (C) 2015, George Politis https://github.com/gpolitis

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

