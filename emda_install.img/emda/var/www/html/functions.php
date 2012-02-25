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

// Set error level (some distro's have php.ini set to E_ALL)
error_reporting(E_ALL ^ E_NOTICE);

// Read in MailWatch configuration file
if(!(@include_once('conf.php'))==true) {
 die("Cannot read conf.php - please create it by copying conf.php.example and modifying the parameters to suit.\n");
}

if (SSL_ONLY && (!empty($_SERVER['PHP_SELF']))) {
 if (!$_SERVER['HTTPS'] == 'on') {
  header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
  exit;
 }
}

// Set PHP path to use local PEAR modules only
ini_set('include_path','.:'.MAILWATCH_HOME.'/pear:'.MAILWATCH_HOME.'/fpdf:'.MAILWATCH_HOME.'/xmlrpc');

// XML-RPC
@include_once('xmlrpc/xmlrpc.inc');
@include_once('xmlrpc/xmlrpcs.inc');
@include_once('xmlrpc/xmlrpc_wrappers.inc');

//begin added by HyTeK
require "grey_config.inc.php";
require "grey_db.inc.php";
//end added by HyTeK
include "postfix.inc";

/*
 For reporting of Virus names and statistics a regular expression matching
 the output of your virus scanner is required.  As Virus names vary across
 the vendors and are therefore impossible to match - you can only define one
 scanner as your primary scanner - this should be the scanner you wish to
 report against.  It defaults to the first scanner found in MailScanner.conf.

 Please submit any new regular expressions to the MailWatch mailing-list or
 to me - smf@f2s.com.

 If you are running MailWatch in DISTRIBUTED_MODE or you wish to override the
 selection of the regular expression - you will need to add on of the following
 statements to conf.php and set the regular expression manually.
*/
// define('VIRUS_REGEX', '<<your regexp here>>');
// define('VIRUS_REGEX', '/(\S+) was infected by (\S+)/');

if(!defined(VIRUS_REGEX)) {
 switch($scanner=get_primary_scanner()) {
  case 'none':
   define('VIRUS_REGEX', '/^Dummy$/');
   break;
  case 'sophos':
   define('VIRUS_REGEX', '/(>>>) Virus \'(\S+)\' found/');
   break;
  case 'sophossavi':
   define('VIRUS_REGEX', '/(\S+) was infected by (\S+)/');
   break;
  case 'clamav':
   define('VIRUS_REGEX', '/(.+) contains (\S+)/');
   break;
  case 'clamd':
   #ORIG#define('VIRUS_REGEX', '/(.+) contains (\S+)/');
   define('VIRUS_REGEX', '/(.+) was infected: (\S+)/');
   break;
  case 'clamavmodule':
   define('VIRUS_REGEX', '/(.+) was infected: (\S+)/');
   break;
  case 'f-prot':
   define('VIRUS_REGEX', '/(.+) Infection: (\S+)/');
   break;
 case 'f-protd-6':
   define('VIRUS_REGEX', '/(.+) Infection: (\S+)/');
   break;
  case 'mcafee6':
   define('VIRUS_REGEX', '/(.+) Found the (\S+) virus !!!/');
   break;
  case 'f-secure':
   define('VIRUS_REGEX', '/(.+) Infected: (\S+)/');
   break;
  case 'trend':
   define('VIRUS_REGEX', '/(Found virus) (\S+) in file (\S+)/');
   break;
  case 'bitdefender':
   define('VIRUS_REGEX', '/(\S+) Found virus (\S+)/');
   break;
  case 'kaspersky-4.5':
   define('VIRUS_REGEX', '/(.+) INFECTED (\S+)/');
   break;
  case 'etrust':
   define('VIRUS_REGEX', '/(\S+) is infected by virus: (\S+)/');
   break;
  case 'avg':
   define('VIRUS_REGEX', '/(Found virus) (\S+) in file (\S+)/');
   break;
  case 'norman':
   define('VIRUS_REGEX', '/(Found virus) (\S+) in file (\S+)/');
   break;
  case 'nod32-1.99':
   define('VIRUS_REGEX', '/(Found virus) (\S+) in (\S+)/');
   break;
  case 'antivir':
   define('VIRUS_REGEX', '/(ALERT:) \[(\S+) \S+\]/');
   break;
  #default:
  # die("<B>Error:</B><BR>\n&nbsp;Unable to select a regular expression for your primary virus scanner ($scanner) - please see the examples in functions.php to create one.\n");
  # break;
 }
} else {
 // Have to set manually as running in DISTRIBUTED_MODE
 die("<B>Error:</B><BR>\n&nbsp;You are running MailWatch in distributed mode therefore MailWatch cannot read your MailScanner configuration files to acertain your primary virus scanner - please edit functions.php and manually set the VIRUS_REGEX constant for your primary scanner.\n");
}

///////////////////////////////////////////////////////////////////////////////
// Functions
///////////////////////////////////////////////////////////////////////////////

