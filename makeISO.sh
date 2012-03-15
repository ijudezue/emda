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


homePath="`whoami`"
ver="1.0"

if [ "$homePath" == "root" ]
then
	homePath="/root"
else
	homePath="/home/$homePath"
fi

workspacePath="$homePath/EMDAworkspace-$ver"
#workspacePath="$homePath/EMDAworkspace"
buildPath="$workspacePath/builds"
isoPath="$workspacePath/iso"
isoMountPath="$workspacePath/tmp"

#change "trunk" to whatever you named your branch from google code svn
#$srcPath is the actual location (root path) of the checked out files from subversion.
# basically the root path to makeISO.sh
#srcPath="$workspacePath/src/trunk"
srcPath="`pwd`"

emdaImagePath="$srcPath/emda_install.img"

emdaVer="`cat $srcPath/version`"
echo $emdaVer > $emdaImagePath/emda/etc/emdaVersion

#create these symlinks since eclipse/svn doesn't like keeping symlinks 
ln -sf /var/www/html/fpdf152 $emdaImagePath/emda/var/www/html/fpdf
ln -sf /var/www/html/jpgraph-1.12.1 $emdaImagePath/emda/var/www/html/jpgraph

func_makeIso(){
	sudo rsync -ar --exclude '.svn' --exclude '.metadata' --exclude "~*" --exclude "*~" $emdaImagePath/emda $buildPath/installImg-$1/
	#fixing permissions
		#User ID: apache=48, postfix=89
		#Group ID: apache=48, postfix=89, postdrop=90, clam=498
		sudo chown 0:0 -R $buildPath/installImg-$1/emda
		
		sudo chown 0:48 -R $buildPath/installImg-$1/emda/etc/mail/spamassassin/razor
			sudo chmod 770 -R $buildPath/installImg-$1/emda/etc/mail/spamassassin/razor
		sudo chown 0:48 -R $buildPath/installImg-$1/emda/etc/MailScanner/bayes
			sudo chmod 774 -R $buildPath/installImg-$1/emda/etc/MailScanner/bayes
	
			sudo chmod 755 -R $buildPath/installImg-$1/emda/usr/share
			sudo chmod 744 -R $buildPath/installImg-$1/emda/usr/share/mailgraph/mailgraph.css
	
		sudo chown 89:0 $buildPath/installImg-$1/emda/var/spool/postfix/*
		sudo chown :90 -R $buildPath/installImg-$1/emda/var/spool/postfix/public
		sudo chown :90 -R $buildPath/installImg-$1/emda/var/spool/postfix/maildrop
		sudo chown :48 -R $buildPath/installImg-$1/emda/var/spool/postfix/incoming
		sudo chown :48 -R $buildPath/installImg-$1/emda/var/spool/postfix/hold
		sudo chown :48 -R $buildPath/installImg-$1/emda/var/spool/postfix/outgoing
			sudo chmod 740 $buildPath/installImg-$1/emda/var/spool/postfix/incoming
			sudo chmod 740 $buildPath/installImg-$1/emda/var/spool/postfix/hold
			sudo chmod 740 $buildPath/installImg-$1/emda/var/spool/postfix/outgoing

		sudo chown :498 -R $buildPath/installImg-$1/emda/var/lib/clamav
	
		sudo chown 89:498 $buildPath/installImg-$1/emda/var/spool/MailScanner/*
		sudo chown 89:48 $buildPath/installImg-$1/emda/var/spool/MailScanner/quarantine
	
		sudo chown 48:48 -R $buildPath/installImg-$1/emda/var/www/.spamassassin
		sudo chown 48:48 -R $buildPath/installImg-$1/emda/var/www
		sudo chmod 755 -R $buildPath/installImg-$1/emda/var/www/cgi-bin
		sudo chmod ug+rwx $buildPath/installImg-$1/emda/var/www/html/images
		sudo chmod ug+rwx $buildPath/installImg-$1/emda/var/www/html/images/cache
		 
	
	sudo rsync -ar --exclude '.svn' --exclude '.metadata' --exclude "~*" --exclude "*~" $srcPath/repodata/comps.xml $buildPath/EMDA-$1/repodata/
	sudo mksquashfs $buildPath/installImg-$1 $buildPath/EMDA-$1/images/install.img -noappend -no-sparse -no-recovery -info -no-fragments -no-duplicates
	sudo rsync -ar --exclude '.svn' --exclude '.metadata' --exclude "~*" --exclude "*~" $srcPath/isolinux/* $buildPath/EMDA-$1/isolinux/;
	sudo sed -i "/menu title Welcome to EMDA ver arch Bit!/ c\menu title Welcome to EMDA $emdaVer $1" $buildPath/EMDA-$1/isolinux/isolinux.cfg
	sudo sed -i "/  menu label ^Install EMDA ver arch/ c\  menu label ^Install EMDA $emdaVer $1" $buildPath/EMDA-$1/isolinux/isolinux.cfg
	sudo rsync -ar --exclude '.svn' --exclude '.metadata' --exclude "~*" --exclude "*~" $srcPath/repodata/* $buildPath/EMDA-$1/repodata/;
	sudo rsync -ar --exclude '.svn' --exclude '.metadata' --exclude "~*" --exclude "*~" $srcPath/Packages/* $buildPath/EMDA-$1/Packages/;
	#create the repo tree
	cd $buildPath/EMDA-$1
	sudo createrepo -g repodata/comps.xml .
	sudo mkisofs -r -R -J -T -v -no-emul-boot -boot-load-size 4 -boot-info-table -V "EMDA $emdaVer " -p "Team EMDA" -A "EMDA $emdaVer " -b isolinux/isolinux.bin -c isolinux/boot.cat -x "lost+found" -x "makeISO.*" -x "~*" -x "*~" -x "*.sh" -o $isoPath/EMDA-$emdaVer-$1.iso .;
	#cleanup
	sudo rm -fr $buildPath/installImg-$1/emda
	sudo rm -fr $buildPath/EMDA-$1/isolinux/isolinux.cfg
	sudo rm -fr $buildPath/EMDA-$1/install.img
	sudo rm -fr $buildPath/EMDA-$1/repodata/*
}

main(){
	func_makeIso i386
	func_makeIso x86_64

	#clean up local links
	sudo rm $emdaImagePath/emda/var/www/html/fpdf
	sudo rm $emdaImagePath/emda/var/www/html/jpgraph
	#clean out version info
	sudo rm $emdaImagePath/emda/etc/emdaVersion
}

main

sudo -K
exit 0;
