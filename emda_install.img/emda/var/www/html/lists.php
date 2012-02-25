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

html_start("Whitelist/Blacklist",0,false,false);

// First check that the user is in the correct form e.g. user@domain.com
// or for domain admins domain.com

if(!(check_username_format())) {
 die("Username is not in the correct format to use this utility.");
}

// Set constraints
switch($_SESSION['user_type']) {
 case 'U':  // User
  $disable_user = 'DISABLED';
  $disable_domain = 'DISABLED';
  preg_match('/(\S+)@(\S+)/',$_SESSION['myusername'],$regs);
  $touser = $regs[1];
  $todomain = $regs[2];
  $to_address = $touser.'@'.$todomain;
  $where = "to_address = '$to_address'";
  break;
 case 'D':  // Domain Admin
  $disable_domain = 'ENABLED';
  $touser = $_GET['to'];
  $todomain = $_SESSION['domain'];
 // $to_address = $todomain;
  break;
 case 'A':  // Administrator
    $todomain = 'default';
    $to_address = 'default';
  break;
}

// Split user/domain if necessary (from detail.php)
if(preg_match('/(\S+)@(\S+)/',$_GET['to'],$split)) {
 $touser = $split[1];
 $todomain = $split[2];
}

// Type
switch($_GET['type']) {
 case 'h':
  $from = $_GET['host'];
  break;
 case 'f':
  $from = $_GET['from'];
  break;
 default:
  if(isset($_GET['entry'])) { $from = $_GET['entry']; }
}

// Submitted
if($_GET['submit'] == 'Add') {
 // Check input is valid
 if(empty($_GET['list'])) {
  $errors[] = "You must select a list to create the entry.";
 }
 if(empty($from)) {
   $errors[] = "You must enter a from address (user@domain, domain or IP).";
 }


 $todomain1 = strtolower($_GET['domain']);

 // Validate input against the user type
 switch($_SESSION['user_type']) {
  case 'U':  // User
   if($_SESSION['myusername'] !== $to_address) {
    $errors[] = "To address does not match current username.";
   }
   break;
  case 'D':  // Domain Admin
   if(in_array($todomain1, $_SESSION['global_array'], TRUE)) {
      switch(true) {
    case(!empty($_GET['to'])):
     $to_address = $_GET['to'];
     if(!empty($_GET['domain'])) { $to_address .= '@'.$todomain1; }
     break;
    case(!empty($_GET['domain'])):
     $to_address = $_GET['domain'];
     break;
   }
   }
   else{
    $errors[] = "To domain does not match the current username.";
   }
   break;
  case 'A':  // Administrator
   switch(true) {
    case(!empty($_GET['to'])):
     $to_address = $_GET['to'];
     if(!empty($_GET['domain'])) { $to_address .= '@'.$_GET['domain']; }
     break;
    case(!empty($_GET['domain'])):
     $to_address = $_GET['domain'];
     break;
   }
   break;
 }

 // Insert the data
 if(!isset($errors)) {
  switch($_GET['list']) {
   case 'w':  // Whitelist
    $list = 'whitelist';
    break;
   case 'b':  // Blacklist
    $list = 'blacklist';
    break;
  }
  $sql  = 'REPLACE INTO '.$list.' (to_address, to_domain, from_address) VALUES';
  $sql .= '(\''.mysql_escape_string($to_address);
  $sql .= '\',\''.mysql_escape_string($todomain);
  $sql .= '\',\''.mysql_escape_string($from).'\')';
  @dbquery($sql);
  audit_log("Added ".$from." to ".$list." for ".$to_address);
  unset($from);
  unset($_GET['list']);
 }
}

// Delete
if($_GET['submit'] == 'Delete') {
 $id = mysql_escape_string($_GET['id']);
 switch($_GET['list']) {
  case 'w':
   $list = 'whitelist';
   break;
  case 'b':
   $list = 'blacklist';
   break;
 }

 switch($_SESSION['user_type']) {
  case 'U':
   $sql = "DELETE FROM $list WHERE id='$id' AND to_address='$to_address'";
   audit_log("Removed entry $id from $list");
   break;
  case 'D':
   $sql = "DELETE FROM $list WHERE id='$id' AND to_domain='$todomain'";
   audit_log("Removed entry $id from $list");
   break;
  case 'A':
   $sql = "DELETE FROM $list WHERE id='$id'";
   audit_log("Removed entry $id from $list");
   break;
 }

 $id = mysql_escape_string($_GET['id']);
 dbquery($sql);
}

function build_table($sql,$list) {
 $sth = dbquery($sql);
 $rows = mysql_num_rows($sth);
 if($rows>0) {
  echo "<table border=\"0\" width=\"100%\">\n";
  echo " <tr>\n";
  echo "  <th>From</th>\n";
  echo "  <th>To</th>\n";
  echo "  <th>Action</th>\n";
  echo " </tr>\n";
  while($row=mysql_fetch_row($sth)) {
   echo " <tr>\n";
   echo "  <td>$row[1]</td>\n";
   echo "  <td>$row[2]</td>\n";
   echo "  <td><a href=\"".$_SERVER['PHP_SELF']."?submit=Delete&amp;id=".$row[0]."&amp;list=$list\">Delete</a><td>\n";
   echo " </tr>\n";
  }
  echo "</table>\n";
 } else {
  echo "No entries found.\n";
 }
}

?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table border=0 cellpadding=1 cellspacing=1 class="mail">
 <tr>
  <th colspan=2>Add to Whitelist/Blacklist</th>
 </tr>
 <tr>
  <td class="heading">From:</td>
  <td><input type="text" name="entry" size=50 value="<?php echo $from; ?>"></td>
 </tr>
 <tr>
  <td class="heading">To:</td>
  <td><input <?php echo $disable_user; ?> type="text" name="to" size=22 value="<?php echo $touser; ?>">@<input <?php echo$disable_domain; ?> type="text" name="domain" size=25 value="<?php echo $todomain; ?>"></td>
 </tr>
 <tr>
  <td class="heading">List:</td>
  <td>
<?php
switch($_GET['list']) {
 case 'w':
  $w = 'CHECKED';
  break;
 case 'b':
  $b = 'CHECKED';
  break;
}
echo  "   <input type=\"radio\" value=\"w\" name=\"list\" $w>Whitelist &nbsp;&nbsp\n";
echo  "   <input type=\"radio\" value=\"b\" name=\"list\" $b>Blacklist\n";
?>
  </td>
 </tr>
 <tr>
  <td class="heading">Action:</td>
  <td><input type="reset" value="Reset">&nbsp;&nbsp;<input type="submit" value="Add" name="submit"></td>
 </tr>
<?php if(isset($errors)): ?>
 <tr>
  <td class="heading">Errors:</td>
  <td><?php echo implode("<br>",$errors); ?></td>
 </tr> 
<?php endif;
echo "</table>";
echo "</form>";

?>

<table border=0 cellpadding=1 cellspacing=1 width="100%" class="mail">
<tr>
 <th class="whitelist">Whitelist</th>
 <th class="blacklist">Blacklist</th>
</tr>
<tr>
 <td valign="top" width="50%">
  <!-- Whitelist -->
  <?php echo build_table("SELECT id, from_address, to_address FROM whitelist WHERE ".$_SESSION['global_list']." ORDER BY to_address",'w'); ?>
 </td>
 <td valign="top" width="50%">
  <!-- Blacklist -->
  <?php echo build_table("SELECT id, from_address, to_address FROM blacklist WHERE ".$_SESSION['global_list']." ORDER BY to_address",'b'); ?>
 </td>
</tr>
</table>

<?php
html_end();
?>