function html_start($title,$refresh=0,$cacheable=true,$report=false) {
 if (!$cacheable) {
  // Cache control (as per PHP website)
  header("Expires: Sat, 10 May 2003 00:00:00 GMT");
  header("Last-Modified: ".gmdate("D, M d Y H:i:s")." GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
 }else{
   // calc an offset of 24 hours
 $offset = 3600 * 48;
 // calc the string in GMT not localtime and add the offset
 $expire = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
 //output the HTTP header
 Header($expire);
  header("Cache-Control: store, cache, must-revalidate, post-check=0, pre-check=1");
  header("Pragma: cache");
  }
   page_creation_timer();
 	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
	echo "<HTML>\n";
	echo "<HEAD>\n";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
 if($report){
echo "<TITLE>MailWatch Filter Report: $title </TITLE>\n";
echo "<LINK REL=\"StyleSheet\" TYPE=\"text/css\" HREF=\"style.css\">\n";
	if(!isset($_SESSION["filter"])) {
  require_once('./filter.inc');
  $filter = new Filter;
  $_SESSION["filter"] = $filter;
 } else {
  // Use existing filters
  $filter = $_SESSION["filter"];
  }
   audit_log('Ran report '.$title);

	}else{
echo "<TITLE>Mailwatch for Mailscanner - $title</TITLE>";
echo "<LINK REL=\"StyleSheet\" TYPE=\"text/css\" HREF=\"style.css\">";
	}

 if ($refresh > 0) {
  echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"$refresh\">";
 }
 
 $message_id=$_GET['id'];
 if(!$message_id){ $message_id =" ";}
echo "</HEAD>";
echo "<BODY>";
echo "<TABLE BORDER=\"0\" CELLPADDING=\"5\" WIDTH=\"100%\">";
echo "<TR>";
echo "<TD>";
echo "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\">";
echo "<TR>";
echo "<TD ALIGN=\"LEFT\"><IMG SRC=\"images/mailwatch-logo.gif\" alt=\"MailWatch for MailScanner\"></TD>";
echo "</TR>";
echo "<TR>";
echo "<TD VALIGN=\"BOTTOM\" ALIGN=\"LEFT\" CLASS=\"jump\">";
echo "<FORM ACTION=\"detail.php\">";
echo "Jump to message: <INPUT TYPE=\"TEXT\" NAME=\"id\" VALUE=\"$message_id\"><br>";
echo "</FORM>";
echo "Welcome ".$_SESSION['fullname'].".\n";
echo "</TD>";
echo "</TR>";
echo "</TABLE>";
echo "</TD>";
echo "<TD ALIGN=\"RIGHT\" VALIGN=\"TOP\">";
 
echo "   <TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" CLASS=\"mail\">\n";
echo "    <TR> <TH COLSPAN=2>Color Codes</TH> </TR> \n";
echo "    <TR> <TD>Bad Content/Infected</TD> <TD WIDTH=15 BGCOLOR=\"#B22222\"></TD> </TR>\n";
echo "    <TR> <TD>Spam</TD> <TD BGCOLOR=\"#F5BBBB\"></TD> </TR>\n";
echo "    <TR> <TD>High Spam</TD> <TD BGCOLOR=\"#EE6262\"></TD> </TR>\n";
echo "    <TR> <TD>MCP</TD> <TD BGCOLOR=\"lightblue\"></TD> </TR>\n";
echo "    <TR> <TD>High MCP</TD><TD BGCOLOR=\"darkblue\"></TD></TR>\n";
echo "    <TR> <TD>Whitelisted</TD> <TD BGCOLOR=\"lightgreen\"></TD> </TR>\n";
echo "    <TR> <TD>Blacklisted</TD> <TD BGCOLOR=\"black\"></TD> </TR>\n";
echo "    <TR> <TD>Clean</TD> <TD></TD> </TR>\n";
echo "   </TABLE>\n";
echo "  </TD>\n";

if(!DISTRIBUTED_SETUP && ($_SESSION['user_type'] == 'A' || $_SESSION['user_type'] == 'D')) {
 echo "  <TD ALIGN=\"RIGHT\" VALIGN=\"TOP\">\n";

 // Status table
 echo "   <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 CLASS=\"mail\" WIDTH=200>\n";
 echo "    <TR><TH COLSPAN=3>Status</TH></TR>\n";

 // MailScanner running?
 if(!DISTRIBUTED_SETUP) {
  $no = "<SPAN CLASS=\"yes\">&nbsp;NO&nbsp;</SPAN>";
  $yes  = "<SPAN CLASS=\"no\">&nbsp;YES&nbsp;</SPAN>";
  $junk = exec("ps ax | grep MailScanner | grep -v grep",$output);
  if(count($output)>0) {
   $running = $yes;
   $procs = count($output) - 1 . " children";
  } else {
   $running = $no;
   $procs = count($output) . " proc(s)";
  }
  echo "     <TR><TD>MailScanner:</TD><TD ALIGN=\"CENTER\">$running</TD><TD ALIGN=\"RIGHT\">$procs</TD></TR>\n";

  // is MTA running
  $output = "";
  $mta = get_conf_var('mta');
  $junk = exec("ps ax | grep $mta | grep -v grep | grep -v php",$output);
  if(count($output)>0) {
   $running = $yes;
  } else {
   $running = $no;
  }
  $procs = count($output)." proc(s)";
  echo "    <TR><TD>".ucwords($mta).":</TD><TD ALIGN=\"CENTER\">$running</TD><TD ALIGN=\"RIGHT\">$procs</TD></TR>\n";
 }

 // Load average
 if(file_exists("/proc/loadavg") && !DISTRIBUTED_SETUP) {
  $loadavg = file("/proc/loadavg");
  $loadavg = explode(" ", $loadavg[0]);
  $la_1m = $loadavg[0];
  $la_5m = $loadavg[1];
  $la_15m = $loadavg[2];
  echo "    <TR><TD>Load Average:</TD><TD ALIGN=\"RIGHT\" COLSPAN=2><TABLE WIDTH=\"100%\" class=\"mail\" CELLPADDING=0 CELLSPACING=0><TR><TD ALIGN=\"CENTER\">$la_1m</TD><TD ALIGN=\"CENTER\">$la_5m</TD><TD ALIGN=\"CENTER\">$la_15m</TD></TR></TABLE></TD>\n";
 }

 // Mail Queues display
//$incomingdir = get_conf_var('incomingqueuedir');
//$outgoingdir = get_conf_var('outgoingqueuedir');


 if($mta =='postfix' && ($_SESSION['user_type'] == 'A')){
//	if(is_readable($incomingdir) && is_readable($outgoingdir)){
         $inq = postfixinq();
	if(postfixallq() == "N/A"){
		$outq = "0";
	}else{
		$outq = postfixallq() - $inq;
	}
         echo "    <TR><TD COLSPAN=2><A HREF=\"postfixmailq.php\">Inbound:</A></TD><TD ALIGN=\"RIGHT\">".$inq."</TD>\n";
         echo "    <TR><TD COLSPAN=2><A HREF=\"postfixmailq.php\">Outbound:</A></TD><TD ALIGN=\"RIGHT\">".$outq."</TD>\n";
         //begin added by HyTeK
         if($line["count"])
         {
         	echo "    <TR><TD COLSPAN=2><A HREF=\"grey_connect.php\">Greylisted:</A></TD><TD ALIGN=\"RIGHT\">".$line["count"]."</TD>\n";
         }else{
         	echo "    <TR><TD COLSPAN=2><A HREF=\"grey_connect.php\">Greylisted:</A></TD><TD ALIGN=\"RIGHT\">0</TD>\n";
         }
         //end added by HyTeK
//	}else{
//		echo "    <TR><TD COLSPAN=3>Please verify read permissions on ".$incomingdir." and ".$outgoingdir."</TD></tr>\n";
//		 //begin added by HyTeK
//         echo "    <TR><TD COLSPAN=2><A HREF=\"grey_connect.php\">Greylisted:</A></TD><TD ALIGN=\"RIGHT\">".$line["count"]."</TD>\n";
//       //end added by HyTeK
//		 }
 }elseif(MAILQ && ($_SESSION['user_type'] == 'A')) {
  $inq = mysql_result(dbquery("SELECT COUNT(*) FROM inq WHERE ".$_SESSION['global_filter']),0);
  $outq = mysql_result(dbquery("SELECT COUNT(*) FROM outq WHERE ".$_SESSION['global_filter']),0);
  echo "    <TR><TD COLSPAN=3 CLASS=\"heading\" ALIGN=\"CENTER\">Mail Queues</TD></TR>\n";
  echo "    <TR><TD COLSPAN=2><A HREF=\"mailq.php?queue=inq\">Inbound:</A></TD><TD ALIGN=\"RIGHT\">".$inq."</TD>\n";
  echo "    <TR><TD COLSPAN=2><A HREF=\"mailq.php?queue=outq\">Outbound:</A></TD><TD ALIGN=\"RIGHT\">".$outq."</TD>\n";
  //begin added by HyTeK
         if($line["count"])
         {
         	echo "    <TR><TD COLSPAN=2><A HREF=\"grey_connect.php\">Greylisted:</A></TD><TD ALIGN=\"RIGHT\">".$line["count"]."</TD>\n";
         }else{
         	echo "    <TR><TD COLSPAN=2><A HREF=\"grey_connect.php\">Greylisted:</A></TD><TD ALIGN=\"RIGHT\">0</TD>\n";
         }
  //end added by HyTeK
  }

  // drive display
 if($_SESSION['user_type'] == 'A') {
   echo "    <TR><TD COLSPAN=3 CLASS=\"heading\" ALIGN=\"CENTER\">Free Drive Space</TD></TR>\n";

  function formatSize($size){
    switch (true){
    case ($size > 1099511627776):
        $size /= 1099511627776;
        $suffix = 'TB';
    break;
    case ($size > 1073741824):
        $size /= 1073741824;
       $suffix = 'GB';
    break;
    case ($size > 1048576):
        $size /= 1048576;
       $suffix = 'MB';
    break;
    case ($size > 1024):
        $size /= 1024;
        $suffix = 'KB';
        break;
    default:
        $suffix = 'B';
    }
    return round($size, 2).$suffix;
  }
  function get_disks(){
    if(php_uname('s')=='Windows NT'){
        // windows
        $disks=`fsutil fsinfo drives`;
        $disks=str_word_count($disks,1);
        if($disks[0]!='Drives')return '';
        unset($disks[0]);
        foreach($disks as $key=>$disk)$disks[$key]=$disk.':\\';
        return $disks;
    }else{
        // unix
        $data=`mount`;
        $data=explode("\n",$data);
        foreach ($data as $disk) {
                $drive = preg_split("/[\s]+/", $disk);
               if (substr($drive[0],0,5) == '/dev/')
                        $disks[] = $drive;
        }
        return $disks;
    }
  }
  foreach (get_disks() as $disk) {
    $free = formatSize(disk_free_space($disk[2]));
    $used = formatSize(disk_total_space($disk[2]));
    if (round($free/$used,2) > .1) {
        $percent ="<span style='color:red'>";
    }else{
        $percent = "<span>";
    }
    $percent = " [";
    $percent.= round($free/$used,2) * 100;
    $percent.= "%] ";
    echo "    <TR><TD>".$disk[2]."</TD><TD COLSPAN=2 ALIGN=\"RIGHT\">".$free.$percent."</TD>\n";
  }


 }
 echo "  </TABLE>\n";
 echo "  </TD>\n";
}

  echo "<TD ALIGN=\"RIGHT\">";
  
$sql = "
 SELECT
  COUNT(*) AS processed,
  SUM(
   CASE WHEN (
    (virusinfected=0 OR virusinfected IS NULL)
    AND (nameinfected=0 OR nameinfected IS NULL)
    AND (otherinfected=0 OR otherinfected IS NULL)
    AND (isspam=0 OR isspam IS NULL)
    AND (ishighspam=0 OR ishighspam IS NULL)
    AND (ismcp=0 OR ismcp IS NULL)
    AND (ishighmcp=0 OR ishighmcp IS NULL)
   ) THEN 1 ELSE 0 END
  ) AS clean,
  ROUND((
   SUM(
    CASE WHEN (
     (virusinfected=0 OR virusinfected IS NULL)
     AND (nameinfected=0 OR nameinfected IS NULL)
     AND (otherinfected=0 OR otherinfected IS NULL)
     AND (isspam=0 OR isspam IS NULL)
     AND (ishighspam=0 OR ishighspam IS NULL)
     AND (ismcp=0 OR ismcp IS NULL)
     AND (ishighmcp=0 OR ishighmcp IS NULL)
    ) THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS cleanpercent,
  SUM(
   CASE WHEN
    virusinfected>0
   THEN 1 ELSE 0 END
  ) AS viruses,
  ROUND((
   SUM(
    CASE WHEN
     virusinfected>0
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS viruspercent,
  SUM(
   CASE WHEN
    nameinfected>0
    AND (virusinfected=0 OR virusinfected IS NULL)
    AND (otherinfected=0 OR otherinfected IS NULL)
    AND (isspam=0 OR isspam IS NULL)
    AND (ishighspam=0 OR ishighspam IS NULL)
   THEN 1 ELSE 0 END
  ) AS blockedfiles,
  ROUND((
   SUM(
    CASE WHEN
     nameinfected>0
     AND (virusinfected=0 OR virusinfected IS NULL)
     AND (otherinfected=0 OR otherinfected IS NULL)
     AND (isspam=0 OR isspam IS NULL)
     AND (ishighspam=0 OR ishighspam IS NULL)
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS blockedfilespercent,
  SUM(
   CASE WHEN
    otherinfected>0
    AND (nameinfected=0 OR nameinfected IS NULL)
    AND (virusinfected=0 OR virusinfected IS NULL)
    AND (isspam=0 OR isspam IS NULL)
    AND (ishighspam=0 OR ishighspam IS NULL)
   THEN 1 ELSE 0 END
  ) AS otherinfected,
  ROUND((
   SUM(
    CASE WHEN
     otherinfected>0
     AND (nameinfected=0 OR nameinfected IS NULL)
     AND (virusinfected=0 OR virusinfected IS NULL)
     AND (isspam=0 OR isspam IS NULL)
     AND (ishighspam=0 OR ishighspam IS NULL)
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS otherinfectedpercent,
  SUM(
   CASE WHEN
    isspam>0
    AND (virusinfected=0 OR virusinfected IS NULL)
    AND (nameinfected=0 OR nameinfected IS NULL)
    AND (otherinfected=0 OR otherinfected IS NULL)
    AND (ishighspam=0 OR ishighspam IS NULL)
   THEN 1 ELSE 0 END
  ) AS spam,
  ROUND((
   SUM(
    CASE WHEN
     isspam>0
     AND (virusinfected=0 OR virusinfected IS NULL)
     AND (nameinfected=0 OR nameinfected IS NULL)
     AND (otherinfected=0 OR otherinfected IS NULL)
     AND (ishighspam=0 OR ishighspam IS NULL)
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS spampercent,
  SUM(
   CASE WHEN
    ishighspam>0
    AND (virusinfected=0 OR virusinfected IS NULL)
    AND (nameinfected=0 OR nameinfected IS NULL)
    AND (otherinfected=0 OR otherinfected IS NULL)
   THEN 1 ELSE 0 END
  ) AS highspam,
  ROUND((
   SUM(
    CASE WHEN
     ishighspam>0
     AND (virusinfected=0 OR virusinfected IS NULL)
     AND (nameinfected=0 OR nameinfected IS NULL)
     AND (otherinfected=0 OR otherinfected IS NULL)
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS highspampercent,
  SUM(
   CASE WHEN
    ismcp>0
    AND (virusinfected=0 OR virusinfected IS NULL)
    AND (nameinfected=0 OR nameinfected IS NULL)
    AND (otherinfected=0 OR otherinfected IS NULL)
    AND (isspam=0 OR isspam IS NULL)
    AND (ishighspam=0 OR ishighspam IS NULL)
    AND (ishighmcp=0 OR ishighmcp IS NULL)
   THEN 1 ELSE 0 END
  ) AS mcp,
  ROUND((
   SUM(
    CASE WHEN
     ismcp>0
     AND (virusinfected=0 OR virusinfected IS NULL)
     AND (nameinfected=0 OR nameinfected IS NULL)
     AND (otherinfected=0 OR otherinfected IS NULL)
     AND (isspam=0 OR isspam IS NULL)
     AND (ishighspam=0 OR ishighspam IS NULL)
     AND (ishighmcp=0 OR ishighmcp IS NULL)
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS mcppercent,
  SUM(
   CASE WHEN
    ishighmcp>0
    AND (virusinfected=0 OR virusinfected IS NULL)
    AND (nameinfected=0 OR nameinfected IS NULL)
    AND (otherinfected=0 OR otherinfected IS NULL)
    AND (isspam=0 OR isspam IS NULL)
    AND (ishighspam=0 OR ishighspam IS NULL)
   THEN 1 ELSE 0 END
  ) AS highmcp,
  ROUND((
   SUM(
    CASE WHEN
     ishighmcp>0
     AND (virusinfected=0 OR virusinfected IS NULL)
     AND (nameinfected=0 OR nameinfected IS NULL)
     AND (otherinfected=0 OR otherinfected IS NULL)
     AND (isspam=0 OR isspam IS NULL)
     AND (ishighspam=0 OR ishighspam IS NULL)
    THEN 1 ELSE 0 END
   )/COUNT(*))*100,1
  ) AS highmcppercent,
  SUM(size) AS size
 FROM
  maillog
 WHERE
  date = CURRENT_DATE()
 AND
  ".$_SESSION['global_filter']."
";

$sth = dbquery($sql);
while($row = mysql_fetch_object($sth)) {
 echo "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 CLASS=\"mail\" WIDTH=200>\n";
 echo " <TR><TH ALIGN=\"CENTER\" COLSPAN=3>Today's Totals</TH></TR>\n";
 echo " <TR><TD>Processed:</TD><TD ALIGN=\"RIGHT\">".number_format($row->processed)."</TD><TD ALIGN=\"RIGHT\">".format_mail_size($row->size)."</TD></TR>\n";
 echo " <TR><TD>Clean:</TD><TD ALIGN=\"RIGHT\">".number_format($row->clean)."</TD><TD ALIGN=\"RIGHT\">$row->cleanpercent%</TD></TR>\n";
 echo " <TR><TD>Viruses:</TD><TD ALIGN=\"RIGHT\">".number_format($row->viruses)."</TD><TD ALIGN=\"RIGHT\">$row->viruspercent%</TR>\n";
 echo " <TR><TD>Top Virus:</TD><TD COLSPAN=2 ALIGN=\"RIGHT\" style=\"white-space:nowrap\">".return_todays_top_virus()."</TD></TR>\n";
 echo " <TR><TD>Blocked files:</TD><TD ALIGN=\"RIGHT\">".number_format($row->blockedfiles)."</TD><TD ALIGN=\"RIGHT\">$row->blockedfilespercent%</TD></TR>\n";
 echo " <TR><TD>Others:</TD><TD ALIGN=\"RIGHT\">".number_format($row->otherinfected)."</TD><TD ALIGN=\"RIGHT\">$row->otherinfectedpercent%</TD></TR>\n";
 echo " <TR><TD>Spam:</TD><TD ALIGN=\"RIGHT\">".number_format($row->spam)."</TD><TD ALIGN=\"RIGHT\">$row->spampercent%</TD></TR>\n";
 echo " <TR><TD style=\"white-space:nowrap\">High Scoring Spam:</TD><TD ALIGN=\"RIGHT\">".number_format($row->highspam)."</TD><TD ALIGN=\"RIGHT\">$row->highspampercent%</TD></TR>\n";
 echo " <TR><TD>MCP:</TD><TD ALIGN=\"RIGHT\">".number_format($row->mcp)."</TD><TD ALIGN=\"RIGHT\">$row->mcppercent%</TD></TR>\n";
 echo " <TR><TD style=\"white-space:nowrap\">High Scoring MCP:</TD><TD ALIGN=\"RIGHT\">".number_format($row->highmcp)."</TD><TD ALIGN=\"RIGHT\">$row->highmcppercent%</TD></TR>\n";
 echo "</TABLE>\n";

 // Navigation links - put them into an array to allow them to be switched
 // on or off as necessary and to allow for the table widths to be calculated.
 $nav['status.php'] 	= "Recent Messages";
 if(LISTS) { $nav['lists.php'] = "Lists"; }
 if(!DISTRIBUTED_SETUP) { $nav['quarantine.php'] = "Quarantine"; }
 //begin added by HyTeK
 //if (MAILQ && ($GLOBALS['user_type'] == 'A')) {
     $nav['grey.php']    = "Greylist";
 //}
 //end added by HyTeK
 $nav['reports.php'] 	= "Reports";
 $nav['other.php'] 	= "Tools/Links";
 $nav['logout.php']	= "Logout";
 $table_width = round(100/count($nav));
}

//Navigation table
echo "  </TD>";
echo " </TR>";
echo "<TR>";
echo "<TD COLSPAN=\"4\">";

echo "<UL id=\"menu\" class=\"yellow\">";

// Display the different words
foreach($nav as $url=>$desc) {
$active_url = "".MAILWATCH_HOME."/".$url."";
  if($_SERVER['SCRIPT_FILENAME'] == $active_url){
   echo "<li class=\"active\"><a href=\"$url\">$desc</a></li>";
   }else{
   echo "<li><a href=\"$url\">$desc</a></li>";
   }
}

echo "
 </UL>
 </TD>
 </TR>
 <TR>
  <TD COLSPAN=\"4\">";

  if($report){
 $return_items = $filter;
 }else{
 $return_items = $refresh;
 }
 return $return_items;
}

function report_start($title) {
 // Cache control (as per PHP website)
 header("Expires: Sat, 10 May 2003 00:00:00 GMT");
 header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
 header("Cache-Control: no-store, no-cache, must-revalidate");
 header("Cache-Control: post-check=0, pre-check=0", false);
 // Start session
 session_start();
 // If filters are not present - create a blank set
 if(!isset($_SESSION["filter"])) {
  require_once('./filter.inc');
  $filter = new Filter;
  $_SESSION["filter"] = $filter;
 } else {
  // Use existing filters
  $filter = $_SESSION["filter"];
 }
?>
<HTML>
<HEAD>
<TITLE>MailWatch Filter Report: <?php echo $title; ?></TITLE>
<LINK REL="StyleSheet" TYPE="text/css" HREF="style.css">
</HEAD>
<BODY>
<?php
 audit_log('Ran report '.$title);

  return $filter;
}

function html_end($footer="") {

echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo $footer;
echo "<br>\n";
echo "<CENTER><p style=\"font-size:13px\"><i>\n";
echo page_creation_timer();
echo "</i></p></CENTER>\n";
echo "<br>\n";
echo "</body>\n";
echo "</html>\n";

}


function dbconn() {
 $link = mysql_connect(DB_HOST,DB_USER,DB_PASS)
        or die ("Could not connect to database: ".mysql_error());
 mysql_select_db(DB_NAME) or die("Could not select db: ".mysql_error());
 return $link;
}

function dbclose(){
	If ($link) {
	mysql_close($link);
		}
}

function dbquery($sql) {
 dbconn();
 if(DEBUG && headers_sent() && preg_match('/\bselect\b/i',$sql)) {
  echo "<!--\n\n";
  $dbg_sql = "EXPLAIN ".$sql;
  echo "SQL:\n\n$sql\n\n";
  $result = mysql_query($dbg_sql) or die("Error executing query: ".mysql_error());
  $fields = mysql_num_fields($result);
  $rows = mysql_num_rows($result);
  while($row=mysql_fetch_row($result)) {
   for($f=0; $f<$fields; $f++) {
    echo mysql_field_name($result,$f).": ".$row[$f]."\n";
   }
  }
  //dbtable("SHOW STATUS");
  echo "\n-->\n\n";
 }
 $result = mysql_query($sql) or die("<B>Error executing query: </B><BR><BR>".mysql_error()."<BR><BR><B>SQL:</B><BR><PRE>$sql</PRE>");
 return $result;
}

function quote_smart($value) {
 if(get_magic_quotes_gpc()) {
  $value = stripslashes($value);
 }
 $value = "'".mysql_escape_string($value)."'";
 return $value;
}


function safe_value($value) {
 if(get_magic_quotes_gpc()) {
  $value = stripslashes($value);
 }
 $value = mysql_escape_string($value);
 return $value;
}


function sa_autolearn($spamreport) {
 switch(true) {
  case(preg_match('/autolearn=spam/',$spamreport)):
   return 'spam';
  case(preg_match('/autolearn=not spam/',$spamreport)):
   return 'not spam';
  default:
   return false;
 }
}

function format_spam_report($spamreport) {
 /* Run regex against the MailScanner spamreport
 picking out the (score=xx, required x, RULES...) */
 if(preg_match('/\s\((.+?)\)/i',$spamreport,$sa_rules)) {
  // Get rid of the first match from the array
  array_shift($sa_rules);
  // Split the array
  $sa_rules = explode(", ",$sa_rules[0]);
  // Check to make sure a check was actually run
  if ($sa_rules[0] == "Message larger than max testing size" || $sa_rules[0] == "timed out") {
   return $sa_rules[0];
  }
  // Get rid of the 'score=', 'required' and 'autolearn=' lines
  foreach(array('cached', 'score=','required','autolearn=') as $val) {
   if(preg_match("/$val/",$sa_rules[0])) {
    array_shift($sa_rules);
   }
  }
  $output_array = array();
  while(list($key,$val)=each($sa_rules)) {
   array_push($output_array,get_sa_rule_desc($val));
  }
  // Return the result as an html formatted string
  if(count($output_array)>0) {
   return "<TABLE BORDER=0 CLASS=\"sa_rules_report\" CELLPADDING=1 CELLSPACING=2 WIDTH=\"100%\">"."<tr><TH>Score</TH><TH>Matching Rule</TH><TH>Description</TH></tr>".implode("\n",$output_array)."</TABLE>\n";
  } else {
   return $spamreport;
  }
 } else {
  // Regular expression did not match, return unmodified report instead
  return $spamreport;
 }
}

function get_sa_rule_desc($rule) {
 // Check if SA scoring is enabled
 if(preg_match('/^(.+) (.+)$/',$rule,$regs)) {
  $rule = $regs[1];
  $rule_score = $regs[2];
 } else {
  $rule_score = "";
 }
 $result = dbquery("SELECT rule, rule_desc FROM sa_rules WHERE rule='$rule'");
 $row = mysql_fetch_object($result);
 if ($row->rule && $row->rule_desc) {
  return("<TR><TD ALIGN=\"LEFT\">$rule_score</TD><TD WIDTH=\"200\">$row->rule</TD><TD>$row->rule_desc</TD></TR>");
 } else {
  return "<TR><TD>$rule_score<TD>$rule</TD><TD>&nbsp;</TD></TR>";
 }
}

function return_sa_rule_desc($rule) {
 $result = dbquery("SELECT rule, rule_desc FROM sa_rules WHERE rule='$rule'");
 $row = mysql_fetch_object($result);
 return $row->rule_desc;
}

function format_mcp_report($mcpreport) {
 // Clean-up input
 $mcpreport = preg_replace('/\n/','',$mcpreport);
 $mcpreport = preg_replace('/\t/',' ',$mcpreport);
 /* Run regex against the MailScanner mcpreport
 picking out the (score=xx, required x, RULES...) */
 if(preg_match('/ \((.+?)\)/i',$mcpreport,$sa_rules)) {
  // Get rid of the first match from the array
  array_shift($sa_rules);
  // Split the array
  $sa_rules = explode(", ",$sa_rules[0]);
  // Check to make sure a check was actually run
  if ($sa_rules[0] == "Message larger than max testing size" || $sa_rules[0] == "timed out") {
   return $sa_rules[0];
  }
  // Get rid of the 'score=', 'required' and 'autolearn=' lines
  foreach(array('score=','required','autolearn=') as $val) {
   if(preg_match("/$val/",$sa_rules[0])) {
    array_shift($sa_rules);
   }
  }
  $output_array = array();
  while(list($key,$val)=each($sa_rules)) {
   array_push($output_array,get_mcp_rule_desc($val));
  }
  // Return the result as an html formatted string
  if(count($output_array)>0) {
   return "<TABLE BORDER=0 CLASS=\"sa_rules_report\" CELLPADDING=1 CELLSPACING=2 WIDTH=100%>"."<tr><TH>Score</TH><TH>Matching Rule</TH><TH>Description</TH></tr>".implode("\n",$output_array)."</TABLE>\n";
  } else {
   return $mcpreport;
  }
 } else {
  // Regular expression did not match, return unmodified report instead
  return $mcpreport;
 }
}

function get_mcp_rule_desc($rule) {
 // Check if SA scoring is enabled
 if(preg_match('/^(.+) (.+)$/',$rule,$regs)) {
  $rule = $regs[1];
  $rule_score = $regs[2];
 } else {
  $rule_score = "";
 }
 $result = dbquery("SELECT rule, rule_desc FROM mcp_rules WHERE rule='$rule'");
 $row = mysql_fetch_object($result);
 if ($row->rule && $row->rule_desc) {
  return("<TR><TD ALIGN=\"LEFT\">$rule_score</TD><TD WIDTH=\"200\">$row->rule</TD><TD>$row->rule_desc</TD></TR>");
 } else {
  return "<TR><TD>$rule_score<TD>$rule</TD><TD>&nbsp;</TD></TR>";
 }
}

function return_mcp_rule_desc($rule) {
 $result = dbquery("SELECT rule, rule_desc FROM mcp_rules WHERE rule='$rule'");
 $row = mysql_fetch_object($result);
 return $row->rule_desc;
}

function return_todays_top_virus() {
 $sql = "
SELECT
 report
FROM
 maillog
WHERE
 virusinfected>0
AND
 date = CURRENT_DATE()
";
 $result = dbquery($sql);
 $virus_array = array();
 while($row=mysql_fetch_object($result)) {
  if(preg_match(VIRUS_REGEX,$row->report,$virus_reports)) {
   $virus = return_virus_link($virus_reports[2]);
   $virus_array[$virus]++;
  }
 }
 arsort($virus_array);
 reset($virus_array);
 // Get the topmost entry from the array
 if ((list($key, $val) = each($virus_array)) != "") {
  // Check and make sure there first placed isn't tied!
  $saved_key = $key;
  $saved_val = $val;
  list($key, $val) = each($virus_array);
  if ($val != $saved_val) {
   return $saved_key;
  } else {
   // Tied first place - return none
   return "None";
  }
 } else {
  return "None";
 }
}

function format_mail_size($size_in_bytes, $decimal_places=1) {
 // Setup common measurements
 $kb = 1024;		// Kilobyte
 $mb = 1024*$kb;	// Megabyte
 $gb = 1024*$mb;	// Gigabyte
 $tb = 1024*$gb;	// Terabyte
 if($size_in_bytes < $kb) {
  return $size_in_bytes."b";
 } else if ($size_in_bytes < $mb) {
  return round($size_in_bytes/$kb,$decimal_places)."Kb";
 } else if ($size_in_bytes < $gb) {
  return round($size_in_bytes/$mb,$decimal_places)."Mb";
 } else if ($size_in_bytes < $tb) {
  return round($size_in_bytes/$gb,$decimal_places)."Gb";
 } else {
  return round($size_in_bytes/$tb,$decimal_places)."Tb";
 }
}

function format_report_volume(&$data_in, &$info_out) {
 // Measures
 $kb = 1024;
 $mb = 1024*$kb;
 $gb = 1024*$mb;
 $tb = 1024*$gb;

 // Copy the data to a temporary variable
 $temp = $data_in;

 // Work out the average size of values in the array
 $count = count($temp);
 $sum = array_sum($temp);
 $average = $sum/$count;

 // Work out the largest value in the array
 arsort($temp);
 $largest = array_pop($temp);

 // Calculate the correct display size for the average value
 if($average < $kb) {
  $info_out['formula'] = 1;
  $info_out['shortdesc'] = "b";
  $info_out['longdesc'] = "Bytes";
 } else if ($average < $mb) {
  $info_out['formula'] = $kb;
  $info_out['shortdesc'] = "Kb";
  $info_out['longdesc'] = "Kilobytes";
 } else if ($average < $gb) {
  $info_out['formula'] = $mb;
  $info_out['shortdesc'] = "Mb";
  $info_out['longdesc'] = "Megabytes";
 } else if ($average < $tb) {
  $info_out['formula'] = $gb;
  $info_out['shortdesc'] = "Gb";
  $info_out['longdesc'] = "Gigabytes";
 } else {
  $info_out['formula'] = $tb;
  $info_out['shortdesc'] = "Tb";
  $info_out['longdesc'] = "Terabytes";
 }

 // Modify the original data accordingly
 for($i=0; $i<sizeof($data_in); $i++) {
  $data_in[$i] = $data_in[$i]/$info_out['formula'];
 }
}

function trim_output($input, $maxlen) {
 if($maxlen > 0 && strlen($input) >= $maxlen) {
  $output = substr($input, 0, $maxlen)."...";
  return $output;
 } else {
  return $input;
 }
}

function get_default_ruleset_value($file) {
 $fh = fopen($file,'r') or die("Cannot open ruleset file $file");
 while(!feof($fh)) {
  $line = rtrim(fgets($fh, filesize($file)));
  if(preg_match('/^([^#]\S+:)\s+(\S+)\s+([^#]\S+)/',$line,$regs)) {
   if($regs[2]=='default') {
    return $regs[3];
   }
  }
 }
 fclose($fh);
 return false;
}

function get_conf_var($name) {
 if(DISTRIBUTED_SETUP) { return false; }
 // Translate input if using LDAP on MSEE
 if(MSEE) {
  $name = translate_etoi($name);
  return ldap_get_conf_var($name);
 } else {
  $msconfig = MS_CONFIG_DIR."MailScanner.conf";
  $fh = fopen($msconfig,'r')
   or die("Cannot open MailScanner configuration file");
  while(!feof($fh)) {
   $line = rtrim(fgets($fh, filesize($msconfig)));
   if(preg_match('/^([^#].+)\s=\s([^#].+)/',$line,$regs)) {
    ##### AJOS1 CHANGE #####
    #ORIG# $regs[1] = ereg_replace(' *', '', $regs[1]);
    ##### AJOS1 CHANGE #####
    $regs[1] = preg_replace('/ */', '', $regs[1]);
    # Strip trailing comments
    $regs[2] = preg_replace("/#.*$/","",$regs[2]);
    # store %var% variables
    if(preg_match("/%.+%/",$regs[1])) {
     $var[$regs[1]] = $regs[2];
    }
    # expand %var% variables
    if(preg_match("/(%.+%)/",$regs[2],$match)) {
     $regs[2] = preg_replace("/%.+%/",$var[$match[1]],$regs[2]);
    }
    if((strtolower($regs[1])) == (strtolower($name))) {
     fclose($fh) or die($php_errormsg);
     if(is_file($regs[2])) {
      return read_ruleset_default($regs[2]);
     } else {
      return $regs[2];
     }
    }
   }
  }
  fclose($fh) or die($php_errormsg);
  die("Cannot find configuration value: $name in $msconfig\n");
 }
}

function get_conf_truefalse($name) {
 if(DISTRIBUTED_SETUP) { return true; }
 // Translate input if using LDAP on MSEE
 if(MSEE) {
  $name = translate_etoi($name);
  return ldap_get_conf_truefalse($name);
 } else {
  $msconfig = MS_CONFIG_DIR."MailScanner.conf";
  $fh = fopen($msconfig,'r')
   or die("Cannot open MailScanner configuration file");
  while(!feof($fh)) {
   $line = rtrim(fgets($fh, filesize($msconfig)));
   if(preg_match('/^([^#].+)\s=\s([^#].+)/',$line,$regs)) {
    ##### AJOS1 CHANGE #####
    #ORIG# $regs[1] = ereg_replace(' *', '', $regs[1]);
    ##### AJOS1 CHANGE #####
    $regs[1] = preg_replace('/ */', '', $regs[1]);
    # Strip trailing comments
    $regs[2] = preg_replace("/#.*$/","",$regs[2]);
    # store %var% variables
    if(preg_match("/%.+%/",$regs[1])) {
     $var[$regs[1]] = $regs[2];
    }
    # expand %var% variables
    if(preg_match("/(%.+%)/",$regs[2],$match)) {
     $regs[2] = preg_replace("/%.+%/",$var[$match[1]],$regs[2]);
    }
    if((strtolower($regs[1])) == (strtolower($name))) {
     fclose($fh) or die($php_errormsg);
     // Is it a ruleset?
     if(is_readable($regs[2])) {
      $regs[2] = get_default_ruleset_value($regs[2]);
     }
     $regs[2] = strtolower($regs[2]);
     switch($regs[2]) {
      case "yes":
       return true;
       break;
      case "1":
       return true;
       break;
      case "no":
       return false;
       break;
      case "0":
       return false;
       break;
      default:
       return false;
       break;
     }
    }
   }
  }
  fclose($fh) or die($php_errormsg);
  return false;
 }
}

function get_primary_scanner() {
 // Might be more than one scanner defined - pick the first as the primary
 $scanners = explode(" ",get_conf_var('VirusScanners'));
 return $scanners[0];
}

function translateQuarantineDate($date, $format='dmy') {
 $y = substr($date,0,4);
 $m = substr($date,4,2);
 $d = substr($date,6,2);

 $format = strtolower($format);

 switch($format) {
  case 'dmy':
   return "$d/$m/$y";
   break;
  case 'sql':
   return "$y-$m-$d";
   break;
  default:
   ##### AJOS1 CHANGE #####
   #ORIG# $format = ereg_replace("%y",$y,$format);
   #ORIG# $format = ereg_replace("%m",$m,$format);
   #ORIG# $format = ereg_replace("%d",$d,$format);
   ##### AJOS1 CHANGE #####
   $format = preg_replace("/%y/",$y,$format);
   $format = preg_replace("/%m/",$m,$format);
   $format = preg_replace("/%d/",$d,$format);
   return $format;
   break;
 }
}

function subtract_get_vars($preserve) {
 if(is_array($_GET)) {
  foreach($_GET as $k=>$v) {
   if(strtolower($k) !== strtolower($preserve)) {
    $output[] = "$k=$v";
   }
  }
  if(is_array($output)) {
   $output = join('&',$output);
   return '&'.$output;
  } else {
   return false;
  }
 } else {
  return false;
 }
}


function subtract_multi_get_vars($preserve) {
 if(is_array($_GET)) {
  foreach($_GET as $k=>$v) {
   if(!in_array($k,$preserve)) {
    $output[] = "$k=$v";
   }
  }
  if(is_array($output)) {
   $output = join('&amp;',$output);
   return '&amp;'.$output;
  } else {
   return false;
  }
 } else {
  return false;
 }
}

function db_colorised_table($sql, $table_heading=false, $pager=false, $order=false, $operations=false) {
 require_once('Mail/mimeDecode.php');

 // Ordering
 $orderby = $_GET['orderby'];
 switch(strtoupper($_GET['orderdir'])) {
  case 'A':
   $orderdir='ASC';
   break;
  case 'D':
   $orderdir='DESC';
   break;
 }
 if(!empty($orderby)) {
  if(($p = stristr($sql,'ORDER BY')) !== false) {
   // We already have an existing ORDER BY clause
   $p = "ORDER BY\n  ".$orderby.' '.$orderdir.','.substr($p,(strlen('ORDER BY')+2));
   $p = substr($sql,0,strpos($sql,'ORDER BY')).$p;
   $sql = $p;
  } else {
   // No existing ORDER BY - disable feature
   $order=false;
  }
 }

 if($pager) {
  require_once('DB/Pager.php');
  if(isset($_GET['offset'])) {
   $from = intval($_GET['offset']);
  } else {
   $from = 0;
  }

  // Remove any ORDER BY clauses as this will slow the count considerably
  if($pos=strpos($sql,"ORDER BY")) {
   $sqlcount = substr($sql,0,$pos);
  }

  // Count the number of rows that would be returned by the query
  $sqlcount = "SELECT COUNT(*) ".strstr($sqlcount,"FROM");
  $rows = mysql_result(dbquery($sqlcount),0);

  // Build the pager data
  $data = DB_Pager::getData($from, MAX_RESULTS, $rows, 20);

  if($rows>0) {
   if($data['numpages']>1) {
?>
 <table border="0" cellpadding="1" cellspacing="1" class="mail">
  <tr>
   <th colspan=5>Displaying page <?php echo $data['current']; ?> of <?php echo $data['numpages']; ?> - Records <?php echo $data['from']; ?> to <?php echo $data['to']; ?> of <?php echo $data['numrows']; ?></th>
  </tr>
  <tr>
   <!-- First page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><<</a>\n",$_SERVER['PHP_SELF'],0,subtract_get_vars('offset'));
    } else {
     printf("<<\n");
    }
    ?>
   </td>
   <!-- Previous page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><</a>\n",$_SERVER['PHP_SELF'],$data['prev'],subtract_get_vars('offset'));
    } else {
     printf("<\n");
    }
    ?>
   </td>
   <!-- Direct links to page (Google-style) -->
   <td align="center">
   <?php
    // Links to each page
    foreach($data['pages'] as $page=>$start) {
     if($data['current'] == $page) {
      printf("%s&nbsp;\n",$page);
     } else {
      printf("<a href=\"%s?offset=%d%s\">%s</a>&nbsp;\n",$_SERVER['PHP_SELF'],$start,subtract_get_vars('offset'),$page);
     }
    }
   ?>
   </td>
   <!-- Next Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">></a>\n",$_SERVER['PHP_SELF'],$data['next'],subtract_get_vars('offset'));
    } else {
     printf(">\n");
    }
    ?>
   </td>
   <!-- Last Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">>></a>\n",$_SERVER['PHP_SELF'],($data['numpages']-1)*$data['limit'],subtract_get_vars('offset'));
    } else {
     printf(">>\n");
    }
    ?>
  </tr>
 </table>
 </td>
</tr>
<tr>
 <td colspan="4">
<?php
   }
   // Re-run the original query and limit the rows
   $sql .= " LIMIT $from,".MAX_RESULTS;
   $sth = dbquery($sql);
   $rows = mysql_num_rows($sth);
   $fields = mysql_num_fields($sth);
   // Account for extra operations column
   if ($operations != false) {
    $fields++;
   }
  }
 } else {
   $sth = dbquery($sql);
   $rows = mysql_num_rows($sth);
   $fields = mysql_num_fields($sth);
   // Account for extra operations column
   if ($operations != false) {
    $fields++;
   }
 }

 if($rows>0) {
  if ($operations != false) {
   // Start form for operations
   echo "<FORM NAME=\"operations\" ACTION=\"do_message_ops.php\" method=POST>\n";
  }
  echo "<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\" class=\"mail\">\n";
  // Work out which columns to display
  for($f=0; $f<$fields; $f++) {
   if ($f == 0 and $operations != false) {
    // Set up display for operations form elements
    $display[$f] = true;
    $orderable[$f] = false;
    // Set it up not to wrap - tricky way to leach onto the align field
    $align[$f] = "CENTER\" style=\"white-space:nowrap";
    $fieldname[$f] = "Ops<br><a href=\"javascript:SetRadios('S')\">S</a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:SetRadios('H')\">H</a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:SetRadios('F')\">F</a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:SetRadios('R')\">R</a>";
    continue;
   }
   $display[$f] = true;
   $orderable[$f] = true;
   $align[$f] = false;
   // Set up the mysql column to account for operations
   if ($operations != false) {
    $colnum = $f-1;
   } else {
    $colnum = $f;
   }
   switch($fieldname[$f]=mysql_field_name($sth,$colnum)) {
    case 'host':
     $fieldname[$f] = "Host";
     if(DISTRIBUTED_SETUP) {
      $display[$f] = true;
     } else {
      $display[$f] = false;
     }
     break;
    case 'timestamp':
     $fieldname[$f] = "Date/Time";
     $align[$f] = "CENTER";
     break;
    case 'datetime':
     $fieldname[$f] = "Date/Time";
     $align[$f] = "CENTER";
     break;
    case 'id':
     $fieldname[$f] = "ID";
     $orderable[$f] = false;
     $align[$f] = "CENTER";
     break;
    case 'id2':
     $fieldname[$f] = "#";
     $orderable[$f] = false;
     $align[$f] = "CENTER";
     break;
    case 'size':
     $fieldname[$f] = "Size";
     $align[$f] = "RIGHT";
     break;
    case 'from_address':
     $fieldname[$f] = "From";
     break;
    case 'to_address':
     $fieldname[$f] = "To";
     break;
    case 'subject':
     $fieldname[$f] = "Subject";
     break;
    case 'clientip':
     $display[$f] = true;
     break;
    case 'archive':
     $display[$f] = false;
     break;
    case 'isspam':
     $display[$f] = false;
     break;
    case 'ishighspam':
     $display[$f] = false;
     break;
    case 'issaspam':
     $display[$f] = false;
     break;
    case 'isrblspam':
     $display[$f] = false;
     break;
    case 'spamwhitelisted':
     $display[$f] = false;
     break;
    case 'spamblacklisted':
     $display[$f] = false;
     break;
    case 'spamreport':
     $display[$f] = false;
     break;
    case 'virusinfected':
     $display[$f] = false;
     break;
    case 'nameinfected':
     $display[$f] = false;
     break;
    case 'otherinfected':
     $display[$f] = false;
     break;
    case 'report':
     $display[$f] = false;
     break;
    case 'ismcp':
     $display[$f] = false;
     break;
    case 'ishighmcp':
     $display[$f] = false;
     break;
    case 'issamcp':
     $display[$f] = false;
     break;
    case 'mcpwhitelisted':
     $display[$f] = false;
     break;
    case 'mcpblacklisted':
     $display[$f] = false;
     break;
    case 'mcpreport':
     $display[$f] = false;
     break;
    case 'hostname':
     $fieldname[$f] = 'Host';
     $display[$f] = true;
     break;
    case 'date':
     $fieldname[$f] = 'Date';
     break;
    case 'time':
     $fieldname[$f] = 'Time';
     break;
    case 'headers':
     $display[$f] = false;
     break;
    case 'sascore':
     if(get_conf_truefalse('UseSpamAssassin')) {
      $fieldname[$f] = "SA Score";
      $align[$f] = "RIGHT";
     } else {
      $display[$f] = false;
     }
     break;
    case 'mcpsascore':
     if(get_conf_truefalse('MCPChecks')) {
      $fieldname[$f] = "MCP Score";
      $align[$f] = "RIGHT";
     } else {
      $display[$f] = false;
     }
     break;
    case 'status':
     $fieldname[$f] = "Status";
     $orderable[$f] = false;
     break;
    case 'message':
     $fieldname[$f] = "Message";
     break;
    case 'attempts':
     $fieldname[$f] = "Tries";
     $align[$f] = "RIGHT";
     break;
    case 'lastattempt':
     $fieldname[$f] = "Last";
     $align[$f] = "RIGHT";
     break;
   }
  }
  // Table heading
  if(isset($table_heading) && $table_heading != "") {
   // Work out how many columns are going to be displayed
   $column_headings=0;
   for($f=0; $f<$fields; $f++) {
    if($display[$f]) {
     $column_headings++;
    }
   }
   echo " <tr>\n";
   echo "  <th colspan=$column_headings>$table_heading</th>\n";
   echo " </tr>\n";
  }
  // Column headings
  echo "<tr>\n";
  for($f=0; $f<$fields; $f++) {
   if($display[$f]) {
    if($order && $orderable[$f]) {
     // Set up the mysql column to account for operations
     if ($operations != false) {
      $colnum = $f-1;
     } else {
      $colnum = $f;
     }
     echo "  <th>\n";
     echo "  $fieldname[$f] (<a href=\"?orderby=".mysql_field_name($sth,$colnum)."&amp;orderdir=a".subtract_multi_get_vars(array('orderby','orderdir'))."\">A</a>/<a href=\"?orderby=".mysql_field_name($sth,$colnum)."&amp;orderdir=d".subtract_multi_get_vars(array('orderby','orderdir'))."\">D</a>)\n";
     echo "  </th>\n";
    } else {
     echo "  <th>".$fieldname[$f]."</th>\n";
    }
   }
  }
  echo " </tr>\n";
  // Rows
  for($r=0; $r<$rows; $r++) {
   $row = mysql_fetch_row($sth);
   if ($operations != false) {
    // Prepend operations elements - later on, replace REPLACEME w/ message id
    array_unshift($row, "<INPUT NAME=\"OPT-REPLACEME\" TYPE=RADIO VALUE=\"S\">&nbsp;<INPUT NAME=\"OPT-REPLACEME\" TYPE=RADIO VALUE=\"H\">&nbsp;<INPUT NAME=\"OPT-REPLACEME\" TYPE=RADIO VALUE=\"F\">&nbsp;<INPUT NAME=\"OPT-REPLACEME\" TYPE=RADIO VALUE=\"R\">");
	}
   // Work out field colourings and mofidy the incoming data as necessary
   // and populate the generate an overall 'status' for the mail.
   $status_array = array();
   $infected = false;
   $highspam = false;
   $spam = false;
   $whitelisted = false;
   $blacklisted = false;
   $mcp = false;
   $highmcp = false;
   for($f=0; $f<$fields; $f++) {
    if ($operations != false) {
     if ($f == 0) {
      // Skip the first field if it is operations
      continue;
     }
     $field = mysql_field_name($sth, $f-1);
    } else {
     $field = mysql_field_name($sth, $f);
    }
    switch($field) {
     case 'id':
      // Store the id for later use
      $id=$row[$f];
      // Create a link to detail.php
      $row[$f] = "<a href=\"detail.php?id=$row[$f]\">$row[$f]</a>";
      break;
     case 'id2':
      // Store the id for later use
      $id=$row[$f];
      // Create a link to detail.php as [<link>]
      $row[$f] = "[<a href=\"detail.php?id=$row[$f]\">&nbsp;&nbsp;</a>]";
      break;
     case 'from_address':
      $row[$f] = htmlentities($row[$f]);
      if(FROMTO_MAXLEN>0) {
       $row[$f] = trim_output($row[$f], FROMTO_MAXLEN);
      }
      break;
     case 'to_address':
      $row[$f] = htmlentities($row[$f]);
      if(FROMTO_MAXLEN>0) {
       // Trim each address to specified size
       $to_temp = explode(",",$row[$f]);
       for($t=0;$t<count($to_temp);$t++) {
        $to_temp[$t] = trim_output($to_temp[$t], FROMTO_MAXLEN);
       }
       // Return the data
       $row[$f] = implode(",",$to_temp);
      }
      // Put each address on a new line
      $row[$f] = str_replace(",","<br>",$row[$f]);
      break;
     case 'subject':
      $row[$f] = decode_header($row[$f]);
      $row[$f] = htmlspecialchars($row[$f]);
      if(SUBJECT_MAXLEN>0) {
       $row[$f] = trim_output($row[$f], SUBJECT_MAXLEN);
      }
      break;
     case 'isspam':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $spam = true;
       array_push($status_array,'Spam');
      }
      break;
     case 'ishighspam':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $highspam = true;
      }
      break;
     case 'ismcp':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $mcp = true;
       array_push($status_array,'MCP');
      }
      break;
     case 'ishighmcp':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $highmcp = true;
      }
      break;
     case 'virusinfected':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $infected = true;
       array_push($status_array,'Virus');
      }
      break;
     case 'report':
      // IMPORTANT NOTE: for this to work correctly the 'report' field MUST
      // appear after the 'virusinfected' field within the SQL statement.
      if(preg_match("/VIRUS_REGEX/",$row[$f],$virus)) {
       foreach($status_array as $k=>$v) {
        if($v = preg_replace('/Virus/',"Virus (".return_virus_link($virus[2]).")",$v)) {
         $status_array[$k] = $v;
        }
       }
      }
      break;
     case 'nameinfected':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $infected = true;
       array_push($status_array,'Bad Content');
      }
      break;
     case 'otherinfected':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $infected = true;
       array_push($status_array,'Other');
      }
      break;
     case 'size':
      $row[$f] = format_mail_size($row[$f]);
      break;
     case 'spamwhitelisted':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $whitelisted = true;
       array_push($status_array,'W/L');
      }
      break;
     case 'spamblacklisted':
      if($row[$f] == 'Y' || $row[$f] > 0) {
       $blacklisted = true;
       array_push($status_array,'B/L');
      }
      break;
     case 'clienthost':
      $hostname = gethostbyaddr($row[$f]);
      if($hostname == $row[$f]) {
       $row[$f] = "(Hostname lookup failed)";
      } else {
       $row[$f] = $hostname;
      }
      break;
     case 'status':
      // NOTE: this should always be the last row for it to be displayed correctly
      // Work out status
      if(count($status_array) == 0) {
       $status = "Clean";
      } else {
       $status = join("<br>",$status_array);
      }
      $row[$f] = $status;
      break;
    }
   }
   // Now add the id to the operations form elements
   if ($operations != false) {
    $row[0] = str_replace("REPLACEME", $id, $row[0]);
    $JsFunc .= "  document.operations.elements[\"OPT-$id\"][val].checked = true;\n";
   }
   // Colorise the row
   switch(true) {
    case $infected:
     echo "<tr class=\"infected\">\n";
     break;
    case $whitelisted:
     echo "<tr class=\"whitelisted\">\n";
     break;
    case $blacklisted:
     echo "<tr class=\"blacklisted\">\n";
     break;
    case $highspam:
     echo "<tr class=\"highspam\">\n";
     break;
    case $spam:
     echo "<tr class=\"spam\">\n";
     break;
    case $highmcp:
     echo "<tr class=\"highmcp\">\n";
     break;
    case $mcp:
     echo "<tr class=\"mcp\">\n";
     break;
    default:
     echo "<tr>\n";
     break;
   }
   // Display the rows
   for($f=0; $f<$fields; $f++) {
    if($display[$f]) {
     if($align[$f]) {
      echo " <td align=\"".$align[$f]."\">".$row[$f]."</td>\n";
     } else {
      echo " <td>".$row[$f]."</td>\n";
     }
    }
   }
   echo " </tr>\n";
  }
  echo "</table>\n";
  // Javascript function to clear radio buttons
  if ($operations != false) {
   echo "<SCRIPT LANGUAGE=\"JavaScript\" type=\"text/javascript\">\n";
   echo "function ClearRadios() {\n";
   echo " e=document.operations.elements\n";
   echo " for(i=0; i<e.length; i++) {\n";
   echo "  if (e[i].type==\"radio\") {\n";
   echo "    e[i].checked=false;\n";
   echo "  }\n";
   echo " }\n";
   echo "}\n";
   echo "function SetRadios(p) {\n";
   echo " var val;\n";
   echo " if (p == 'S') {\n";
   echo "  val = 0;\n";
   echo " } else if (p == 'H') {\n";
   echo "  val = 1;\n";
   echo " } else if (p == 'F') {\n";
   echo "  val = 2;\n";
   echo " } else if (p == 'R') {\n";
   echo "  val = 3;\n";
   echo " } else if (p == 'C') {\n";
   echo "  ClearRadios();\n";
   echo "  return;\n";
   echo " } else {\n";
   echo "  return;\n";
   echo " }\n";
   echo $JsFunc;
   echo "}\n";
   echo "</SCRIPT>\n";
   echo "&nbsp; <a href=\"javascript:SetRadios('S')\">S</a>";
   echo "&nbsp; <a href=\"javascript:SetRadios('H')\">H</a>";
   echo "&nbsp; <a href=\"javascript:SetRadios('F')\">F</a>";
   echo "&nbsp; <a href=\"javascript:SetRadios('R')\">R</a>";
   echo "&nbsp; or <a href=\"javascript:SetRadios('C')\">Clear</a> all";
   echo "<P><INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=\"Learn\">\n";
   echo "</FORM>\n";
   echo "<P><b>S</b> = Spam &nbsp; <b>H</b> = Ham &nbsp; <b>F</b> = Forget &nbsp; <b>R</b> = Release\n";
   }

  if($pager) {
?>
 <br>
 <table border=0 cellpadding=1 cellspacing=1 class="mail">
  <tr>
   <th colspan=5>Displaying page <?php echo $data['current']; ?> of <?php echo $data['numpages']; ?> - Records <?php echo $data['from']; ?> to <?php echo $data['to']; ?> of <?php echo $data['numrows']; ?></th>
  </tr>
  <tr>
   <!-- First page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><<</a>\n",$_SERVER['PHP_SELF'],0,subtract_get_vars('offset'));
    } else {
     printf("<<\n");
    }
    ?>
   </td>
   <!-- Previous page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><</a>\n",$_SERVER['PHP_SELF'],$data['prev'],subtract_get_vars('offset'));
    } else {
     printf("<\n");
    }
    ?>
   </td>
   <!-- Direct links to page (Google-style) -->
   <td align="center">
   <?php
    // Links to each page
    foreach($data['pages'] as $page=>$start) {
     if($data['current'] == $page) {
      printf("%s&nbsp;\n",$page);
     } else {
      printf("<a href=\"%s?offset=%d%s\">%s</a>&nbsp;\n",$_SERVER['PHP_SELF'],$start,subtract_get_vars('offset'),$page);
     }
    }
   ?>
   </td>
   <!-- Next Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">></a>\n",$_SERVER['PHP_SELF'],$data['next'],subtract_get_vars('offset'));
    } else {
     printf(">\n");
    }
    ?>
   </td>
   <!-- Last Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">>></a>\n",$_SERVER['PHP_SELF'],($data['numpages']-1)*$data['limit'],subtract_get_vars('offset'));
    } else {
     printf(">>\n");
    }
    ?>
  </tr>
 </table>
 </td>
</tr>
<tr>
 <td colspan=4>

<?php
}

} else {
  echo "No rows retrieved.\n";
 }
}

// Function to display data as a table
function dbtable($sql,$title=false,$pager=false) {
 
 // Query the data
 $sth = dbquery($sql);
 
 // Count the number of rows in a table
 $rows = mysql_num_rows($sth);
 
 // Count the nubmer of fields
 $fields = mysql_num_fields($sth);

  // Turn on paging of for the database
  if($pager) {
  require_once('DB/Pager.php');
  if(isset($_GET['offset'])) {
   $from = intval($_GET['offset']);
  } else {
   $from = 0;
  }

  // Remove any ORDER BY clauses as this will slow the count considerably
  if($pos=strpos($sql,"ORDER BY")) {
   $sqlcount = substr($sql,0,$pos);
  }

  // Count the number of rows that would be returned by the query
  $sqlcount = "SELECT COUNT(*) ".strstr($sqlcount,"FROM");
  $rows = mysql_result(dbquery($sqlcount),0);

  // Build the pager data
  $data = DB_Pager::getData($from, MAX_RESULTS, $rows, 20);

  if($rows>0) {
   if($data['numpages']>1) {
?>
 <table border=0 cellpadding=1 cellspacing=1 class="mail">
  <tr>
   <th colspan=5>Displaying page <?php echo $data['current']; ?> of <?php echo $data['numpages']; ?> - Records <?php echo $data['from']; ?> to <?php echo $data['to']; ?> of <?php echo $data['numrows']; ?></th>
  </tr>
  <tr>
   <!-- First page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><<</a>\n",$_SERVER['PHP_SELF'],0,subtract_get_vars('offset'));
    } else {
     printf("<<\n");
    }
    ?>
   </td>
   <!-- Previous page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><</a>\n",$_SERVER['PHP_SELF'],$data['prev'],subtract_get_vars('offset'));
    } else {
     printf("<\n");
    }
    ?>
   </td>
   <!-- Direct links to page (Google-style) -->
   <td align="center">
   <?php
    // Links to each page
    foreach($data['pages'] as $page=>$start) {
     if($data['current'] == $page) {
      printf("%s&nbsp;\n",$page);
     } else {
      printf("<a href=\"%s?offset=%d%s\">%s</a>&nbsp;\n",$_SERVER['PHP_SELF'],$start,subtract_get_vars('offset'),$page);
     }
    }
   ?>
   </td>
   <!-- Next Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">></a>\n",$_SERVER['PHP_SELF'],$data['next'],subtract_get_vars('offset'));
    } else {
     printf(">\n");
    }
    ?>
   </td>
   <!-- Last Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">>></a>\n",$_SERVER['PHP_SELF'],($data['numpages']-1)*$data['limit'],subtract_get_vars('offset'));
    } else {
     printf(">>\n");
    }
    ?>
  </tr>
 </table>
 </td>
