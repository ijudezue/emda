#!/bin/sh

##
## OLD STUFF !!! papmich
##

LOAD_LOC=/var/emda_install



#
#chkconfig --add postfix
#chkconfig --level 345 postfix on
#/usr/sbin/alternatives --set mta /usr/sbin/sendmail.postfix
#chkconfig --levels 2345 sendmail off


# Enable daemons

#chkconfig saslauthd on
#chkconfig yum on

# make safe copy of 

cp /etc/postfix/main.cf /etc/postfix/main.save
cp /etc/postfix/master.cf /etc/postfic/master.save

# Configure Postfix
mkdir /etc/postfix/ssl
echo /^Received:/ HOLD>>/etc/postfix/header_checks
postconf -e "inet_interfaces = all"
postconf -e "mynetworks_style = subnet"
postconf -e "header_checks = regexp:/etc/postfix/header_checks"
postconf -e "myorigin = \$mydomain"
postconf -e "mydestination = \$myhostname, localhost.\$mydomain, localhost"
postconf -e "relay_domains = hash:/etc/postfix/transport"
postconf -e "transport_maps = hash:/etc/postfix/transport"
postconf -e "local_recipient_maps = "
postconf -e "smtpd_helo_required = yes"
postconf -e "smtpd_delay_reject = yes"
postconf -e "disable_vrfy_command = yes"
#postconf -e "virtual_alias_maps = hash:/etc/postfix/virtual"
#postconf -e "alias_maps = hash:/etc/aliases"
postconf -e "alias_database = hash:/etc/aliases"
postconf -e "default_destination_recipient_limit = 1"
# SASL config
postconf -e "broken_sasl_auth_clients = yes"
postconf -e "smtpd_sasl_auth_enable = yes"
postconf -e "smtpd_sasl_local_domain = "
postconf -e "smtpd_sasl_path = smtpd"
postconf -e "smtpd_sasl_local_domain = $myhostname"
postconf -e "smtpd_sasl_security_options = noanonymous"
postconf -e "smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd"
postconf -e "smtp_sasl_type = cyrus"
# tls config
postconf -e "smtp_use_tls = yes"
postconf -e "smtpd_use_tls = yes"
postconf -e "smtp_tls_CAfile = /etc/postfix/ssl/smtpd.pem"
postconf -e "smtp_tls_session_cache_database = btree:/var/spool/postfix/smtp_tls_session_cache"
postconf -e "smtp_tls_note_starttls_offer = yes"
postconf -e "smtpd_tls_key_file = /etc/postfix/ssl/smtpd.pem"
postconf -e "smtpd_tls_cert_file = /etc/postfix/ssl/smtpd.pem"
postconf -e "smtpd_tls_CAfile = /etc/postfix/ssl/smtpd.pem"
postconf -e "smtpd_tls_loglevel = 1"
postconf -e "smtpd_tls_received_header = yes"
postconf -e "smtpd_tls_session_cache_timeout = 3600s"
postconf -e "tls_random_source = dev:/dev/urandom"
postconf -e "smtpd_tls_session_cache_database = btree:/var/spool/postfix/smtpd_tls_session_cache"
postconf -e "smtpd_tls_security_level = may"
# restrictions
postconf -e "smtpd_helo_restrictions =  check_helo_access hash:/etc/postfix/helo_access, reject_invalid_hostname"
postconf -e "smtpd_sender_restrictions = permit_sasl_authenticated, check_sender_access hash:/etc/postfix/sender_access, reject_non_fqdn_sender, reject_unknown_sender_domain"
postconf -e "smtpd_data_restrictions =  reject_unauth_pipelining"
postconf -e "smtpd_client_restrictions = permit_sasl_authenticated, reject_rbl_client zen.spamhaus.org, reject_rbl_client b.barracudacentral.org, reject_rbl_client bl.spamcop.net, #reject_rbl_client list.dsbl.org"
postconf -e "smtpd_recipient_restrictions = permit_sasl_authenticated, permit_mynetworks, reject_unauth_destination, reject_non_fqdn_recipient, reject_unknown_recipient_domain, check_recipient_access hash:/etc/postfix/recipient_access, check_policy_service inet:127.0.0.1:2501"
postconf -e "masquerade_domains = \$mydomain"


#* Postfix Admin 
# * 
# * LICENSE 
# * This source file is subject to the GPL license that is bundled with  
# * this package in the file LICENSE.TXT. 
# * 
# * Further details on the project are available at : 
# *     http://www.postfixadmin.com or http://postfixadmin.sf.net 
# * 
# * @version $Id: setup.php 824 2010-05-16 22:55:19Z christian_boltz $ 
# * @license GNU GPL v2 or later. 

# postfix admin changes
postconf -e "virtual_mailbox_domains = proxy:mysql:/etc/postfix/sql/mysql_virtual_domains_maps.cf"
postconf -e "virtual_alias_maps = proxy:mysql:/etc/postfix/sql/mysql_virtual_alias_maps.cf, proxy:mysql:/etc/postfix/sql/mysql_virtual_alias_domain_maps.cf, proxy:mysql:/etc/postfix/sql/mysql_virtual_alias_domain_catchall_maps.cf"
postconf -e "virtual_mailbox_maps = proxy:mysql:/etc/postfix/sql/mysql_virtual_mailbox_maps.cf, proxy:mysql:/etc/postfix/sql/mysql_virtual_alias_domain_mailbox_maps.cf"

