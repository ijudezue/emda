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

html_start("Message Detail ($_GET[id])");
ini_set("memory_limit",MEMORY_LIMIT);

$yes = "<SPAN CLASS=\"YES\">&nbsp;Y&nbsp;</SPAN>";
$no  = "<SPAN CLASS=\"NO\">&nbsp;N&nbsp;</SPAN>";

$mta = get_conf_var('mta');

$sql = "
 SELECT
  DATE_FORMAT(timestamp, '".DATE_FORMAT." ".TIME_FORMAT."') AS 'Received on:',
  hostname AS 'Received by:',
  clientip AS 'Received from:',
  headers 'Received Via:',
  id AS 'ID:',
  headers AS 'Message Headers:',
  from_address AS 'From:',
  to_address AS 'To:',
  subject AS 'Subject:',
  size AS 'Size:',
  archive AS 'Archive:',
  'Anti-Virus/Dangerous Content Protection' AS 'HEADER',
  CASE WHEN virusinfected>0 THEN '$yes' ELSE '$no' END AS 'Virus:',
  CASE WHEN nameinfected>0 THEN '$yes' ELSE '$no' END AS 'Blocked File:',
  CASE WHEN otherinfected>0 THEN '$yes' ELSE '$no' END AS 'Other Infection:',
  report AS 'Report:',
  'SpamAssassin' AS 'HEADER',
  CASE WHEN isspam>0 THEN '$yes' ELSE '$no' END AS 'Spam:',
  CASE WHEN ishighspam>0 THEN '$yes' ELSE '$no' END AS 'High Scoring Spam:',
  CASE WHEN issaspam>0 THEN '$yes' ELSE '$no' END AS 'SpamAssassin Spam:',
  CASE WHEN isrblspam>0 THEN '$yes' ELSE '$no' END AS 'Listed in RBL:',
  CASE WHEN spamwhitelisted>0 THEN '$yes' ELSE '$no' END AS 'Spam Whitelisted:',
  CASE WHEN spamblacklisted>0 THEN '$yes' ELSE '$no' END AS 'Spam Blacklisted:',
  spamreport AS 'SpamAssassin Autolearn:',
  sascore AS 'SpamAssassin Score:',
  spamreport AS 'Spam Report:',
  'Message Content Protection (MCP)' AS 'HEADER',
  CASE WHEN ismcp>0 THEN '$yes' ELSE '$no' END AS 'MCP:',
  CASE WHEN ishighmcp>0 THEN '$yes' ELSE '$no' END AS 'High Scoring MCP:',
  CASE WHEN issamcp>0 THEN '$yes' ELSE '$no' END AS 'SpamAssassin MCP:',
  CASE WHEN mcpwhitelisted>0 THEN '$yes' ELSE '$no' END AS 'MCP Whitelisted:',
  CASE WHEN mcpblacklisted>0 THEN '$yes' ELSE '$no' END AS 'MCP Blacklisted:',
  mcpsascore AS 'MCP Score:',
  mcpreport AS 'MCP Report:'
 FROM
  maillog
 WHERE
  ".$_SESSION['global_filter']."
 AND
  id = '$_GET[id]'
";

$result = dbquery($sql);

// Check to make sure something was returned
if(mysql_num_rows($result) == 0) {
 die("Message ID '".$_GET['id']."' not found!\n");
} else {
 audit_log('Viewed message detail (id='.mysql_escape_string($_GET['id']).')');
}

