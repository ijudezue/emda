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

// Authenication checking
session_start();
require('login.function.php');

html_start('Tools');

?>
<TABLE WIDTH=100% CLASS="BOXTABLE">
 <TR>
  <TD>
   <UL>
    <?php if(!MSEE): ?>
     <LI><A HREF="user_manager.php">User Management</A>
    <?php endif; ?>
    <?php if(preg_match('/sophos/i',get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="sophos_status.php">Sophos Status</A>
    <?php endif; ?>
    <?php if(preg_match('/f-secure/i',get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="f-secure_status.php">F-Secure Status</A>
    <?php endif; ?>
    <?php if(preg_match('/clam/i',get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="clamav_status.php">ClamAV Status</A>
    <?php endif; ?>
    <?php if(preg_match('/mcafee/i',get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="mcafee_status.php">McAfee Status</A>
    <?php endif; ?>
    <?php if(preg_match('/f-prot/i',get_conf_var('VirusScanners')) && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="f-prot_status.php">F-Prot Status</A>
    <?php endif; ?>
    <?php if($_SESSION['user_type'] == 'A'): ?>
    <LI><A HREF="mysql_status.php">MySQL Database Status</A>
    <?php endif; ?>
    <?php if($_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="msconfig.php">View MailScanner Configuration</A>
    <?php endif; ?>
    <?php if(!DISTRIBUTED_SETUP && get_conf_truefalse('UseSpamAssassin') && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="bayes_info.php">SpamAssassin Bayes Database Info</A>
     <LI><A HREF="sa_lint.php">SpamAssassin Lint (Test)</A>
     <LI><A HREF="ms_lint.php">MailScanner Lint (Test)</A>
     <LI><A HREF="sa_rules_update.php">Update SpamAssasin Rule Descriptions</A>
    <?php endif; ?>
    <?php if(!DISTRIBUTED_SETUP && get_conf_truefalse('MCPChecks') && $_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="mcp_rules_update.php">Update MCP Rule Descriptions</A>
    <?php endif; ?>
    <?php if($_SESSION['user_type'] == 'A'): ?>
     <LI><A HREF="geoip_update.php">Update GeoIP Database</A>
    <?php endif; ?>
   </UL>
<?php if($_SESSION['user_type'] == 'A'): ?>
   <P>Links</P>
   <UL>
    <LI><A HREF="http://mailwatch.sourceforge.net" target="_newwin1">MailWatch for MailScanner</A>

    <LI><A HREF="http://www.mailscanner.info" target="_newwin1">MailScanner</A>
    <?php if(get_conf_truefalse('UseSpamAssassin')): ?>
     <LI><A HREF="http://www.spamassassin.org" target="_newwin2">SpamAssassin</A>
    <?php endif; ?>
    <?php if(preg_match('/sophos/i',get_conf_var('VirusScanners'))): ?>
     <LI><A HREF="http://www.sophos.com" target="_newwin3">Sophos</A>
    <?php endif; ?>
    <?php if(preg_match('/clamav/i',get_conf_var('VirusScanners'))): ?>
     <LI><A HREF="http://clamav.sourceforge.net" target="_newwin4">ClamAV</A>
    <?php endif; ?>
    <LI><A HREF="http://www.dnsstuff.com" target="_newwin5">DNSstuff</A>
    <LI><A HREF="http://www.samspade.org" target="_newwin6">Sam Spade</A>
    <LI><A HREF="http://spam.abuse.net" target="_newwin7">spam.abuse.net</A>
    <LI><A HREF="http://www.dnsreport.com" target="_newwin8">DNS Report</A>
   </UL>
<?php endif; ?>
  </TD>
 </TR>
</TABLE>
<?php
// Add footer
html_end();
// Close any open db connections
dbclose();
?>
