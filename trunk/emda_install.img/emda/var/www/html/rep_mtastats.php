<?php
/*
 MailWatch for MailScanner
 Original Copyright (C) 2003  Steve Freegard (smf@f2s.com)
 Modified for ESVA, Copyright (C) 2007 Gal Zilberman

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

//begin modified by HyTeK
//Require files
require_once('./functions.php');

// verify login
session_start();
require('login.function.php');

// add the header information such as the logo, search, menu, ....
html_start("MTA Statistics",0,false,false);

echo "<center><iframe src=\"./mailgraph\" frameborder=\"0\" width=\"750\" height=\"2200\" scrolling=\"no\"></iframe></center>";

//end modified by HyTeK

//echo "</center>";
html_end();
?>

