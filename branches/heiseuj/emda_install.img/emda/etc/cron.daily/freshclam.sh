#!/bin/bash

#MalwarePatrol DB download and conversion to .db
#Regular List Basic: http://malwarepatrol.com.br/cgi/submit?action=list_clamav
#Regular List Extended: http://malwarepatrol.com.br/cgi/submit?action=list_clamav_ext
#Aggresive List Basic: http://malwarepatrol.com.br/cgi/submit-agressive?action=list_clamav&type=agressive
#Aggresive List Extended: http://malwarepatrol.com.br/cgi/submit-agressive?action=list_clamav_ext&type=agressive
wget -O - http://malwarepatrol.com.br/cgi/submit?action=list_clamav_ext > /var/lib/clamav/mbl.db

#SaneSecurity DB downloads via rsync
rsync rsync://rsync.sanesecurity.net/sanesecurity/junk.ndb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/jurlbl.ndb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/phish.ndb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/rogue.hdb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/scam.ndb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/spamimg.hdb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/spamattach.hdb /var/lib/spamav/

#OITC DB downloads via rsync
rsync rsync://rsync.sanesecurity.net/sanesecurity/winnow_malware.hdb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/winnow_malware_links.ndb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/winnow_extended_malware.hdb /var/lib/spamav/
rsync rsync://rsync.sanesecurity.net/sanesecurity/winnow.attachments.hdb /var/lib/spamav/

freshclam
