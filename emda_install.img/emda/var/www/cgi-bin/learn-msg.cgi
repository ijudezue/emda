#!/usr/bin/perl
use CGI::Carp qw(fatalsToBrowser);
use CGI qw(:standard);
print "Content-type: text/html \n\n";

$query    = new CGI;
$salearn = "/usr/bin/sa-learn --spam";
$id = param("id");
$msgtolearn = `find /var/spool/MailScanner/quarantine/ -name $id`;

print "$msgtolearn";
open(MAIL, "|$salearn $msgtolearn") or die "Cannot open $salearn: $!";
close(MAIL);

# redirect to success page
print "<meta http-equiv=\"refresh\" content=\"0;URL=/learned.html\">";