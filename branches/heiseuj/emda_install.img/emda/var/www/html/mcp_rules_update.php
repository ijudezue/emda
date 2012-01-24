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

html_start("MCP Rule Description Update");

?>
<FORM METHOD="POST" ACTION=<?php echo $_SERVER['PHP_SELF']; ?>>
<INPUT TYPE="HIDDEN" NAME="run" VALUE="true">
<TABLE CLASS="BOXTABLE" WIDTH="100%">
 <TR>
  <TD>
   This utility is used to update the SQL database with up-to-date descriptions of the MCP rules which are displayed on the Message Detail screen.<BR>
   <BR>
   This utility should generally be run after an update to your MCP rules, however it is safe to run at any time as it only replaces the existing values and inserts only new values in the table (therefore preserving descriptions from potentially deprecated or removed rules).<BR>
  </TD>
 </TR>
 <TR>
  <TD ALIGN="CENTER"><BR><INPUT TYPE="SUBMIT" VALUE="Run Now"><BR><BR></TD>
 </TR>
<?php

if($_POST['run']) {
 echo "<TR><TD ALIGN=\"CENTER\"><TABLE CLASS=\"mail\" BORDER=0 CELLPADDING=1 CELLSPACING=1><THEAD><TH>Rule</TH><TH>Description</TH></THEAD>\n";
 $mcp_prefs_file = get_conf_var('MCPSpamAssassinPrefsFile');
 $mcp_local_rules_dir = get_conf_var('MCPSpamAssassinLocalRulesDir');
 $mcp_default_rules_dir = get_conf_var('MCPSpamAssassinDefaultRulesDir');
 $fh = popen("ls $mcp_prefs_file $mcp_local_rules_dir/*.cf $mcp_default_rules_dir/*.cf | xargs grep -h '^describe'",'r');
 audit_log('Ran MCP Rules Description Update');
 while (!feof($fh)) {
  $line = rtrim(fgets($fh,4096));
  debug("line: ".$line."\n");
  preg_match("/^describe\s+(\S+)\s+(.+)$/",$line,$regs);
  if ($regs[1] && $regs[2]) {
   $regs[1] = mysql_escape_string(ltrim(rtrim($regs[1])));
   $regs[2] = mysql_escape_string(ltrim(rtrim($regs[2])));
   echo "<TR><TD>".htmlentities($regs[1])."</TD><TD>".htmlentities($regs[2])."</TD></TR>\n";
   dbquery("REPLACE INTO mcp_rules VALUES ('$regs[1]','$regs[2]')");
   //debug("\t\tinsert: ".$regs[1].", ".$regs[2]);
  } else {
    debug("$line - did not match regexp, not inserting into database");
  }
 }
 pclose($fh);
 echo "</TABLE><BR></TD></TR>\n";
}
?>
</TABLE>
<?php
html_end();
dbclose();
?>