</tr>
<tr>
 <td colspan=4>
<?php
   }
   // Re-run the original query and limit the rows
   $sql .= " LIMIT $from,".MAX_RESULTS;
   $sth = dbquery($sql);
   $rows = mysql_num_rows($sth);
   $fields = mysql_num_fields($sth);
   // Account for extra operations column
   if ($operations != false) {
    $fields++;
   }
  }
 } else {
   $sth = dbquery($sql);
   $rows = mysql_num_rows($sth);
   $fields = mysql_num_fields($sth);
   // Account for extra operations column
   if ($operations != false) {
    $fields++;
   }
 }

 if($rows>0) {
  echo "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=\"100%\" CLASS=\"mail\">\n";
  if($title) {
   echo "<tr><TH COLSPAN=$fields>$title</TH></tr>\n";
  }
  // Column headings
  echo " <tr>\n";
  for($f=0;$f<$fields;$f++) {
   echo "  <TH>".mysql_field_name($sth,$f)."</TH>\n";
  }
  echo " </tr>\n";
  // Rows
  while($row=mysql_fetch_row($sth)) {
   echo " <TR>\n";
   for($f=0;$f<$fields;$f++) {
    echo "  <TD>".$row[$f]."</TD>\n";
   }
   echo " </TR>\n";
  }
  echo "</TABLE>\n";
 } else {
  echo "No rows retrieved!\n";
 }
	if($pager){	
 ?>
  <br>
 <table border=0 cellpadding=1 cellspacing=1 class="mail">
  <tr>
   <th colspan=5>Displaying page <?php echo $data['current']; ?> of <?php echo $data['numpages']; ?> - Records <?php echo $data['from']; ?> to <?php echo $data['to']; ?> of <?php echo $data['numrows']; ?></th>
  </tr>
  <tr>
   <!-- First page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><<</a>\n",$_SERVER['PHP_SELF'],0,subtract_get_vars('offset'));
    } else {
     printf("<<\n");
    }
    ?>
   </td>
   <!-- Previous page -->
   <td>
    <?php
    if(isset($data['prev'])) {
     printf("<a href=\"%s?offset=%d%s\"><</a>\n",$_SERVER['PHP_SELF'],$data['prev'],subtract_get_vars('offset'));
    } else {
     printf("<\n");
    }
    ?>
   </td>
   <!-- Direct links to page (Google-style) -->
   <td align="center">
   <?php
    // Links to each page
    foreach($data['pages'] as $page=>$start) {
     if($data['current'] == $page) {
      printf("%s&nbsp;\n",$page);
     } else {
      printf("<a href=\"%s?offset=%d%s\">%s</a>&nbsp;\n",$_SERVER['PHP_SELF'],$start,subtract_get_vars('offset'),$page);
     }
    }
   ?>
   </td>
   <!-- Next Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">></a>\n",$_SERVER['PHP_SELF'],$data['next'],subtract_get_vars('offset'));
    } else {
     printf(">\n");
    }
    ?>
   </td>
   <!-- Last Page -->
   <td align="right">
    <?php
    if(isset($data['next'])) {
     printf("<a href=\"%s?offset=%d%s\">>></a>\n",$_SERVER['PHP_SELF'],($data['numpages']-1)*$data['limit'],subtract_get_vars('offset'));
    } else {
     printf(">>\n");
    }
    ?>
  </tr>
 </table>
 </td>