echo "<TABLE CLASS=\"maildetail\" BORDER=0 CELLSPACING=1 CELLPADDING=1 WIDTH=100%>\n";
while($row=mysql_fetch_array($result,MYSQL_BOTH)) {
 $listurl = "lists.php?host=".$row['Received from:']."&from=".$row['From:']."&to=".$row['To:'];
 for($f=0; $f<mysql_num_fields($result); $f++) {
  $fieldn = mysql_field_name($result,$f);
  if ($fieldn == "Received from:") {
   $output = "<table class=\"sa_rules_report\" width=\"100%\" cellspacing=0 cellpadding=0><tr><td>".$row[$f]."</td>";
   if(LISTS) { $output .= "<td align=\"right\">[<a href=\"$listurl&type=h&list=w\">Add to Whitelist</a>&nbsp|&nbsp;<a href=\"$listurl&type=h&list=b\">Add to Blacklist</a>]</td>";
   }
   $output .= "</tr></table>\n";
   $row[$f] = $output;
  }
  if ($fieldn == "Received Via:") {
   // Start Table
   $output = "<TABLE WIDTH=\"100%\" CLASS=\"sa_rules_report\">\n";
   $output .= " <THEAD>\n";
   $output .= " <TH>IP Address</TH>\n";
   $output .= " <TH>Hostname</TH>\n";
   $output .= " <TH>Country</TH>\n";
   $output .= " <TH>RBL</TH>\n";
   $output .= " <TH>Spam</TH>\n";
   $output .= " <TH>Virus</TH>\n";
   $output .= " <TH>All</TH>\n";
   $output .= " </THEAD>\n";
   if(is_array(($relays = get_mail_relays($row[$f])))) {
    foreach($relays as $relay) {
     $output .= " <TR>\n";
     $output .= " <TD>$relay</TD>\n";
     // Reverse lookup on address
     if(($host=gethostbyaddr($relay)) <> $relay) {
      $output .= " <TD>$host</TD>\n";
     } else {
      $output .= " <TD>(Reverse Lookup Failed)</TD>\n";
     }
     // Do GeoIP lookup on address
     if($geoip_country=return_geoip_country($relay)) {
      $output .= " <TD>$geoip_country</TD>\n";
     } else {
      $output .= " <TD>(GeoIP Lookup Failed)</TD>\n";
     }
     // Link to RBL Lookup
     $output .= " <TD ALIGN=\"CENTER\">[<a href=\"http://www.mxtoolbox.com/SuperTool.aspx?action=blacklist:$relay\">&nbsp;&nbsp;</a>]</TD>";
     // Link to Spam Report for this relay
     $output .= " <TD ALIGN=\"CENTER\">[<a href=\"rep_message_listing.php?relay=$relay&isspam=1\">&nbsp;&nbsp;</a>]</TD>";
     // Link to Virus Report for this relay
     $output .= " <TD ALIGN=\"CENTER\">[<a href=\"rep_message_listing.php?relay=$relay&isvirus=1\">&nbsp;&nbsp;</a>]</TD>";
     // Link to All Messages Report for this relay
     $output .= " <TD ALIGN=\"CENTER\">[<a href=\"rep_message_listing.php?relay=$relay\">&nbsp;&nbsp;</a>]</TD>";
     // Close table
     $output .= " </TR>\n";
    }
    $output .= "</TABLE>\n";
    $row[$f] = $output;
   } else {
    $row[$f] = "127.0.0.1";  // Must be local mailer (Exim)
   }
  }
  if ($fieldn == "Report:") {
   $row[$f] = nl2br(str_replace(",","<BR/>",htmlentities($row[$f])));
  }
  if ($fieldn == "From:") {
   $row[$f] = htmlentities($row[$f]);
   $output = "<table class=\"sa_rules_report\" width=\"100%\" cellspacing=0 cellpadding=0><tr><td>".$row[$f]."</td>";
   if(LISTS) { $output .= "<td align=\"right\">[<a href=\"$listurl&type=f&list=w\">Add to Whitelist</a>&nbsp|&nbsp;<a href=\"$listurl&type=f&list=b\">Add to Blacklist</a>]</td>";
   }
   $output .= "</tr></table>\n";
   $row[$f] = $output;
  }
  if ($fieldn == "To:" || $fieldn == "Subject:") {
   $row[$f] = htmlentities($row[$f]);
  }
  if ($fieldn == "To:") {
   $row[$f] = str_replace(",","<BR/>",$row[$f]);
  }
  if ($fieldn == "Subject:") {
   $row[$f] = decode_header($row[$f]);
   //$row[$f] = htmlentities($row[$f]);
  }
  if ($fieldn == "Spam Report:") {
   $row[$f] = format_spam_report($row[$f]);
  }
  if ($fieldn == "Size:") {
   $row[$f] = format_mail_size($row[$f]);
  }
  if ($fieldn == "Message Headers:") {
   $row[$f] = nl2br(str_replace(array("\\n","\t"),array("<BR/>","&nbsp; &nbsp; &nbsp;"),htmlentities($row[$f])));
  }
  if ($fieldn == "SpamAssassin Autolearn:") {
   if(($autolearn = sa_autolearn($row[$f]))!==false) {
    $row[$f] = $yes." ($autolearn)";
   } else {
    $row[$f] = $no;
   }
  }
  if ($fieldn == "Spam:" && !DISTRIBUTED_SETUP) {
   // Display actions if spam/not-spam
   if($row[$f] == $yes) {
    $row[$f] = $row[$f]."&nbsp;&nbsp;Action(s): ".str_replace(" ",", ",get_conf_var("SpamActions"));
   } else {
    $row[$f] = $row[$f]."&nbsp;&nbsp;Action(s): ".str_replace(" ",", ",get_conf_var("NonSpamActions"));
   }
  }
  if ($fieldn == "High Scoring Spam:" && $row[$f] == $yes) {
   // Display actions if high-scoring
   $row[$f] = $row[$f]."&nbsp;&nbsp;Action(s): ".str_replace(" ",", ",get_conf_var("HighScoringSpamActions"));
  }
  if ($fieldn == "MCP Report:") {
   $row[$f] = format_mcp_report($row[$f]);
  }
  // Handle dummy header fields
  if(mysql_field_name($result,$f)=='HEADER') {
   // Display header
   echo "<TR><TD CLASS=\"heading\" ALIGN=\"CENTER\" VALIGN=\"TOP\" COLSPAN=2>".$row[$f]."</TD></TR>\n";
  } else {
   // Actual data
   if(!empty($row[$f])) {
    // Skip empty rows (notably Spam Report when SpamAssassin didn't run)
    echo "<TR><TD CLASS=\"heading\" ALIGN=\"RIGHT\" VALIGN=\"TOP\" WIDTH=175>".mysql_field_name($result,$f)."</TD><TD CLASS=\"detail\">".$row[$f]."</TD></TR>\n";
   }
  }
 }
}
// Display the relay information only if there are matching
// rows in the relay table (maillog.id = relay.msg_id)...
if ($mta == 'postfix')
{ //version for postfix
$sql = "
 SELECT
  DATE_FORMAT(m.timestamp,'".DATE_FORMAT." ".TIME_FORMAT."') AS 'Date/Time',
  m.host AS 'Relayed by',
  m.relay AS 'Relayed to',
  m.delay AS 'Delay',
  m.status AS 'Status'
 FROM
  mtalog AS m
	LEFT JOIN mtalog_ids AS i ON (i.smtp_id = m.msg_id)
 WHERE
  i.smtpd_id='".$_GET['id']."'
 AND
  m.type='relay'
 ORDER BY
  m.timestamp DESC";
}
else
{ //version for sendmail
$sql = "
 SELECT
  DATE_FORMAT(timestamp,'".DATE_FORMAT." ".TIME_FORMAT."') AS 'Date/Time',
  host AS 'Relayed by',
  relay AS 'Relayed to',
  delay AS 'Delay',
  status AS 'Status'
 FROM
  mtalog
 WHERE
  msg_id='".$_GET['id']."'
 AND
  type='relay'
 ORDER BY
  timestamp DESC";
}
$sth1 = dbquery($sql);
if(mysql_num_rows($sth1) > 0) {
 // Display the relay table entries
 echo " <TR><TD CLASS=\"heading\" ALIGN=\"RIGHT\" VALIGN=\"TOP\" WIDTH=175>Relay Information:</TD><TD CLASS=\"detail\">\n";
 echo "  <TABLE CLASS=\"sa_rules_report\" WIDTH=100%>\n";
 echo "   <THEAD>\n";
 for($f=0;$f<mysql_num_fields($sth1);$f++) {
  echo "   <TH>".mysql_field_name($sth1, $f)."</TH>\n";
 }
 echo "   </THEAD>\n";
 while($row=mysql_fetch_row($sth1)) {
  echo "    <TR>\n";
  echo "     <TD CLASS=\"detail\" ALIGN=\"left\">$row[0]</TD>\n"; // Date/Time
  echo "     <TD CLASS=\"detail\" ALIGN=\"left\">$row[1]</TD>\n"; // Relayed by
  if(($lhost = @gethostbyaddr($row[2])) <> $row[2]) {
   echo "     <TD CLASS=\"detail\" ALIGN=\"left\">$lhost</TD>\n"; // Relayed to
  } else {
   echo "     <TD CLASS=\"detail\" ALIGN=\"left\">$row[2]</TD>\n";
  }
  echo "     <TD CLASS=\"detail\">$row[3]</TD>\n"; // Delay
  echo "     <TD CLASS=\"detail\">$row[4]</TD>\n"; // Status
  echo "    </TR>\n";
 }
 echo "  </TABLE>\n";
 echo " </TD</TR>\n";
}
echo "</TABLE>\n";

