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

require_once('./functions.php');
require_once('DB.php');
require_once('DB/Pager.php');
require_once('./filter.inc');

session_start();
require('login.function.php');

$refresh = html_start("Operation Results");

echo "<TABLE BORDER=\"0\" WIDTH=\"100%\" CLASS=\"maildetail\">\n";
echo " <THEAD>\n";
echo "  <TH>Spam Learn Results</TH>\n";
echo " </THEAD>\n";
echo "  <TR>\n";
echo "  <TD CLASS=\"detail\">";

// Iterate through the POST variables

if(is_array($_POST)) {
 foreach($_POST as $k=>$v) {
  if (preg_match('/^OPT-(.+)$/', $k, $Regs)) {
   $id = $Regs[1];
  } else {
   continue;
  }
  switch ($v) {
   case 'S':
    $type='spam';
    break;
   case 'H':
    $type='ham';
    break;
   case 'F':
    $type='forget';
    break;
    case 'R':
    $type='release';
    break;
   default:
    continue;
    break;
  }
  $items = quarantine_list_items($id,RPC_ONLY);
  echo "<TABLE WIDTH=\"100%\">\n";
  if(count($items) > 0) {
    $itemnum = array($num);
   if ($type == 'release'){
      if($quarantined = quarantine_list_items($id,RPC_ONLY)) {
         $to = $quarantined[0]['to'];
       }
       echo "<tr><td><a href=\"detail.php?id=$id\">$id</a></td><td>$type</td><td>" . quarantine_release($quarantined, $itemnum, $to, RPC_ONLY) . "</td></tr>\n";
    } else {
     echo "<tr><td><a href=\"detail.php?id=$id\">$id</a></td><td>$type</td><td>" . quarantine_learn($items, $itemnum, $type, RPC_ONLY) . "</td></tr>\n";
     }
   }
  }
  }else{
   echo "<tr><td colspan=3>Message $id not found in quarantine</td></tr>\n";
  }
  echo "</TABLE>\n";


echo "  </TD>\n";
echo " </TR>\n";
echo " </TABLE>\n";

echo "<p><a href=\"javascript:history.back(1)\">Back</a>";

//Add footer
html_end();
//Close database connection
dbclose();
?>
