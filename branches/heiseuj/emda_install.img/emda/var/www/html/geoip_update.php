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

//Require files
require_once('./functions.php');

// Authenication verification and keep the session alive
session_start();
require('login.function.php');

html_start("GeoIP Database Update");

if(!isset($_POST['run'])) {
?>
<FORM METHOD="POST" ACTION=<?php echo $_SERVER['PHP_SELF']; ?>>
<INPUT TYPE="HIDDEN" NAME="run" VALUE="true">
<TABLE CLASS="BOXTABLE" WIDTH="100%">
 <TR>
  <TD>
   This utility is used to update the SQL database with up-to-date GeoIP data from <a href="http://www.maxmind.com/app/geoip_country" target="_maxmind">MaxMind</a> which is used to work out the country of origin for any given IP address and is displayed on the Message Detail page.<BR>
   <BR>
  </TD>
 </TR>
 <TR>
  <TD ALIGN="CENTER"><BR><INPUT TYPE="SUBMIT" VALUE="Run Now"><BR><BR></TD>
 </TR>
<TABLE>
<?php
html_end();
} else {
 echo "Downloading file, please wait....<BR>\n";
 $file = 'temp/GeoIPCountryCSV.zip';
 $file2 = 'temp/GeoIPCountryWhois.csv';
 $base = dirname(__FILE__);
 // Clean-up from last run
 if(file_exists($file)) { unlink($file); }
 if(file_exists($file2)) { unlink($file2); }
 ###### AJOS1 CHANGE #####
 $FILECMD="/usr/bin/wget";
 $LINKGEO="http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip";
 $FILELOG="/tmp/geo_down.txt";
 $OUTDIR="./temp";
 $getfilestring = sprintf("%s --tries=5 -N -nd -nH -o %s -P %s %s", $FILECMD, $FILELOG, $OUTDIR, $LINKGEO);
 #debug# printf("Running:  %s\n", $getfilestring);
 if (! ini_get('safe_mode')) {
    if (file_exists($FILECMD)) {
       ###STILL TESTING### passthru("$getfilestring");
    }
 }
 ###### AJOS1 CHANGE #####
 if(! file_exists($file)) {
    // Download GeoIP CSV file
    if($ufh = fopen($LINKGEO,"r")) {
     // Open local file for writing
     if($lfh = fopen($file,"w+")) {
      do {
       $data = fread($ufh,8192);
       if(strlen($data)==0) {
        break;
       }
       fwrite($lfh,$data);
      } while(true);
      fclose($ufh);
      fclose($lfh);
     } else {
      die("Unable to open $file for writing.\n");
     }
   } else {
     die("Unable to download GeoIP data file.\n");
   }
 }

 // Unzip the file (unzip required)
 $exec = exec('unzip -d temp/ '.$file, $output, $retval);
 if($retval==0) {
  // Drop the data from the table
  dbquery("DELETE FROM geoip_country");
  // Load the data
  dbquery("LOAD DATA LOCAL INFILE '".$base.'/'.$file2."' INTO TABLE geoip_country FIELDS TERMINATED BY ',' ENCLOSED BY '\"'");
  // Done return the number of rows
  echo "Download complete ... ".mysql_result(dbquery("SELECT COUNT(*) FROM geoip_country"),0)." rows imported.<BR>\n";
  audit_log('Ran GeoIP update');
 } else {
  die("Unzip failed:<BR>Error: ".join("<BR>",$output)."<BR>\n");
 }
 html_end();
 dbclose();
}
?>
