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

class Filter {
 var $item;
 var $operators = array(
  '='			=>		"is equal to",
  '<>'			=>		"is not equal to",
  '>'			=>		"is greater than",
  '>='			=>		"is greater than or equal to",
  '<'			=>		"is less than",
  '<='			=>		"is less than or equal to",
  'LIKE'		=>		"contains",
  'NOT LIKE'		=>		"does not contain",
  'REGEXP'		=>		"matches the regular expression",
  'NOT REGEXP'		=>		"does not match the regular expression",
  'IS NULL'		=>		"is null",
  'IS NOT NULL'		=>		"is not null"
 );
 var $columns = array(
  'date'		=>		"Date",
  'headers'		=>		"Headers",
  'id' 			=> 		"Message ID",
  'size'		=>		"Size (bytes)",
  'from_address'	=>		"From",
  'from_domain'		=>		"From Domain",
  'to_address'		=>		"To",
  'to_domain'		=>		"To Domain",
  'subject'		=>		"Subject",
  'clientip'		=>		"Received from (IP Address)",
  'isspam'		=>		"is Spam (>0 = TRUE)",
  'ishighspam'          =>              "is High Scoring Spam (>0 = TRUE)",
  'issaspam'		=>		"is Spam according to SpamAssassin (>0 = TRUE)",
  'isrblspam'		=>		"is Listed in one or more RBL's (>0 = TRUE)",
  'spamwhitelisted'	=>		"is Whitelisted (>0 = TRUE)",
  'spamblacklisted'	=>		"is Blacklisted (>0 = TRUE)",
  'sascore'		=>		"SpamAssassin Score",
  'spamreport'		=>		"Spam Report",
  'ismcp'               =>              "is MCP (>0 = TRUE)",
  'ishighmcp'           =>              "is High Scoring MCP (>0 = TRUE)",
  'issamcp'             =>              "is MCP accoding to SpamAssassin (>0 = TRUE)",
  'mcpwhitelisted'      =>              "is MCP Whitelisted (>0 = TRUE)",
  'mcpblacklisted'      =>              "is MCP Blacklisted (>0 = TRUE)",
  'mcpscore'            =>              "MCP Score",
  'mcpreport'           =>              "MCP Report",
  'virusinfected'	=>		"contained a Virus (>0 = TRUE)",
  'nameinfected'	=>		"contained an Unacceptable Attachment (>0 = TRUE)",
  'otherinfected'	=>		"contained other infections (>0 = TRUE)",
  'report'		=>		"Virus Report",
  'hostname'		=>		"MailScanner Hostname"
 );
 var $reports = array();
 var $last_operator;
 var $last_column;
 var $last_value;
 var $display_last = 0;

 function Add($column, $operator, $value) {
  // Don't show the last column, operator, and value now
  $this->display_last = 0;

  //  Make sure this is not a duplicate
  if(count($this->item)>0) {
   foreach($this->item as $key=>$val) {
    if (($val[0] == $column) && ($val[1] == $operator) && ($val[2] == $value)) {
     return;
    }
   }
  }

  $this->item[] = array($column, $operator, $value);
 }

 function Remove($item) {
  // Store the last column, operator, and value, and force the form to default to them
  $this->last_column = $this->item[$item][0];
  $this->last_operator = $this->item[$item][1];
  $this->last_value = $this->item[$item][2];
  $this->display_last = 1;
  unset($this->item[$item]);
 }

