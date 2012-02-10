#!/bin/bash

#    Copyright 2012 Uriah Heise - aka. HyTeK
#
# This file is part of EMDA.
#
# EMDA is free software: you can redistribute it and/or modify it 
# under the terms of the GNU General Public License as published by 
# the Free Software Foundation, either version 3 of the License, or 
# (at your option) any later version.
#
# EMDA is distributed in the hope that it will be useful, but 
# WITHOUT ANY WARRANTY; without even the implied warranty of 
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
# General Public License for more details.You should have received a 
# copy of the GNU General Public License along with Foobar. If not, 
# see http://www.gnu.org/licenses/.


emdaVer="`cat /home/emda/workspace/src/trunk/version`"
echo $emdaVer > /home/emda/workspace/src/trunk/emda_install.img/emda/etc/emdaVersion
#create these symlinks since eclipse/svn doesn't like keeping symlinks 
ln -sf /var/www/html/fpdf152 /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/fpdf
ln -sf /var/www/html/jpgraph-1.12.1 /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/jpgraph 

#make i386 EMDA $emdaVer
arch="i386"
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/emda_install.img/emda /home/emda/workspace/src/trunk/emda_install.img/$arch/
#fixing permissions
	#User ID: apache=48, postfix=89
	#Group ID: apache=48, postfix=89, postdrop=90, clam=498
	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/root
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc
	sudo chown 0:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/mail/spamassassin/razor
		sudo chmod 770 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/mail/spamassassin/razor
	sudo chown 0:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/MailScanner/bayes
		sudo chmod 774 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/MailScanner/bayes
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/share
		sudo chmod 755 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/share
		sudo chmod 744 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/share/mailgraph/mailgraph.css		
	
	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/sbin/MailScanner
	
	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix
	sudo chown 89:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/*
	sudo chown :90 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/public
	sudo chown :90 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/maildrop
	sudo chown :48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/incoming
	sudo chown :48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/hold
	sudo chown :48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/outgoing
		sudo chmod 740 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/incoming
		sudo chmod 740 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/hold
		sudo chmod 740 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/outgoing

	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/MailScanner
	sudo chown 89:498 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/MailScanner/*
	sudo chown 89:48 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/MailScanner/quarantine	
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/lib/spamassassin	
	
	sudo chown 48:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/.spamassassin
	sudo chown 48:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www
	sudo chmod 755 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/cgi-bin
	sudo chmod ug+rwx /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/html/images
	sudo chmod ug+rwx /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/html/images/cache
	 

rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/repodata/comps.xml /home/emda/workspace/builds/EMDA-$arch/repodata/
mksquashfs /home/emda/workspace/src/trunk/emda_install.img/$arch /home/emda/workspace/builds/EMDA-$arch/images/install.img -noappend -no-sparse -no-recovery -info -no-fragments -no-duplicates
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/isolinux/* /home/emda/workspace/builds/EMDA-$arch/isolinux/;
sed -i "/menu title Welcome to EMDA ver arch Bit!/ c\menu title Welcome to EMDA $emdaVer 32 Bit!" /home/emda/workspace/builds/EMDA-$arch/isolinux/isolinux.cfg
sed -i "/  menu label ^Install EMDA ver arch/ c\  menu label ^Install EMDA $emdaVer $arch" /home/emda/workspace/builds/EMDA-$arch/isolinux/isolinux.cfg
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/repodata/* /home/emda/workspace/builds/EMDA-$arch/repodata/;
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/Packages/* /home/emda/workspace/builds/EMDA-$arch/Packages/;
#create the repo tree for i386
cd /home/emda/workspace/builds/EMDA-$arch
createrepo -g repodata/comps.xml .
mkisofs -r -R -J -T -v -no-emul-boot -boot-load-size 4 -boot-info-table -V "EMDA $emdaVer " -p "Team EMDA" -A "EMDA $emdaVer " -b isolinux/isolinux.bin -c isolinux/boot.cat -x "lost+found" -x "makeISO.*" -x "~*" -x "*~" -x "*.sh" -o /home/emda/workspace/iso/EMDA-$emdaVer-$arch.iso .;
#cleanup
sudo rm -fr /home/emda/workspace/src/trunk/emda_install.img/$arch/emda
rm -fr /home/emda/workspace/builds/EMDA-$arch/isolinux/isolinux.cfg
rm -fr /home/emda/workspace/builds/EMDA-$arch/install.img
rm -fr /home/emda/workspace/builds/EMDA-$arch/repodata/*

#make x86_64 EMDA $emdaVer
arch="x86_64"
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/emda_install.img/emda /home/emda/workspace/src/trunk/emda_install.img/$arch/
#fixing permissions
	#User ID: apache=48, postfix=89
	#Group ID: apache=48, postfix=89, postdrop=90, clam=498
	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/root
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc
	sudo chown 0:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/mail/spamassassin/razor
		sudo chmod 770 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/mail/spamassassin/razor
	sudo chown 0:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/etc/MailScanner/bayes
		sudo chmod 774 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/etc/MailScanner/bayes
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/share
		sudo chmod 755 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/share
		sudo chmod 744 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/share/mailgraph/mailgraph.css		
	
	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/usr/sbin/MailScanner
	
	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix
	sudo chown 89:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/*
	sudo chown :90 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/public
	sudo chown :90 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/maildrop
	sudo chown :48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/incoming
	sudo chown :48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/hold
	sudo chown :48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/outgoing
		sudo chmod 740 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/incoming
		sudo chmod 740 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/hold
		sudo chmod 740 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/postfix/outgoing

	sudo chown 0:0 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/MailScanner
	sudo chown 89:498 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/MailScanner/*
	sudo chown 89:48 /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/spool/MailScanner/quarantine
	
	sudo chown 0:0 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/lib/spamassassin	
	
	sudo chown 48:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/.spamassassin
	sudo chown 48:48 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www
	sudo chmod 755 -R /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/cgi-bin
	sudo chmod ug+rwx /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/html/images
	sudo chmod ug+rwx /home/emda/workspace/src/trunk/emda_install.img/$arch/emda/var/www/html/images/cache


rsync --archive --exclude '.svn' /home/emda/workspace/src/trunk/repodata/comps.xml /home/emda/workspace/builds/EMDA-$arch/repodata/
mksquashfs /home/emda/workspace/src/trunk/emda_install.img/$arch /home/emda/workspace/builds/EMDA-$arch/images/install.img -noappend -no-sparse -no-recovery -info -no-fragments -no-duplicates
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/isolinux/* /home/emda/workspace/builds/EMDA-$arch/isolinux/;
sed -i "/menu title Welcome to EMDA ver arch Bit!/ c\menu title Welcome to EMDA $emdaVer 64 Bit!" /home/emda/workspace/builds/EMDA-$arch/isolinux/isolinux.cfg
sed -i "/  menu label ^Install EMDA ver arch/ c\  menu label ^Install EMDA $emdaVer $arch" /home/emda/workspace/builds/EMDA-$arch/isolinux/isolinux.cfg
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/repodata/* /home/emda/workspace/builds/EMDA-$arch/repodata/;
rsync -ar --exclude '.svn' /home/emda/workspace/src/trunk/Packages/* /home/emda/workspace/builds/EMDA-$arch/Packages/;
#create the repo tree for x86_64
cd /home/emda/workspace/builds/EMDA-$arch
createrepo -g repodata/comps.xml .
mkisofs -r -R -J -T -v -no-emul-boot -boot-load-size 4 -boot-info-table -V "EMDA $emdaVer $arch" -p "Team EMDA" -A "EMDA $emdaVer $arch" -b isolinux/isolinux.bin -c isolinux/boot.cat -x "lost+found" -x "makeISO.*" -x "~*" -x "*~" -x "*.sh" -o /home/emda/workspace/iso/EMDA-$emdaVer-$arch.iso .;
#cleanup
sudo rm -fr /home/emda/workspace/src/trunk/emda_install.img/$arch/emda
rm -fr /home/emda/workspace/builds/EMDA-$arch/isolinux/isolinux.cfg
rm -fr /home/emda/workspace/builds/EMDA-$arch/install.img
rm -fr /home/emda/workspace/builds/EMDA-$arch/repodata/*


#clean up local links
rm /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/fpdf
rm /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/jpgraph
#clean out version info
rm /home/emda/workspace/src/trunk/emda_install.img/emda/etc/emdaVersion

exit 0;