</tr>
<tr>
 <td colspan=4>
 
 <?php
 }
 
 
}

function db_vertical_table($sql) {
 $sth = dbquery($sql);
 $rows = mysql_num_rows($sth);
 $fields = mysql_num_fields($sth);

 if($rows>0) {
  echo "<TABLE BORDER=1 CLASS=\"mail\">\n";
  while($row=mysql_fetch_row($sth)) {
   for($f=0; $f<$fields; $f++) {
    echo " <TR>\n";
    echo "  <TD>".mysql_field_name($sth, $f)."</TD>\n";
    echo "  <TD>".$row[$f]."</TD>\n";
    echo " </TR>\n";
   }
  }
  echo "</TABLE>\n";
 } else {
  echo "No rows retrieved\n";
 }
}

function array_table($array, $keyed=false) {
 return false;
}

function get_microtime() {
 $mtime = explode(" ",microtime());
 $mtime = $mtime[1] + $mtime[0];
 return $mtime;
}

function page_creation_timer() {
 if(!isset($GLOBALS[pc_start_time])) {
  $GLOBALS[pc_start_time] = get_microtime();
 } else {
  $pc_end_time = get_microtime();
  $pc_total_time = $pc_end_time - $GLOBALS[pc_start_time];
  printf("Page generated in %f seconds\n", $pc_total_time);
 }
}

