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

html_start("SpamAssassin Bayes Database Info");
audit_log('Viewed SpamAssasin Bayes Database Info');
echo "<TABLE ALIGN=\"CENTER\" CLASS=\"boxtable\" BORDER=0 CELLSPACING=1 CELLPADDING=1 WIDTH=600>\n";
echo "<THEAD><TH COLSPAN=2>Bayes Database Information</TH></THEAD>\n";
$fh = popen(SA_DIR.'sa-learn -p '.SA_PREFS.' --dump magic','r');
while (!feof($fh)) {
 $line = rtrim(fgets($fh,4096));
 debug("line: ".$line."\n");
 if(preg_match('/(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+non-token data: (.+)/',$line,$regs)) {
  switch($regs[5]) {
   case 'nspam':
    echo "<TR><TD CLASS=\"heading\">Number of Spam Messages:</TD><TD ALIGN=\"RIGHT\">".number_format($regs[3])."</TD></TR>\n";
    break;
   case 'nham':
    echo "<TR><TD CLASS=\"heading\">Number of Ham Messages:</TD><TD ALIGN=\"RIGHT\">".number_format($regs[3])."</TD></TR>\n";
    break;
   case 'ntokens':
    echo "<TR><TD CLASS=\"heading\">Number of Tokens:</TD><TD ALIGN=\"RIGHT\">".number_format($regs[3])."</TD></TR>\n";
    break;
   case 'oldest atime':
    echo "<TR><TD CLASS=\"heading\">Oldest Token:</TD><TD ALIGN=\"RIGHT\">".date('r',$regs[3])."</TD></TR>\n";
    break;
   case 'newest atime':
    echo "<TR><TD CLASS=\"heading\">Newest Token:</TD><TD ALIGN=\"RIGHT\">".date('r',$regs[3])."</TD></TR>\n";
    break;
   case 'last journal sync atime':
    echo "<TR><TD CLASS=\"heading\">Last Journal Sync:</TD><TD ALIGN=\"RIGHT\">".date('r',$regs[3])."</TD></TR>\n";
    break;
   case 'last expiry atime':
    echo "<TR><TD CLASS=\"heading\">Last Expiry:</TD><TD ALIGN=\"RIGHT\">".date('r',$regs[3])."</TD></TR>\n";
    break;
   case 'last expire reduction count':
    echo "<TR><TD CLASS=\"heading\">Last Expiry Reduction Count:</TD><TD ALIGN=\"RIGHT\">".number_format($regs[3])." tokens</TD></TR>\n";
    break;
  }
 }
}
pclose($fh);
echo "</TABLE>\n";
html_end();
dbclose();
?>