 function Display() {
  echo "<TABLE ALIGN=\"CENTER\" WIDTH=\"600\" BORDER=\"0\" CLASS=\"boxtable\">\n";
  echo " <TR><TH COLSPAN=2>Active Filters</TH></TR>\n";
  if(count($this->item)>0) {
   foreach($this->item as $key=>$val) {
    echo "<TR><TD>".
         $this->TranslateColumn($val[0])." ".$this->TranslateOperator($val[1]).
         " '".stripslashes($val[2])."'</TD><TD ALIGN=\"RIGHT\"><A HREF=\"".$_SERVER["PHP_SELF"]."?action=remove&column=$key\">Remove</A></TD></TR>\n";
   }
  } else {
   echo "<TR><TD COLSPAN=2>None</TD></TR>\n";
  }
  
  // Add filter
  echo " <TR><TH COLSPAN=2>Add Filter</TH></TR>\n";
  echo " <TR><TD COLSPAN=2>".$this->DisplayForm()."</TD></TR>\n";
  echo " <TR><TH COLSPAN=2>Statistics (Filtered)</TH></TR>\n";
  $query = "
SELECT
 DATE_FORMAT(MIN(date),'".DATE_FORMAT."') AS oldest,
 DATE_FORMAT(MAX(date),'".DATE_FORMAT."') AS newest,
 COUNT(*) AS messages
FROM
 maillog
WHERE
 1=1 
".$this->CreateSQL();
  $sth = dbquery($query);
  while($row = mysql_fetch_object($sth)) {
   echo " <TR><TD>Oldest record:</TD><TD ALIGN=\"RIGHT\">$row->oldest</TD></TR>\n";
   echo " <TR><TD>Newest record:</TD><TD ALIGN=\"RIGHT\">$row->newest</TD></TR>\n";
   echo " <TR><TD>Message count:</TD><TD ALIGN=\"RIGHT\">".number_format($row->messages)."</TD></TR>\n";
  }
  echo "<TR><TH COLSPAN=2>Reports</TH></TR>\n";
  echo "<TR><TD COLSPAN=2><UL>\n";
  foreach($this->reports as $description=>$url) {
   echo "<LI><A HREF=\"$url\">$description</A>\n";
  }
  echo "</UL></TD></TR>\n";
  echo "</TABLE>\n";
 }

 function CreateMtalogSQL() {
  if(count($this->item)>0) {
   foreach($this->item as $key=>$val) {
    if($val[0] == 'date') {
     // Change field from timestamp to date format
     $val[0] = "DATE_FORMAT(timestamp,'%Y-%m-%d')";
     // If LIKE selected - place wildcards either side of the query string
     if($val[1] == "LIKE" or $val[1] == "NOT LIKE") {
      $val[2] = '%'.$val[2].'%';
     }
     if(is_numeric($val[2])) {
      $sql .= "AND\n $val[0] $val[1] $val[2]\n";
     } else {
      // Handle NULL and NOT NULL's
      if($val[1] == "IS NULL" || $val[1] == "IS NOT NULL") {
       $sql .= "AND\n $val[0] $val[1]\n";
      } else {
       // Allow !<sql_function>
       if($val[2]{0} == "!") {
        $sql .= "AND\n $val[0] $val[1] ".substr($val[2],1)."\n";
       } else {
        // Regular string
        $sql .= "AND\n $val[0] $val[1] '$val[2]'\n";
       }
      }
     }
    }
   }
  }
  return $sql;
 }

 function CreateSQL() {
  $sql .= "AND ".$_SESSION['global_filter']."\n";
  if(count($this->item)>0) {
   foreach($this->item as $key=>$val) {
    // If LIKE selected - place wildcards either side of the query string
    if($val[1] == "LIKE" or $val[1] == "NOT LIKE") {
     $val[2] = '%'.$val[2].'%';
    }
    if(is_numeric($val[2])) {
     $sql .= "AND\n $val[0] $val[1] $val[2]\n";
    } else {
     // Handle NULL and NOT NULL's
     if($val[1] == "IS NULL" || $val[1] == "IS NOT NULL") {
      $sql .= "AND\n $val[0] $val[1]\n";
     } else {
      // Allow !<sql_function>
      if($val[2]{0} == "!") {
       $sql .= "AND\n $val[0] $val[1] ".substr($val[2],1)."\n";
      } else {
       // Regular string
       $sql .= "AND\n $val[0] $val[1] '$val[2]'\n";
      }
     }
    }
   }
   return $sql;
  } else {
   return "AND ".$_SESSION['global_filter']."\n";
  }
 }

 function TranslateColumn($column) {
  return($this->columns[$column]);
 }

 function TranslateOperator($operator) {
  return($this->operators[$operator]);
 }