function debug($text) {
 if(DEBUG && headers_sent()) {
  echo "<!-- DEBUG: $text -->\n";
 }
}

function return_24_hour_array() {
 for($h=0; $h<24; $h++) {
  if(strlen($h) < 2) {
   $h = "0".$h;
  }
  $array[$h] = 0;
 }
 return $array;
}

function return_60_minute_array() {
 for($m=0; $m<60; $m++) {
  if(strlen($m) < 2) {
   $m = "0".$m;
  }
  $array[$m] = 0;
 }
 return $array;
}

function return_time_array() {
 for($h=0; $h<24; $h++) {
  if(strlen($h) < 2) {
   $h = "0".$h;
  }
  for($m=0; $m<60; $m++) {
   if(strlen($m) < 2) {
    $m = "0".$m;
   }
   $array[$h][$m] = 0;
  }
 }
 return $array;
}

function count_files_in_dir($dir) {
 if(!$drh = @opendir($dir)) {
  return false;
 } else {
  while(false!==($file=readdir($drh))) {
   if($file !== "." && $file !== "..") {
    $array[] = $file;
   }
  }
 }
 return count($array);
}


function get_mail_relays($message_headers) {
 $headers = explode("\\n",$message_headers);
 foreach($headers as $header) {
$header=preg_replace('/IPv6\:/','', $header);
	if(preg_match_all('/\[([\dabcdef.:]+)\]/',$header,$regs)){
   foreach($regs[1] as $relay){
    $relays[$relay]++;
   }
  }
 }
 if (is_array($relays)) {
  return array_keys($relays);
 }
}