#------------------------------------------
#mysql_virtual_alias_maps.cf
#------------------------------------------
#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#table = alias
#select_field = goto
#where_field = address

#Syntax with postfix 2.2.x:
#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#query = SELECT goto FROM alias WHERE address='%s'

#------------------------------------------

cat << EOF > /etc/postfix/mysql_virtual_alias_maps.cf

#------------------------------------------
#mysql_virtual_alias_maps.cf
#------------------------------------------
user = postfix
password = postfix
hosts = localhost
dbname = postfix
table = alias
select_field = goto
where_field = address

#Syntax with postfix 2.2.x:
#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#query = SELECT goto FROM alias WHERE address='%s'

#------------------------------------------

EOF
 

#------------------------------------------
# mysql_virtual_domains_maps.cf
#------------------------------------------
#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#table = domain
#select_field = description
#where_field = domain
#additional_conditions = and backupmx = '0' and active = '1'
#query = SELECT description FROM domain WHERE domain='%s'

#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#query = SELECT description FROM domain WHERE domain='%s'
#------------------------------------------ 

cat << EOF > /etc/postfix/mysql_virtual_domains_maps.cf

#------------------------------------------
# mysql_virtual_domains_maps.cf
#------------------------------------------
user = postfix
password = postfix
hosts = localhost
dbname = postfix
table = domain
select_field = description
where_field = domain
additional_conditions = and backupmx = '0' and active = '1'
query = SELECT description FROM domain WHERE domain='%s'

#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#query = SELECT description FROM domain WHERE domain='%s'
#------------------------------------------ 

EOF

#------------------------------------------
#  mysql_virtual_mailbox_maps.cf
#------------------------------------------
#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#table = mailbox
#select_field = maildir
#where_field = username
#additional_conditions = and active = '1'
##user = postfix
##password = postfix
##hosts = localhost
##dbname = postfix
##query = SELECT maildir FROM mailbox WHERE username='%s'
#------------------------------------------ 

cat << EOF > /etc/postfix/mysql_virtual_mailbox_maps.cf

#------------------------------------------
#  mysql_virtual_mailbox_maps.cf
#------------------------------------------
user = postfix
password = postfix
hosts = localhost
dbname = postfix
table = mailbox
select_field = maildir
where_field = username
additional_conditions = and active = '1'
#user = postfix
#password = postfix
#hosts = localhost
#dbname = postfix
#query = SELECT maildir FROM mailbox WHERE username='%s'
#------------------------------------------ 

########
#For MySQL:
#  CREATE DATABASE postfix;
#  CREATE USER 'postfix'@'localhost' IDENTIFIED BY 'choose_a_password';
#  GRANT ALL PRIVILEGES ON 'postfix' . * TO 'postfix'@'localhost';
########




# Create Database User & Database
#mysqladmin -u root

echo "CREATE DATABASE postfix;" | /usr/bin/mysql --user=root
echo "CREATE USER 'postfix'@'localhost' IDENTIFIED BY 'postfix';" | /usr/bin/mysql --user=root 
echo "GRANT ALL PRIVILEGES ON "postfix" . * TO 'postfix'@'localhost';" | /usr/bin/mysql --user=root

#
###
# More information - HowTo docs that use PostfixAdmin
#http://postfix.wiki.xs4all.nl/index.php?title=Virtual_Users_and_Domains_with_Courier-IMAP_and_MySQL
# http://wiki.dovecot.org/HowTo/DovecotLDAPostfixAdminMySQL
###


#other configuration files
newaliases
touch /etc/postfix/transport
touch /etc/postfix/virtual
touch /etc/postfix/helo_access
touch /etc/postfix/sender_access
touch /etc/postfix/recipient_access
touch /etc/postfix/sasl_passwd
postmap /etc/postfix/transport
postmap /etc/postfix/virtual
postmap /etc/postfix/helo_access
postmap /etc/postfix/sender_access
postmap /etc/postfix/recipient_access
postmap /etc/postfix/sasl_passwd
echo "pwcheck_method: auxprop">/usr/lib/sasl2/smtpd.conf
echo "auxprop_plugin: sasldb">>/usr/lib/sasl2/smtpd.conf
echo "mech_list: PLAIN LOGIN CRAM-MD5 DIGEST-MD5">>/usr/lib/sasl2/smtpd.conf
#----------------------------------
#Create then fix permissions on the SASL database
echo password|saslpasswd2 -p -c -u `postconf -h myhostname` exampleuser
saslpasswd2 -p -d -u `postconf -h myhostname` exampleuser
chown postfix:postdrop /etc/sasldb2
#----------------------------------
#Fix permissions on /var/spool/postfix/hold
chown postfix:apache /var/spool/postfix/hold
chmod 770 /var/spool/postfix/hold
#------------------------------------



