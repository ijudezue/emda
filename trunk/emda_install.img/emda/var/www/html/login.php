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

if(file_exists('conf.php')){

require_once('./functions.php');

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
	echo "<HTML>\n";
	echo "<HEAD>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";

?>

<TITLE>MailWatch Login Page</TITLE>
</HEAD>
<table width="300" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">
    <TR>

         <TD ALIGN="center"><IMG SRC="images/mailwatch-logo.png" ALT="Mailwatch Logo"></TD>
        </TR>

<tr>
<td>
<form name="form1" method="post" action="checklogin.php">
<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
<tr>
<td colspan="3"><strong> MailWatch Login</strong></td>
</tr>
<tr>
<td width="78">Username</td>
<td width="6">:</td>
<td width="294"><input name="myusername" type="text" id="myusername"></td>
</tr>
<tr>
<td>Password</td>
<td>:</td>
<td><input name="mypassword" type="password" id="mypassword"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><input type="submit" name="Submit" value="Login"> <input type="reset" value="Reset">  <INPUT TYPE="button" VALUE="Back" onClick="history.go(-1);return true;"></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
</html>

<?php

}
else{

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
	echo "<HTML>\n";
	echo "<HEAD>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
?>

<TITLE>MailWatch Login Page</TITLE>
</HEAD>
<table width="300" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">
    <TR>

         <TD ALIGN="center"><IMG SRC="images/mailwatch-logo.png"></TD>
        </TR>

<tr>
<form name="form1" method="post" action="checklogin.php">
<td>
<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
<tr>
<td colspan="3"><strong> MailWatch Login</strong></td>
</tr>
<td colspan="3"> Sorry the Server is missing config.conf. Please create the file by copying config.conf.example and making the required changes.</td>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><INPUT TYPE="button" VALUE="Back" onClick="history.go(-1);return true;"></td>
</tr>
</table>
</td>
</form>
</tr>
</table>
</html>
<?php

}
?>
