<?php

/*
 MailWatch for MailScanner
 Copyright (C) 2003-2011  Steve Freegard (steve@freegard.name)
 Copyright (C) 2011  Garrod Alwood (garrod.alwood@lorodoes.com)

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
require_once("./functions.php");

session_start();
require('login.function.php');

html_start("Documentation");

if (isset($_GET[doc])) {
 include_once("docs/".$_GET[doc].".html");
} else {
 echo "<TABLE WIDTH=100% CLASS=\"BOXTABLE\">\n";
 echo " <TR>\n";
 echo "  <TD>\n";
 echo "  <H1>Documentation</H1>\n";
 echo "  This page does require authentication, so you can put links to your site documentation here and allow your users to access it if you wish.";
 echo "  </TD>\n";
 echo " </TR>\n";
 echo "</TABLE>\n";
}

html_end();
?>
