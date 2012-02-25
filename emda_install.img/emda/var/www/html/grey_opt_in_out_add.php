<?
/*
 ESVA
 Copyright (C) 2007  Andrew MacLachlan (andy.mac@global-domination.org)

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
require "grey_tools.inc.php";
require('login.function.php');

// verify login
session_start();
require('login.function.php');

// add the header information such as the logo, search, menu, ....
html_start("Greylist Opt In/Out Add",0,false,false);
//end modified by HyTeK

$entry = $_POST[$field];
?>
<HTML>
<HEAD>
<TITLE><? print $title; ?>, add <? print $entry; ?></TITLE>
<LINK REL="StyleSheet" TYPE="text/css" HREF="style.css">
<meta http-equiv="refresh" content="0;URL=grey_opt_in_out.php?direction=<? print $_GET["direction"]; ?>&what=<? print $_GET["what"]; ?>">
<BODY>
<TABLE WIDTH=100% HEIGHT=100%><TR VALIGN=CENTER><TD ALIGN=CENTER>

<TABLE><TR><TD>
<H1><? print $title; ?>, add <? print $entry; ?></H1>

<?
# mysql> describe optout_domain;
# +--------+--------------+------+-----+---------+-------+
# | Field  | Type         | Null | Key | Default | Extra |
# +--------+--------------+------+-----+---------+-------+
# | domain | varchar(255) |      | PRI |         |       |
# +--------+--------------+------+-----+---------+-------+
# 1 row in set (0.00 sec)
?>

<?
$result = do_query("INSERT INTO ".$table."(".$field.") VALUES('".addslashes(strtolower($_POST[$field]))."')");
?>
Added.<BR>
<BR>
<BR>

<A HREF="grey_opt_in_out.php?direction=<? print $_GET["direction"]; ?>&what=<? print $_GET["what"]; ?>"><? print $title; ?> menu</A><BR>


</TD></TR></TABLE>

</TD></TR>

<? require "grey_copyright.inc.php"; ?>

</TABLE>
</BODY>
</HTML>