function address_filter_sql($addresses, $type) {
 switch($type) {
  case 'A': // Administrator - show everything
   return "1=1";
   break;
  case 'U': // User - show only specific addresses
   $count=0;
   foreach($addresses as $address) {
    if((defined('FILTER_TO_ONLY') & FILTER_TO_ONLY)) {
     $sqladdr[$count] = "to_address='$address'";
    } else {
     $sqladdr[$count] = "to_address='$address' OR from_address='$address'";
    }
    $count++;
   }
   $sqladdr = join(' OR ',$sqladdr);
   return '('.$sqladdr.')';
   break;
  case 'D':  // Domain administrator
   // From Domain
   $count=0;
   foreach($addresses as $domain) {
    if((defined('FILTER_TO_ONLY') & FILTER_TO_ONLY)) {
     $sqladdr[$count] = "to_domain='$domain'";
    } else {
     $sqladdr[$count] = "to_domain='$domain' OR from_domain='$domain'";
    }
    $count++;
   }
   // Join together to form a suitable SQL WHERE clause
   $sqladdr = join(' OR ',$sqladdr);
   return '('.$sqladdr.')';
   break;
  case 'H': // Host
   $count=0;
   foreach($addresses as $hostname) {
    $sqladdr[$count] = "hostname='$hostname'";
    $count++;
   }
   $sqladdr = join(' OR ',$host);
   return '('.$sqladdr.')';
   break;
 }
}

function ldap_get_conf_var($entry) {
 // Translate MailScanner.conf vars to internal
 $entry = translate_etoi($entry);

 $lh = @ldap_connect(LDAP_HOST, LDAP_PORT)
  or die("Error: could not connect to LDAP directory on: ".LDAP_HOST."\n");

 @ldap_bind($lh)
  or die("Error: unable to bind to LDAP directory\n");

 # As per MailScanner Config.pm
 $filter = "(objectClass=mailscannerconfmain)";
 $filter = "(&$filter(mailScannerConfBranch=main))";

 $sh = ldap_search($lh, LDAP_DN, $filter, array($entry));

 $info = ldap_get_entries($lh, $sh);
 if($info['count']>0 && $info[0]['count'] <> 0) {
  if($info[0]['count'] == 0) {
   // Return single value
   return $info[0][$info[0][0]][0];
  } else {
   // Multi-value option, build array and return as space delimited
   for($n=0; $n<$info[0][$info[0][0]]['count']; $n++) {
    $return[] = $info[0][$info[0][0]][$n];
   }
   return join(" ",$return);
  }
 } else {
  // No results
  die("Error: cannot find configuration value '$entry' in LDAP directory.\n");
 }
}

function ldap_get_conf_truefalse($entry) {
 // Translate MailScanner.conf vars to internal
 $entry = translate_etoi($entry);

 $lh = @ldap_connect(LDAP_HOST, LDAP_PORT)
  or die("Error: could not connect to LDAP directory on: ".LDAP_HOST."\n");

 @ldap_bind($lh)
  or die("Error: unable to bind to LDAP directory\n");

 # As per MailScanner Config.pm
 $filter = "(objectClass=mailscannerconfmain)";
 $filter = "(&$filter(mailScannerConfBranch=main))";

 $sh = ldap_search($lh, LDAP_DN, $filter, array($entry));

 $info = ldap_get_entries($lh, $sh);
 debug(debug_print_r($info));
 if($info['count']>0) {
  debug("Entry: ".debug_print_r($info[0][$info[0][0]][0]));
  switch($info[0][$info[0][0]][0]) {
    case 'yes':
     return true;
     break;
    case '1':
     return true;
     break;
    case 'no':
     return false;
     break;
    case '0':
     return false;
     break;
    default:
     return false;
     break;
  }
 } else {
  // No results
  //die("Error: cannot find configuration value '$entry' in LDAP directory.\n");
  return false;
 }
}

function translate_etoi($name) {
 $name = strtolower($name);
 $file = MS_LIB_DIR.'MailScanner/ConfigDefs.pl';
 $fh = fopen($file,'r')
  or die("Cannot open MailScanner ConfigDefs file: $file\n");
 while(!feof($fh)) {
  $line = rtrim(fgets($fh, filesize($file)));
  if(preg_match('/^([^#].+)\s=\s([^#].+)/i',$line,$regs)) {
   // Lowercase all values
   $regs[1] = strtolower($regs[1]);
   $regs[2] = strtolower($regs[2]);
   $etoi[rtrim($regs[2])] = rtrim($regs[1]);
  }
 }
 fclose($fh) or die($php_errormsg);
 if(isset($etoi["$name"])) {
  return $etoi["$name"];
 } else {
  return $name;
 }
}

