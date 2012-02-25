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

if($_SESSION['user_type']!=A){
echo "Not Authorized";
}
else{
html_start("Configuration");
audit_log('Viewed MailScanner configuration');

echo "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" CLASS=\"maildetail\" WIDTH=\"100%\">\n";
echo " <TR><TH COLSPAN=2>MailScanner Configuration</TH></TR>\n";
$fh = fopen(MS_CONFIG_DIR.'MailScanner.conf','r');
while (!feof($fh)) {
 $line = rtrim(fgets($fh,4096));
 //echo "line: ".$line."\n";
 if(preg_match("/^([^#].+)\s=\s([^#].*)/",$line,$regs)) {
  # Strip trailing comments
  $regs[2] = preg_replace("/#.*$/","",$regs[2]);
  # store %var% variables
  if(preg_match("/%.+%/",$regs[1])) {
  	$var[$regs[1]] = $regs[2];
  }
  $regs[1] = htmlentities($regs[1]);
  $regs[2] = htmlentities($regs[2]);
  # expand %var% variables
  if(preg_match("/(%.+%)/",$regs[2],$match)) {
  	$regs[2] = preg_replace("/%.+%/",$var[$match[1]],$regs[2]);
  }
  # See if parameter is a rules file
  if (@is_file($regs[2]) && @is_readable($regs[2]) && !@is_executable($regs[2])) {
   $regs[2] = "<A HREF=\"msrule.php?file=$regs[2]\">$regs[2]</A>";
  }
   $string = nl2br(str_replace("\\n","\n",$regs[2]));
   $string = preg_replace("/<br \/>/","<BR>",$string);
  echo "<TR><TD CLASS=\"heading\">$regs[1]</TD><TD>".$string."</TD></TR>\n";
 }
}
fclose($fh);

echo "</TABLE>\n";

// Add footer
html_end();
// Close any open db connections
dbclose();
}
