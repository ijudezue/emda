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

html_start("GeoIP Database Update",0,false,false);

if(!isset($_POST['run'])) {

echo "<FORM METHOD=\"POST\" ACTION=\"".$_SERVER['PHP_SELF']."\">";
echo "<INPUT TYPE=\"HIDDEN\" NAME=\"run\" VALUE=\"true\">";
echo "<TABLE CLASS=\"boxtable\" WIDTH=\"100%\">";
echo "<TR>";
echo "<TD>";
echo "This utility is used to update the SQL database with up-to-date GeoIP data from <a href=\"http://www.maxmind.com/app/geoip_country\" target=\"_maxmind\">MaxMind</a> which is used to work out the country of origin for any given IP address and is displayed on the Message Detail page.<BR>";
echo "<BR>";
echo "</TD>";
echo "</TR>";
echo "<TR>";
echo "<TD ALIGN=\"CENTER\"><BR><INPUT TYPE=\"SUBMIT\" VALUE=\"Run Now\"><BR><BR></TD>";
echo "</TR>";
echo "</TABLE>";
echo "</FORM>";

} else {
 echo "Downloading file, please wait....<BR>\n";
 $file1 = './temp/GeoIPCountryCSV.zip';
 $file2 = './temp/GeoIPCountryWhois.csv';
 $file3 = './temp/GeoIPv6.csv.gz';
 $file4 = './temp/GeoIPv6.csv';
 // Clean-up from last run
 if(file_exists($file1)) { unlink($file1); }
 if(file_exists($file2)) { unlink($file2); }
 if(file_exists($file3)) { unlink($file3); }
 if(file_exists($file4)) { unlink($file4); }
 $LINKGEOIPv4="http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip";
 $LINKGEOIPv6="http://geolite.maxmind.com/download/geoip/database/GeoIPv6.csv.gz";
 $OUTDIR="./temp/";



 
 // changing to cURL rather than fopen
 if(!file_exists($file) && !file_exists($file3)) {
	if(is_writable($OUTDIR) && is_readable($OUTDIR)){ 

//////////////////////// IPv4 ///////////////////////////////////////////////////

         ////////////////////////////////////
         /// Initialize the cURL session ////
         ////////////////////////////////////
        $curl_var1 = curl_init();

         /////////////////////////////////////////////////////////
         //////  Set the URL of the page or file to download. ////
         /////////////////////////////////////////////////////////
        curl_setopt($curl_var1, CURLOPT_URL, $LINKGEOIPv4);


     // Create the file
        $fp1 = fopen($file1, "w+");

        curl_setopt($curl_var1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_var1, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl_var1, CURLOPT_FILE,$fp1);


         ////////////////////////////////////////////////////////////////
         ///// Set the timeout to allow curl to finish the download//////
         ////////////////////////////////////////////////////////////////
        curl_setopt($curl_var1, CURLOPT_TIMEOUT, 180);


/////////////////////  IPv6 /////////////////////////////////////////////////////

         ////////////////////////////////////
         /// Initialize the cURL session ////
         ////////////////////////////////////
        $curl_var2 = curl_init();

         /////////////////////////////////////////////////////////
         //////  Set the URL of the page or file to download. ////
         /////////////////////////////////////////////////////////
        curl_setopt($curl_var2, CURLOPT_URL, $LINKGEOIPv6);


     // Create the file
        $fp2 = fopen($file3, "w+");

        curl_setopt($curl_var2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_var2, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl_var2, CURLOPT_FILE,$fp2);


         ////////////////////////////////////////////////////////////////
         ///// Set the timeout to allow curl to finish the download//////
         ////////////////////////////////////////////////////////////////
        curl_setopt($curl_var2, CURLOPT_TIMEOUT, 180);





   // Download GeoIP CSV file
   if(curl_exec($curl_var1) && curl_exec($curl_var2)) {
       //Close the curl connection
       curl_close($curl_var1);
       
	   // Cluse the file connection
	   fclose($fp1);
	   
	   // Unset the file variable
       unset($fp1);
	   
	   // Close the curl connection
	   curl_close($curl_var2);
	   
	   // Cluse the file connection
       fclose($fp2);
	   
	   // Unset the file variable
       unset($fp1);
	   
       // Unzip the IPv4 file (unzip required) 
       $exec = exec('unzip -d '.$OUTDIR.' '.$file1, $output, $retval);

	   // Gunzip the IPv6 file (gunzip required)
       $exec = exec('gunzip '.$OUTDIR.' '.$file3, $output1, $retval1);

        if($retval==0) {

        // Drop the data from the table
        dbquery("DELETE FROM geoip_country");

        // Load the data
        dbquery("LOAD DATA LOCAL INFILE '".$file2."' INTO TABLE geoip_country FIELDS TERMINATED BY ',' ENCLOSED BY '\"'");

        dbquery("LOAD DATA LOCAL INFILE '".$file4."' INTO TABLE geoip_country FIELDS TERMINATED BY ',' ENCLOSED BY '\"'");

        // Done return the number of rows
        echo "Download complete ... ".mysql_result(dbquery("SELECT COUNT(*) FROM geoip_country"),0)." rows imported.<BR>\n";
        
		audit_log('Ran GeoIP update');

        } else {
		
        // If it was unable to unzip the the file display this erro
        
		die("Unzip failed:<BR>Error: ".join("<BR>",$output)."<BR>".join("<BR>",$output1)."\n");
        }
    }else{
        
		// unable to download the file correctly
        die("Unable to download GeoIP data file.\n");
    
	}
    
	}else{
    
	// unable to read or write to the directory
    
	die("Unable to read or write to the".$OUTDIR.".\n");
    
	}
    
	}else{
    
	die("Files still exist for some reason\n");
    
	}

}

  // Add the footer
 html_end();
  
  // close the connection to the Database
 dbclose();
?>
