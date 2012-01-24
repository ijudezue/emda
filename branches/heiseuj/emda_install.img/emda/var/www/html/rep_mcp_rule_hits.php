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

// Include of nessecary functions
require_once("./functions.php");
require_once("./filter.inc");

// Authenication checking
session_start();
require('login.function.php');

// add the header information such as the logo, search, menu, ....
html_start("MCP Rule Hits");

// File name
$filename = "images/cache/rep_mcp_rule_hits.png.".time()."";

$filter=report_start("MCP Rule Hits");

$sql = "
 SELECT
  mcpreport,
  ismcp
 FROM
  maillog
 WHERE
  mcpreport IS NOT NULL
 AND mcpreport != \"\"
".$filter->CreateSQL();

$result = dbquery($sql);
if(!mysql_num_rows($result) > 0) {
 die("Error: no rows retrieved from database\n");
}


// Initialise the array
$sa_array = array();

// Retrieve rows and insert into array
while ($row = mysql_fetch_object($result)) {
 // Clean-up input
 $row->mcpreport = preg_replace('/\n/','',$row->mcpreport);
 $row->mcpreport = preg_replace('/\t/',' ',$row->mcpreport);
 preg_match('/ \((.+?)\)/i',$row->mcpreport,$sa_rules);
 // Get rid of first match from the array
 $junk = array_shift($sa_rules);
 // Split the array, and get rid of the score and required values
 $sa_rules = explode(", ",$sa_rules[0]);
 $junk = array_shift($sa_rules);  // score=
 $junk = array_shift($sa_rules);  // required
 foreach($sa_rules as $rule) {
  // Check if SA scoring is present
  if(preg_match('/^(.+) (.+)$/',$rule,$regs)) {
   $rule = $regs[1];
  }
  $sa_array[$rule]['total']++;
  if ($row->ismcp <> 0) {
   $sa_array[$rule]['mcp']++;
  } else {
   $sa_array[$rule]['not-mcp']++;
  }
  // Initialise the other dimensions of the array
  if (!$sa_array[$rule]['mcp']) { $sa_array[$rule]['mcp']=0; }
  if (!$sa_array[$rule]['not-mcp']) { $sa_array[$rule]['not-mcp']=0; }
 }
}

reset($sa_array);
arsort($sa_array);
?>
<TABLE BORDER=0 CELLPADDING=10 CELLSPACING=0 HEIGHT=100% WIDTH=100%>
 <TR><TD ALIGN="CENTER"><IMG SRC="images/mailscannerlogo.gif"></TD></TR>
 <TR><TD ALIGN="CENTER">
<?php
echo "<TABLE ALIGN=\"CENTER\" BORDER=0>\n";
echo "
<TR BGCOLOR=\"#F7CE4A\">
 <TH>Rule</TH>
 <TH>Description</TH>
 <TH>Total</TH>
 <TH>Clean</TH>
 <TH>%</TH>
 <TH>MCP</TH>
 <TH>%</TH>
</TR>\n";
while ((list($key,$val) = each($sa_array)) && $count < 10) {
 echo "
<TR BGCOLOR=\"#EBEBEB\">
 <TD>$key</TD>
 <TD>".return_mcp_rule_desc(strtoupper($key))."</TD>
 <TD ALIGN=\"RIGHT\">".number_format($val['total'])."</TD>
 <TD ALIGN=\"RIGHT\">".number_format($val['not-mcp'])."</TD>
 <TD ALIGN=\"RIGHT\">".round(($val['not-mcp']/$val['total'])*100,1)."</TD>
 <TD ALIGN=\"RIGHT\">".number_format($val['mcp'])."</TD>
 <TD ALIGN=\"RIGHT\">".round(($val['mcp']/$val['total'])*100,1).
 "</TD></TR>";
}
echo "
  </TABLE>
 </TD>
</TR>
</TABLE>";

// Add footer
html_end();
// Close any open db connections
dbclose();
?>
