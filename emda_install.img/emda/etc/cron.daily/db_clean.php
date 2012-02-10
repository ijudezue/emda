#!/usr/bin/php -qn
<?php
/*
 MailWatch for MailScanner
 Copyright (C) 2003  Steve Freegard (smf@f2s.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Change the following to reflect the location of functions.php
require('/var/www/html/functions.php');

ini_set('error_log','syslog');
ini_set('html_errors','off');
ini_set('display_errors','on');
ini_set('implicit_flush','false');

dbquery("DELETE LOW_PRIORITY FROM maillog WHERE timestamp < (now() - INTERVAL '.RECORD_DAYS_TO_KEEP.' DAY)");
dbquery("OPTIMIZE TABLE maillog");

?>