function decode_header($input) {
 // Remove white space between encoded-words
 $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);
 // For each encoded-word...
 while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {
  $encoded  = $matches[1];
  $charset  = $matches[2];
  $encoding = $matches[3];
  $text     = $matches[4];
  switch (strtolower($encoding)) {
   case 'b':
    $text = base64_decode($text);
    break;
   case 'q':
    $text = str_replace('_', ' ', $text);
    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
    foreach($matches[1] as $value)
     $text = str_replace('='.$value, chr(hexdec($value)), $text);
    break;
   }
  $input = str_replace($encoded, $text, $input);
 }
 return $input;
}

function debug_print_r($input) {
 ob_start();
 print_r($input);
 $return = ob_get_contents();
 ob_end_clean();
 return $return;
}

function return_geoip_addr($ip) {
 $piece = explode(".",$ip);
 $ip1 = (16777216*$piece[0]);
 $ip2 = (65536*$piece[1]);
 $ip3 = (256*$piece[2]);
 $ip4 = ($piece[3]);
 $geoip = ($ip1+$ip2+$ip3+$ip4);
 return $geoip;
}

function return_geoip_country($ip) {
 $geoip_num = return_geoip_addr($ip);
 $sql = "
SELECT
 country
FROM
 geoip_country
USE INDEX (geoip_country_begin,geoip_country_end)
WHERE
 ($geoip_num > begin_num)
AND
 ($geoip_num < end_num)
";
 $sth = dbquery($sql);
 return(@mysql_result($sth,0));
}

if (!function_exists('file_get_contents')) {
 function file_get_contents($filename, $use_include_path = 0) {
  $file = @fopen($filename, 'rb', $use_include_path);
  if ($file) {
   if ($fsize = @filesize($filename)) {
    $data = fread($file, $fsize);
   } else {
    while (!feof($file)) {
     $data .= fread($file, 1024);
    }
   }
   fclose($file);
  }
  return $data;
 }
}

function quarantine_list($input="/") {
 $quarantinedir = get_conf_var('QuarantineDir').'/';
 switch($input) {
  case '/':
   // Return top-level directory
   $d = @opendir($quarantinedir);
   while(false !== ($f = @readdir($d))) {
    if ($f !== "." && $f !== "..") {
     $item[] = $f;
    }
   }
   if(count($item)>0) {
    // Sort in reverse chronological order
    arsort($item);
   }
   @closedir($d);
   break;
  default:
   $current_dir = $quarantinedir.$input;
   $dirs = array($current_dir, $current_dir.'/spam', $current_dir.'/nonspam', $current_dir.'/mcp');
   foreach($dirs as $dir) {
    if(is_dir($dir) && is_readable($dir)) {
     $d = @opendir($dir);
     while(false !== ($f = readdir($d))) {
      if ($f !== "." && $f !== "..") {
       $item[] = "'$f'";
      }
     }
     if(count($item)>0) {
      asort($item);
     }
     closedir($d);
    }
   }
 }
 return $item;
}

function is_local($host) {
 $host = strtolower($host);
 // Is RPC required to look-up??
 $sys_hostname = strtolower(chop(`hostname`));
 switch($host) {
  case $sys_hostname:
   return true;
   break;
  case gethostbyaddr('127.0.0.1'):
   return true;
   break;
  default:
   // Remote - RPC needed
   return false;
   break;
 }
}

function quarantine_list_items($msgid, $rpc_only=false) {
$sql = "
SELECT
  hostname,
  DATE_FORMAT(date,'%Y%m%d') AS date,
  id,
  to_address,
  CASE WHEN isspam>0 THEN 'Y' ELSE 'N' END AS isspam,
  CASE WHEN nameinfected>0 THEN 'Y' ELSE 'N' END AS nameinfected,
  CASE WHEN virusinfected>0 THEN 'Y' ELSE 'N' END AS virusinfected,
  CASE WHEN otherinfected>0 THEN 'Y' ELSE 'N' END AS otherinfected
 FROM
  maillog
 WHERE
  id = '$msgid'";
 $sth = dbquery($sql);
 $rows = mysql_num_rows($sth);
 if($rows<=0) {
  die("Message ID $msgid not found.\n");
 }
 $row = mysql_fetch_object($sth);
 if(!$rpc_only && is_local($row->hostname)) {
  $quarantinedir = get_conf_var("QuarantineDir");
  $quarantine = $quarantinedir.'/'.$row->date.'/'.$row->id;
  $spam = $quarantinedir."/".$row->date.'/spam/'.$row->id;
  $nonspam = $quarantinedir."/".$row->date.'/nonspam/'.$row->id;
  $mcp = $quarantinedir."/".$row->date.'/mcp/'.$row->id;

  $count = 0;
  // Check for non-spam first
  if(file_exists($nonspam) && is_readable($nonspam)) {
   $quarantined[$count]['id']		= $count;
   $quarantined[$count]['host']		= $row->hostname;
   $quarantined[$count]['msgid']	= $row->id;
   $quarantined[$count]['to']		= $row->to_address;
   $quarantined[$count]['file'] 	= "message";
   $quarantined[$count]['type'] 	= "message/rfc822";
   $quarantined[$count]['path'] 	= $nonspam;
   $quarantined[$count]['md5'] 	 	= md5($nonspam);
   $quarantined[$count]['dangerous'] 	= $row->virusinfected;
   $quarantined[$count]['isspam']	= $row->isspam;
   $count++;
  }
  // Check for spam
  if(file_exists($spam) && is_readable($spam)) {
   $quarantined[$count]['id']		= $count;
   $quarantined[$count]['host'] 	= $row->hostname;
   $quarantined[$count]['msgid']	= $row->id;
   $quarantined[$count]['to']		= $row->to_address;
   $quarantined[$count]['file']     	= "message";
   $quarantined[$count]['type']     	= "message/rfc822";
   $quarantined[$count]['path']     	= $spam;
   $quarantined[$count]['md5']      	= md5($spam);
   $quarantined[$count]['dangerous'] 	= $row->virusinfected;
   $quarantined[$count]['isspam']	= $row->isspam;
   $count++;
  }
  // Check for mcp
  if(file_exists($mcp) && is_readable($mcp)) {
   $quarantined[$count]['id']           = $count;
   $quarantined[$count]['host']         = $row->hostname;
   $quarantined[$count]['msgid']        = $row->id;
   $quarantined[$count]['to']           = $row->to_address;
   $quarantined[$count]['file']         = "message";
   $quarantined[$count]['type']         = "message/rfc822";
   $quarantined[$count]['path']         = $mcp;
   $quarantined[$count]['md5']          = md5($spam);
   $quarantined[$count]['dangerous']    = $row->virusinfected;
   $quarantined[$count]['isspam']       = $row->isspam;
   $count++;
  }
  // Check the main quarantine
  if(is_dir($quarantine) && is_readable($quarantine)) {
   $d = opendir($quarantine) or die("Cannot open quarantine dir: $quarantine\n");
   while(false !== ($f = readdir($d))) {
    if($f !== '..' && $f !== '.') {
     $quarantined[$count]['id']		= $count;
     $quarantined[$count]['host']	= $row->hostname;
     $quarantined[$count]['msgid']	= $row->id;
     $quarantined[$count]['to']		= $row->to_address;
     $quarantined[$count]['file'] 	= $f;
     $file				= escapeshellarg($quarantine.'/'.$f);
     $quarantined[$count]['type'] 	= ltrim(rtrim(`/usr/bin/file -bi $file`));
     $quarantined[$count]['path'] 	= $quarantine.'/'.$f;
     $quarantined[$count]['md5']  	= md5($quarantine.'/'.$f);
     $quarantined[$count]['dangerous'] 	= $row->virusinfected;
     $quarantined[$count]['isspam']	= $row->isspam;
     $count++;
    }
   }
   closedir($d);
  }
  return $quarantined;
 } else {
  // Host is remote call quarantine_list_items by RPC
  debug("Calling quarantine_list_items on $row->hostname by XML-RPC");
  //$client = new xmlrpc_client(constant('RPC_RELATIVE_PATH').'/rpcserver.php',$row->hostname,80);
  //if(DEBUG) { $client->setDebug(1); }
  //$parameters = array($input);
  //$msg = new xmlrpcmsg('quarantine_list_items',$parameters);
  $msg = new xmlrpcmsg('quarantine_list_items', array(new xmlrpcval($msgid)));
  $rsp = xmlrpc_wrapper($row->hostname,$msg); //$client->send($msg);
  if($rsp->faultcode()==0) {
   $response = php_xmlrpc_decode($rsp->value());
  } else {
   $response = "XML-RPC Error: ".$rsp->faultstring();
  }
  return $response;
 }
}

function quarantine_release($list, $num, $to, $rpc_only=false) {
 if(!is_array($list) || !isset($list[0]['msgid'])) {
  return "Invalid argument";
 } else {
  $new = quarantine_list_items($list[0]['msgid']);
  $list =& $new;
 }

 if(!$rpc_only && is_local($list[0]['host'])) {
  if(!QUARANTINE_USE_SENDMAIL) {
   // Load in the required PEAR modules
   require_once('PEAR.php');
   require_once('Mail.php');
   require_once('Mail/mime.php');
   $crlf = "\r\n";
   $hdrs = array('From' => QUARANTINE_FROM_ADDR, 'Subject' => QUARANTINE_SUBJECT, 'Date' => date("r"));
   $mime = new Mail_mime($crlf);
   $mime->setTXTBody(QUARANTINE_MSG_BODY);
   // Loop through each selected file and attach them to the mail
   foreach($num as $key=>$val) {
    // If the message is of rfc822 type then set it as Quoted printable
    if(preg_match('/message\/rfc822/',$list[$val]['type'])) {
     $mime->addAttachment($list[$val]['path'], 'message/rfc822', 'Original Message', true, '');
    } else {
     // Default is base64 encoded
     $mime->addAttachment($list[$val]['path'], $list[$val]['type'], $list[$val]['file'], true);
    }
   }
   $mail_param = array('host' => QUARANTINE_MAIL_HOST);
   $body = $mime->get();
   $hdrs = $mime->headers($hdrs);
   $mail =& Mail::factory('smtp',$mail_param);
   $m_result = $mail->send($to, $hdrs, $body);
   if(PEAR::isError($m_result)) {
    // Error
    $status = 'Release: error ('.$m_result->getMessage().')';
    global $error;
    $error = true;
   } else {
    $status = "Release: message released to ".str_replace(",",", ",$to);
    audit_log('Quarantined message ('.$list[$val]['msgid'].') released to '.$to);
   }
   return($status);
  } else {
   // Use sendmail to release message
   // We can only release message/rfc822 files in this way.
   $cmd = QUARANTINE_SENDMAIL_PATH." -i -f ".QUARANTINE_FROM_ADDR." ".escapeshellarg($to)." < ";
   foreach($num as $key=>$val) {
    if(preg_match('/message\/rfc822/',$list[$val]['type'])) {
     debug($cmd.$list[$val]['path']);
     exec($cmd.$list[$val]['path']." 2>&1", $output_array, $retval);
     if ($retval == 0) {
      $status = "Release: message released to ".str_replace(",",", ",$to);
      audit_log('Quarantined message ('.$list[$val]['msgid'].') released to '.$to);
     } else {
      $status = "Release: error code ".$retval." returned from Sendmail:\n".join("\n",$output_array);
      global $error;
      $error = true;
     }
     return($status);
    }
   }
  }
 } else {
  // Host is remote - handle by RPC
  debug("Calling quarantine_release on ".$list[0]['host']." by XML-RPC");
  //$client = new xmlrpc_client(constant('RPC_RELATIVE_PATH').'/rpcserver.php',$list[0]['host'],80);
  // Convert input parameters
  foreach($list as $list_array) {
   foreach($list_array as $key=>$val) {
    $list_struct[$key] = new xmlrpcval($val);
   }
   $list_output[] = new xmlrpcval($list_struct,'struct');
  }
  foreach($num as $key=>$val) {
   $num_output[$key] = new xmlrpcval($val);
  }
  // Build input parameters
  $param1 = new xmlrpcval($list_output,'array');
  $param2 = new xmlrpcval($num_output,'array');
  $param3 = new xmlrpcval($to,'string');
  $parameters = array($param1,$param2,$param3);
  $msg = new xmlrpcmsg('quarantine_release',$parameters);
  $rsp = xmlrpc_wrapper($list[0]['host'],$msg); //$client->send($msg);
  if($rsp->faultcode()==0) {
   $response = php_xmlrpc_decode($rsp->value());
  } else {
   $response = "XML-RPC Error: ".$rsp->faultstring();
  }
  return $response." (RPC)";
 }
}

