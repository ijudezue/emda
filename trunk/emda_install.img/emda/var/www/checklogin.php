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

if (isset($_SERVER['PHP_AUTH_USER']))
{
    $myusername = $_SERVER['PHP_AUTH_USER'];
    $mypassword = $_SERVER['PHP_AUTH_PW'];
}
else
{
    // Define $myusername and $mypassword
    $myusername=$_POST['myusername'];
    $mypassword=$_POST['mypassword'];
}

// To protect MySQL injection (more detail about MySQL injection)
$myusername = safe_value($myusername);
$mypassword = safe_value($mypassword);

// encrypt password
$encrypted_mypassword=md5($mypassword);

$sql="SELECT * FROM users WHERE username='$myusername' and password='$encrypted_mypassword'";
$result=dbquery($sql);

if (!$result) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}

$fullname=mysql_result($result, 0, fullname);
$usertype=mysql_result($result, 0, type);

$sql1="SELECT filter FROM user_filters WHERE username='$myusername' AND active='Y'";
$result1=dbquery($sql1);

if (!$result1) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}

while($row = mysql_fetch_array($result1)){

$filter[]=$row['filter'];

}

if(strpos($myusername,'@')){
$ar=split("@",$myusername);
$domainname = $ar[1];
$filter[] = $domainname;
}

switch ($usertype){

	case "A":
		$global_filter ="1=1";
		$global_list = "1=1";
		break;
	case "D":
		$filter1 = implode("' OR to_domain='",$filter);
		$filter2 = implode("' OR from_domain='",$filter);
		$global_filter = "to_domain='$filter1' OR from_domain='$filter2'";
		$global_list = "to_domain='$filter1'";
                break;
	case "U":
		$global_filter ="to_address='$myusername'";
		break;
}


// Mysql_num_row is counting table row
$count=mysql_num_rows($result);

// If result matched $myusername and $mypassword, table row must be 1 row

if($count==1){
// Register $myusername, $mypassword and redirect to file "login_success.php"
$_SESSION['myusername']    = $myusername;
$_SESSION['fullname']      = $fullname;
$_SESSION['user_type']     = $usertype;
$_SESSION['domain']        = $domainname;
$_SESSION['global_filter'] = $global_filter;
$_SESSION['global_list']   = $global_list;
$_SESSION['global_array']  = $filter;
header("Location: index.php");
}
else {
?>
<html>
<TITLE>MailWatch Login Page</TITLE>
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
<tr>
<td colspan="3"> Bad username or Password</td>
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
</td>
</form>
</tr>
</table>
</html>

<?php }
dbclose();
html_end();

?>
