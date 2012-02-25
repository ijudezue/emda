
#!/usr/bin/perl
use CGI::Carp qw(fatalsToBrowser);
use CGI qw(:standard);
print "Content-type: text/html \n\n";

$query    = new CGI;
$sendmail = "/usr/sbin/sendmail.postfix";
$id = param("id");
$datenumber = param("datenumber");
$to = param("to");
$msgtorelease = "/var/spool/MailScanner/quarantine/$datenumber/spam/$id";

open(MAIL, "|$sendmail $to <$msgtorelease") or die "Cannot open $sendmail: $!";
close(MAIL);

# redirect to success page
print "<meta http-equiv=\"refresh\" content=\"0;URL=/released.html\">";