function quarantine_learn($list, $num, $type, $rpc_only=false) {
 if(!is_array($list) || !isset($list[0]['msgid'])) {
  return "Invalid argument";
 } else {
  $new = quarantine_list_items($list[0]['msgid']);
  $list =& $new;
 }

 if(!$rpc_only && is_local($list[0]['host'])) {
  foreach($num as $key=>$val) {
   switch ($type) {
    case "ham":
     $learn_type = "ham";
     if($list[$val]['isspam'] == 'Y') {
      // Learning SPAM as HAM - this is a false-positive
      $sql = "UPDATE maillog SET timestamp=timestamp, isfp=1, isfn=0 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     } else {
      // Learning HAM as HAM - better reset the flags just in case
      $sql = "UPDATE maillog SET timestamp=timestamp, isfp=0, isfn=0 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     }
     break;
    case "spam":
     $learn_type = "spam";
     if($list[$val]['isspam'] == 'N') {
      // Learning HAM as SPAM - this is a false-negative
      $sql = "UPDATE maillog SET timestamp=timestamp, isfp=0, isfn=1 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     } else {
      // Learning SPAM as SPAM - better reset the flags just in case
      $sql = "UPDATE maillog SET timestamp=timestamp, isfp=0, isfn=0 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     }
     break;
    case "forget":
     $learn_type = "forget";
     $sql = "UPDATE maillog SET timestamp=timestamp, isfp=0, isfn=0 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     break;
    case "report":
     $use_spamassassin = true;
     $learn_type = "-r";
     $sql = "UPDATE maillog SET timestamp=timestamp, isfp=0, isfn=1 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     break;
    case "revoke":
     $use_spamassassin = true;
     $learn_type = "-k";
     $sql = "UPDATE maillog SET timestamp=timestamp, isfp=1, isfn=0 WHERE id='".mysql_escape_string($list[$val]['msgid'])."'";
     break;
   }
   if(true === $use_spamassassin) {
    // Run SpamAssassin to report or revoke spam/ham
    exec(SA_DIR.'spamassassin -p '.SA_PREFS.' '.$learn_type.' < '.$list[$val]['path'].' 2>&1', $output_array, $retval);
    if ($retval == 0) {
     // Command succeeded - update the database accordingly
     if(isset($sql)) {
      debug("Learner - running SQL: $sql");
      $junk = dbquery($sql);
     }
     $status[] = "SpamAssassin: ".join(", ",$output_array);
     switch($learn_type) {
      case "-r":
       $learn_type = "spam";
       break;
      case "-k":
       $learn_type = "ham";
       break;
     }
     audit_log('SpamAssassin was trained and reported on message '.$list[$val]['msgid'].' as '.$learn_type);
    } else {
     $status[] = "SpamAssassin: error code ".$retval." returned from SpamAssassin:\n".join("\n",$output_array);
     global $error;
     $error = true;
    }
   } else {
    // Only sa-learn required
    exec(SA_DIR.'sa-learn -p '.SA_PREFS.' --'.$learn_type.' --file '.$list[$val]['path'].' 2>&1', $output_array, $retval);
    if ($retval == 0) {
     // Command succeeded - update the database accordingly
     if(isset($sql)) {
      debug("Learner - running SQL: $sql");
      $junk = dbquery($sql);
     }
     $status[] = "SA Learn: ".join(", ",$output_array);
     audit_log('SpamAssassin was trained on message '.$list[$val]['msgid'].' as '.$learn_type);
    } else {
     $status[] = "SA Learn: error code ".$retval." returned from sa-learn:\n".join("\n",$output_array);
     global $error;
     $error = true;
    }
   }
  }
  return join("\n",$status);
 } else {
  // Call by RPC
  debug("Calling quarantine_learn on ".$list[0]['host']." by XML-RPC");
  //$client = new xmlrpc_client(constant('RPC_RELATIVE_PATH').'/rpcserver.php',$list[0]['host'],80);
  // Convert input parameters
  foreach($list as $list_array) {
   foreach($list_array as $key=>$val) {
    $list_struct[$key] = new xmlrpcval($val);
   }
   $list_output[] = new xmlrpcval($list_struct,'struct');
  }
  foreach($num as $key=>$val) {
   $num_output[$key] = new xmlrpcval($val);
  }
  // Build input parameters
  $param1 = new xmlrpcval($list_output,'array');
  $param2 = new xmlrpcval($num_output,'array');
  $param3 = new xmlrpcval($type,'string');
  $parameters = array($param1,$param2,$param3);
  $msg = new xmlrpcmsg('quarantine_learn',$parameters);
  $rsp = xmlrpc_wrapper($list[0]['host'],$msg); //$client->send($msg);
  if($rsp->faultcode()==0) {
   $response = php_xmlrpc_decode($rsp->value());
  } else {
   $response = "XML-RPC Error: ".$rsp->faultstring();
  }
  return $response." (RPC)";
 }
}

function quarantine_delete($list, $num, $rpc_only=false) {
 if(!is_array($list) || !isset($list[0]['msgid'])) {
  return "Invalid argument";
 } else {
  $new = quarantine_list_items($list[0]['msgid']);
  $list =& $new;
 }

 if(!$rpc_only && is_local($list[0]['host'])) {
  foreach($num as $key=>$val) {
   if(@unlink($list[$val]['path'])) {
    $status[] = "Delete: deleted file ".$list[$val]['path'];
    dbquery("UPDATE maillog SET quarantined=NULL WHERE id='".$list[$val]['msgid']."'");
    audit_log('Delete file from quarantine: '.$list[$val]['path']);
   } else {
    $status[] = "Delete: error deleting file ".$list[$val]['path'];
    global $error;
    $error = true;
   }
  }
  return join("\n",$status);
 } else {
  // Call by RPC
  debug("Calling quarantine_delete on ".$list[0]['host']." by XML-RPC");
  //$client = new xmlrpc_client(constant('RPC_RELATIVE_PATH').'/rpcserver.php',$list[0]['host'],80);
  // Convert input parameters
  foreach($list as $list_array) {
   foreach($list_array as $key=>$val) {
    $list_struct[$key] = new xmlrpcval($val);
   }
   $list_output[] = new xmlrpcval($list_struct,'struct');
  }
  foreach($num as $key=>$val) {
   $num_output[$key] = new xmlrpcval($val);
  }
  // Build input parameters
  $param1 = new xmlrpcval($list_output,'array');
  $param2 = new xmlrpcval($num_output,'array');
  $parameters = array($param1,$param2);
  $msg = new xmlrpcmsg('quarantine_delete',$parameters);
  $rsp = xmlrpc_wrapper($list[0]['host'],$msg); //$client->send($msg);
  if($rsp->faultcode()==0) {
   $response = php_xmlrpc_decode($rsp->value());
  } else {
   $response = "XML-RPC Error: ".$rsp->faultstring();
  }
  return $response." (RPC)";
 }
}

function audit_log($action) {
 if(AUDIT) {
  if(!MSEE) {
   $user = mysql_escape_string($_SESSION['myusername']);
   $action = mysql_escape_string($action);
   $ip = mysql_escape_string($_SERVER['REMOTE_ADDR']);
   dbquery("INSERT INTO audit_log (user,ip_address,action) VALUES ('$user','$ip','$action')");
  } else {
   // TODO: MSEE audit_logging (what session variable hold user name??)
   return false;
  }
 }
}

function mailwatch_array_sum($array) {
 if(!is_array($array)) {
  // Not an array
  return array();
 } else {
  return array_sum($array);
 }
}

function check_username_format() {
 switch($_SESSION['user_type']) {
  case 'U':
   if(strpos($_SESSION['myusername'],'@')) {
    return true;
   }
   break;
  case 'D':
   if(strpos($_SESSION['myusername'],'@')) {
    return true;
   }
   break;
  case 'A':
   return true;
   break;
  default:
   return false;
 }
 return false;
}


function read_ruleset_default($file) {
 $fh = fopen($file,'r')
  or die("Cannot open MailScanner ruleset file ($file)");
 while(!feof($fh)) {
  $line = rtrim(fgets($fh, filesize($file)));
  if(preg_match('/(\S+)\s+(\S+)\s+(\S+)/',$line,$regs)) {
   if(strtolower($regs[2]) == 'default') {
    // Check that it isn't another ruleset
    if(is_file($regs[3])) {
     return read_ruleset_default($regs[3]);
    } else {
     return $regs[3];
    }
   }
  }
 }
}

function get_virus_conf($scanner) {
 $fh = fopen(MS_CONFIG_DIR.'virus.scanners.conf','r');
 while (!feof($fh)) {
  $line = rtrim(fgets($fh,1048576));
  if(preg_match("/(^[^#]\S+)\s+(\S+)\s+(\S+)/",$line,$regs)) {
   if($regs[1] == $scanner) {
    fclose($fh);
    return $regs[2]." ".$regs[3];
   }
  }
 }
 // Not found
 fclose($fh);
 return false;
}

function return_quarantine_dates() {
 $array = array();
 for($d=0; $d<(QUARANTINE_DAYS_TO_KEEP+1); $d++) {
  $array[] = date('Ymd', mktime(0, 0, 0, date("m"), date("d")-$d, date("Y")));
 }
 return $array;
}

function return_virus_link($virus) {
 if((defined('VIRUS_INFO') & VIRUS_INFO!==false)) {
  $link = sprintf(VIRUS_INFO,$virus);
  return sprintf("<a href=\"%s\">%s</a>",$link,$virus);
 } else {
  return $virus;
 }
}

function net_match($network, $ip) {
 // Skip invalid entries
 if(long2ip(ip2long($ip)) === false) return false;
 // From PHP website
 // determines if a network in the form of 192.168.17.1/16 or
 // 127.0.0.1/255.255.255.255 or 10.0.0.1 matches a given ip
 $ip_arr = explode('/', $network);
 // Skip invalid entries
 if(long2ip(ip2long($ip_arr[0])) === false) return false;
 $network_long = ip2long($ip_arr[0]);

 $x = ip2long($ip_arr[1]);
 $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
 $ip_long = ip2long($ip);

 return ($ip_long & $mask) == ($network_long & $mask);
}


function is_rpc_client_allowed() {
 // If no server address supplied
 if(!isset($_SERVER['SERVER_ADDR']) || empty($_SERVER['SERVER_ADDR'])) {
  return true;
 }
 // Get list of allowed clients
 if(defined('RPC_ALLOWED_CLIENTS') && (!RPC_ALLOWED_CLIENTS === false)) {
  // Read in space separated list
  $clients = explode(' ',constant('RPC_ALLOWED_CLIENTS'));
  // Validate each client type
  foreach($clients as $client) {
   if($client == 'allprivate' && (net_match('10.0.0.0/8', $_SERVER['SERVER_ADDR']) || net_match('172.16.0.0/12', $_SERVER['SERVER_ADDR']) || net_match('192.168.0.0/16', $_SERVER['SERVER_ADDR']))) {
    return true;
   }
   if($client == 'local24') {
    // Get machine IP address from the hostname
    $ip = gethostbyname(chop(`hostname`));
    // Change IP address to a /24 network
    $ipsplit = explode('.',$ip);
    $ipsplit[3] = '0';
    $ip = implode('.',$ipsplit);
    if(net_match("{$ip}/24",$_SERVER['SERVER_ADDR'])) return true;
   }
   // All any others
   if(net_match($client,$_SERVER['SERVER_ADDR'])) return true;
   // Try hostname
   $iplookup = gethostbyname($client);
   if($client !== $iplookup && net_match($iplookup, $_SERVER['SERVER_ADDR'])) return true;
  }
  // If all else fails
  return false;
 } else {
  return false;
 }
}

function xmlrpc_wrapper($host,$msg) {
 $method = 'http';
 // Work out port
 if((defined('SSL_ONLY') && SSL_ONLY)) {
  $port = 443;
  $method = 'https';
 } elseif(defined('RPC_PORT')) {
  $port = RPC_PORT;
  if((defined('RPC_SSL') && RPC_SSL)) {
   $method = 'https';
   if(!defined('RPC_PORT')) $port = 443;
  }
 } else {
  $port = 80;
 }
 $client = new xmlrpc_client(constant('RPC_RELATIVE_PATH').'/rpcserver.php',$host,$port);
 if(DEBUG) $client->setDebug(1);
 $client->setSSLVerifyPeer(0);
 $client->setSSLVerifyHost(0);
 $response = $client->send($msg,0,$method);
 return $response;
}

// Clean Cache folder
function delete_dir($path) {
        $files = glob($path.'/*');
        // Life of cached images: hard set to 60 seconds
		$life  = '60';
		// File not to delete
        $notfile = "".MAILWATCH_HOME."/".CACHE_DIR."/place_holder.txt";
        foreach($files as $file) {
                if(is_dir($file) && !is_link($file)) {
                        delete_dir($file);
                }
                else {
                     if(((time() - filemtime($file) >= $life) && ($file != $notfile))){
                        unlink($file);
}
                }
        }
		// Check to see if we are in the right path
        if ( $path!= "".MAILWATCH_HOME."/".CACHE_DIR."") echo "bad path";
}


### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ###
### Last Updated: 2011/12/9
### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ###
function funcs_phpversion()
{
   return(phpversion());
}

?>