 function DisplayForm() {
  // Form
  $return .= "<FORM ACTION=\"$_SERVER[PHP_SELF]\">\n";

  // Table
  $return .= "<TABLE WIDTH=\"100%\">\n";

  // Columns
  $return .= "<TR><TD COLSPAN=2>\n";
  $return .= "<SELECT NAME=\"column\">\n";
  foreach($this->columns as $key=>$val) {
   $return .= " <OPTION VALUE=\"$key\"";
   if ($this->display_last) {
    //  Use the last value as the default
    if ($key == $this->last_column) {
     $return .= " SELECTED";
    }
   }
   $return .= ">$val</OPTION>\n";
  }
  $return .= "</SELECT>\n";
  $return .= "</TD></TR>\n";

  // Operators
  $return .= "<TR><TD COLSPAN=2>\n";
  $return .= "<SELECT NAME=\"operator\">\n";
  foreach($this->operators as $key=>$val) {
   $return .= " <OPTION VALUE=\"$key\"";
   if ($this->display_last) {
    //  Use the last value as the default
    if ($key == $this->last_operator) {
     $return .= " SELECTED";
    }
   }
   $return .= ">$val</OPTION>\n";
  }
  $return .= "</SELECT><BR>\n";
  $return .= "</TD></TR>\n";

  // Input
  $return .= "<TR><TD>\n";
  $return .= "<INPUT TYPE=\"TEXT\" SIZE=50 NAME=\"value\"";
  if ($this->display_last) {
   //  Use the last value as the default
   $return .= " VALUE=\"" . htmlentities(stripslashes($this->last_value)) . "\"";
  }
  $return .= ">\n";
  $return .= "</TD><TD ALIGN=\"RIGHT\"><INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Add\"></TD></TR>";
  $return .= "<TR><TD ALIGN=\"LEFT\">To set date you must use YYYY-mm-dd format</TD>\n <TD></TD></TR>";
  $return .= "<TR><TH COLSPAN=2>Load/Save Filter</TH></TR>\n";
  $return .= "<TR><TD><INPUT TYPE=\"TEXT\" SIZE=50 NAME=\"save_as\"></TD><TD ALIGN=\"RIGHT\"><INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Save\"></TD></TR>";
  $return .= "<TR><TD>\n";
  $return .= $this->ListSaved()."</TD><TD ALIGN=\"RIGHT\" NOWRAP><INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Load\">&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Save\">&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Delete\"></TD></TR>\n";
  $return .= "</TABLE>";
  $return .= "</FORM>\n";


  return $return;
 }

 function AddReport($url, $description) {
  $this->reports[$description] =  $url;
 }

 function Save($name) {
  if(count($this->item)>0) {
   // Delete the existing first
   $dsql = "DELETE FROM saved_filters WHERE username='".$_SESSION['myusername']."' AND name='".mysql_escape_string($name)."'";
   dbquery($dsql);
   foreach($this->item as $key=>$val) {
    $sql = "REPLACE INTO saved_filters VALUES ('$name',";
    foreach($val as $value) {
     $sql .= "'$value',";
    }
    $sql .= "'".$_SESSION['myusername']."')";
    dbquery($sql);
   }
  }
 }

 function Load($name) {
  $sql = "SELECT col, operator, value FROM saved_filters WHERE name='".mysql_escape_string($name)."' AND username='".$_SESSION['myusername']."'";
  $sth = dbquery($sql);
  while($row=mysql_fetch_row($sth)) {
   $this->item[] = $row;
  }
 }

 function Delete($name) {
  $sql = "DELETE FROM saved_filters WHERE username='".$_SESSION['myusername']."' AND name='".mysql_escape_string($name)."'";
  dbquery($sql);
 }

 function ListSaved() {
  $sql = "SELECT DISTINCT name FROM saved_filters WHERE username='".$_SESSION['myusername']."'";
  $sth = dbquery($sql);
  $return = "<SELECT NAME=\"filter\">\n";
  $return .= " <OPTION VALUE=\"_none_\">None</OPTION>\n";
  while($row=mysql_fetch_array($sth)) {
   $return .= " <OPTION VALUE=\"$row[0]\">$row[0]</OPTION>\n";
  }
  $return .= "</SELECT>\n";
  return $return;
 }

}