flush();

$quarantinedir = get_conf_var('QuarantineDir');
$quarantined = quarantine_list_items($_GET['id'],RPC_ONLY);
if((is_array($quarantined)) && (count($quarantined)>0)) {
 echo "<BR>\n";

 if($_GET['submit'] == "Submit") {
  debug("submit branch taken");
  // Reset error status
  $error=0;
  // Release
  if(isset($_GET['release'])) {
   // Send to the original recipient(s) or to an alternate address
   if(($_GET['alt_recpt_yn'] == "y")) {
    $to = $_GET['alt_recpt'];
   } else {
    $to = $quarantined[0]['to'];
   }
   $status[] = quarantine_release($quarantined,$_GET['release'],$to,RPC_ONLY);
  }
  // sa-learn
  if(isset($_GET['learn'])) {
  $status[] = quarantine_learn($quarantined,$_GET['learn'],$_GET['learn_type'],RPC_ONLY);
  }
  // Delete
  if(isset($_GET['delete'])) {
   $status[] = quarantine_delete($quarantined,$_GET['delete'],RPC_ONLY);
  }
  echo "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=\"100%\" CLASS=\"maildetail\">\n";
  echo " <THEAD>\n";
  echo "  <TH COLSPAN=2>Quarantine Command Results</TH>\n";
  echo " </THEAD>\n";
  if(isset($status)) {
   echo "  <TR>\n";
   echo "  <TD CLASS=\"heading\" WIDTH=150 ALIGN=\"RIGHT\" VALIGN=\"TOP\">Result Messages:</TD>\n";
   echo "  <TD CLASS=\"detail\">";
   foreach($status as $key=>$val) {
    echo "  $val<BR>\n";
   }
   echo "  </TD>\n";
   echo " </TR>\n";
  }
  if(isset($errors)) {
   echo " <TR>\n";
   echo "  <TD CLASS=\"heading\" WIDTH=150 ALIGN=\"RIGHT\" VALIGN=\"TOP\">Error Messages:</TD>\n";
   echo "  <TD CLASS=\"detail\">";
   foreach($errors as $key=>$val) {
    echo "  $val<BR>\n";
   }
   echo "  </TD>\n";
   echo " <TR>\n";
  }
  echo " <TR>\n";
  echo "  <TD CLASS=\"heading\" WIDTH=150 ALIGN=\"RIGHT\" VALIGN=\"TOP\">Error:</TD>\n";
  echo "  <TD CLASS=\"detail\">".($error?$yes:$no)."</TD>\n";
  echo " </TR>\n";
  echo "</TABLE>\n";
 } else {
  echo "<FORM ACTION=\"".$_SERVER['PHP_SELF']."\" NAME=\"quarantine\">\n";
  echo "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=\"100%\" CLASS=\"mail\">\n";
  echo " <THEAD>\n";
  echo "  <TH COLSPAN=7>Quarantine</TH>\n";
  echo " </THEAD>\n";
  echo " <THEAD>\n";
  echo "  <TH>Release</TH>\n";
  echo "  <TH>Delete</TH>\n";
  echo "  <TH>SA Learn</TH>\n";
  echo "  <TH>File</TH>\n";
  echo "  <TH>Type</TH>\n";
  echo "  <TH>Path</TH>\n";
  echo "  <TH>Dangerous?</TH>\n";
  echo " </THEAD>\n";
  foreach($quarantined as $item) {
   echo " <TR>\n";
   // Don't allow message to be released if it is marked as 'dangerous'
   // Currently this only applies to messages that contain viruses.
   if($item['dangerous'] !== "Y") {
    echo "  <TD ALIGN=\"CENTER\"><INPUT TYPE=\"checkbox\" NAME=\"release[]\" VALUE=\"".$item['id']."\"></TD>\n";
   } else {
    echo "<TD>&nbsp;&nbsp;</TD>\n";
   }
   echo "  <TD ALIGN=\"CENTER\"><INPUT TYPE=\"checkbox\" NAME=\"delete[]\" VALUE=\"".$item['id']."\"></TD>\n";
   // If the file is an rfc822 message then allow the file to be learnt
   // by SpamAssassin Bayesian learner as either spam or ham (sa-learn).
   if(preg_match('/message\/rfc822/',$item['type']) || $item['file'] == "message" && (strtoupper(get_conf_var("UseSpamAssassin")) == "YES")) {
    echo "   <TD ALIGN=\"CENTER\"><INPUT TYPE=\"checkbox\" NAME=\"learn[]\" VALUE=\"".$item['id']."\"><SELECT NAME=\"learn_type\"><OPTION VALUE=\"ham\">As Ham</OPTION><OPTION VALUE=\"spam\">As Spam</OPTION><OPTION VALUE=\"forget\">Forget</OPTION><OPTION VALUE=\"report\">As Spam+Report</OPTION><OPTION VALUE=\"revoke\">As Ham+Revoke</OPTION></SELECT></TD>\n";
   } else {
    echo "   <TD>&nbsp&nbsp</TD>\n";
   }
   echo "  <TD>".$item['file']."</TD>\n";
   echo "  <TD>".$item['type']."</TD>\n";
   // If the file is in message/rfc822 format and isn't dangerous - create a link to allow it to be viewed
   if($item['dangerous'] == "N" && preg_match('!message/rfc822!',$item['type'])) {
    echo "  <TD><A HREF=\"viewmail.php?id=".$item['msgid']."&filename=".substr($item['path'],strlen($quarantinedir)+1)."\">".substr($item['path'],strlen($quarantinedir)+1)."</A></TD>\n";
   } else {
    echo "  <TD>".substr($item['path'],strlen($quarantinedir)+1)."</TD>\n";
   }
   if($item['dangerous'] == "Y") {
    $dangerous = $yes;
   } else {
    $dangerous = $no;
   }
   echo "  <TD ALIGN=\"CENTER\">".$dangerous."</TD>\n";
   echo " </TR>\n";
  }
  echo " <TR>\n";
  if($item['dangerous'] == "Y") {
   echo "  <TD COLSPAN=6>&nbsp</TD>\n";
  } else {
   echo "  <TD COLSPAN=6><INPUT TYPE=\"checkbox\" NAME=\"alt_recpt_yn\" VALUE=\"y\">&nbsp;Alternate Recipient(s):&nbsp;<INPUT TYPE=\"TEXT\" NAME=\"alt_recpt\" SIZE=100></TD>\n";
  }
  echo "  <TD ALIGN=\"RIGHT\">\n";
  echo "<INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".$quarantined[0]['msgid']."\">\n";
  echo "<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Submit\">\n";
  echo "  </TD></TR>\n";
  echo "</TABLE>\n";
  echo "</FORM>\n";
 }
} else {
 // Error??
 if(!is_array($quarantined)) {
  echo "<br/>$quarantined\n";
 }
}
html_end();
dbclose();
?>
