#!/bin/bash

#    Copyright 2012 Uriah Heise - aka. HyTeK
#
# This file is part of EMDA.
#	Enterprise Mail Delivery Appliance
#	www.emda.pro
#
# EMDA is free software: you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# EMDA is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
# General Public License for more details. You should have received a
# copy of the GNU General Public License along with EMDA. If not,
# see http://www.gnu.org/licenses/.

if [ ! "`lsb_release -a | grep CentOS | grep 6.`" ]
then
	clear
	echo "It appears you are not running CentOS 6.x."
	echo " "
	echo "CentOS 6.0 is required to develop EMDA, and to use this installer."
	echo "Please run this installer again from a CentOS 6.0 installation."
	exit 0
fi

homePath="`whoami`"
ver="1.0"

#check what arch we are currently running on
tmp="`uname -m`"
if [ "$tmp" == "x86_64" ]
then
	curArch="64"
else
	curArch=""
fi

#shouldn't run this script as root, but if the developer is root, set the correct homePath
if [ "$homePath" == "root" ]
then
	homePath="/root"
else
	homePath="/home/$homePath"
fi

workspacePath="$homePath/EMDAworkspace-$ver"
buildPath="$workspacePath/builds"
isoPath="$workspacePath/iso"
isoMountPath="$workspacePath/tmp"
#srcPath="$workspacePath/src"
srcPath="$workspacePath/src"



func_checkDeps(){
	if [ ! "`rpm -qa | grep epel-release`" ]
	then
    su -c 'rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-5.noarch.rpm'
		if [ ! "`rpm -qa | grep epel-release`" ]
		then
			clear
			echo "EMDA developer workstations require the epel-release rpm package to be installed first."
			echo "This workstation was unable to obtain and install the required package."
			echo " "
			echo "The package required is: epel-release-6-5.noarch.rpm"
			echo " "
			echo "Please download and install epel-release before attempting to run this installer again."
			exit 0
		fi
	fi

	sudo yum install -y ant ant-antlr ant-apache-bcel ant-apache-bsf ant-apache-log4j ant-apache-oro \
ant-apache-regexp ant-apache-resolver ant-commons-logging ant-commons-net ant-javamail \
ant-jdepend ant-jsch ant-junit antlr ant-nodeps ant-swing ant-trax apache-jasper augeas-libs \
autoconf automake axis bcel bsf classpathx-jaf classpathx-mail cloog-ppl cobbler cobbler-web \
cpp Django ecj eclipse-emf eclipse-gef eclipse-phpeclipse eclipse-platform eclipse-rcp eclipse-rse eclipse-subclipse \
eclipse-subclipse-graph eclipse-svnkit eclipse-swt epel-release fakeroot fakeroot-libs gcc gd \
geronimo-specs geronimo-specs-compat gettext-devel gettext-libs git glib2-devel glibc-devel \
glibc-headers icu4j-eclipse intltool jakarta-commons-discovery jakarta-commons-el \
jakarta-commons-net jakarta-oro java-1.6.0-openjdk-devel jdepend jdom jetty-eclipse jigdo \
jna jsch junit jzlib kernel kernel-headers koan libvirt-client libvirt-python libyaml \
livecd-tools log4j lucene-contrib mod_python mod_wsgi mpfr mx4j netcf-libs perl-Cairo \
perl-CGI perl-Chart perl-Compress-Raw-Zlib perl-Compress-Zlib perl-Crypt-SSLeay \
perl-devel perl-Digest-HMAC perl-Error perl-ExtUtils-MakeMaker perl-ExtUtils-ParseXS \
perl-GD perl-Getopt-GUI-Long perl-Git perl-Glib perl-Gtk2 perl-HTML-Parser perl-HTML-Tagset \
perl-IO-Compress-Base perl-IO-Compress-Zlib perl-IO-Socket-SSL perl-libwww-perl perl-Net-DNS \
perl-Net-LibIDN perl-Net-SSLeay perl-Pango perl-QWizard perl-Test-Harness perl-Test-Simple \
perl-Time-Piece perl-Tk perl-URI perl-XML-Parser perl-XML-Simple polipo ppl python-cheetah \
python-devel python-imgcreate python-markdown python-netaddr python-pygments python-simplejson \
python-virtinst PyYAML regexp rhpl rpm-build rpmdevtools sat4j saxon slf4j subversion \
subversion-javahl svnkit system-config-kickstart system-config-language tftp-server \
trilead-ssh2 wsdl4j xalan-j2 xerces-j2 xinetd xml-commons-resolver yajl yum-downloadonly
}

func_checkWorkspace(){
	if [ ! -d $workspacePath ]
	then
		mkdir $workspacePath
		mkdir $buildPath
		mkdir $isoPath
		mkdir $isoMountPath
		mkdir -p $srcPath
	fi
}

func_downloadExtractIsos(){
	isoDownload="http://mirror.stanford.edu/yum/pub/centos/6.2/isos/$1/CentOS-6.2-$1-minimal.iso"
	if [ ! -f $isoPath/CentOS-6.2-$1-minimal.iso ]
	then
		clear
		echo "Please wait while we download the CentOS $1 ISO."
		echo ""
		wget -nv $isoDownload -O $isoPath/CentOS-6.2-$1-minimal.iso
		echo " "
		echo "Downloading Complete. Please press the enter key to continue..."
		read tmp
	fi

	if [ -f $isoPath/CentOS-6.2-$1-minimal.iso ]
	then
		sudo mount -o loop $isoPath/CentOS-6.2-$1-minimal.iso $isoMountPath
		if [ -d $isoMountPath/images ]
		then
			sudo rsync -ar $isoMountPath/* $buildPath/EMDA-$1/
      sudo cp $isoMountPath/.discinfo $buildPath/EMDA-$1/
      sudo cp $isoMountPath/.treeinfo $buildPath/EMDA-$1/
			sudo umount $isoMountPath
		else
			echo "There was an error with the downloaded ISO image. Please delete the file $isoPath/CentOS-6.2-$1-minimal.iso, and run this installer again."
			exit 0
		fi
	fi
}

func_extractImage(){
	if [ -f $buildPath/EMDA-$1/images/install.img ]
	then
		sudo mount -t squashfs $buildPath/EMDA-$1/images/install.img $isoMountPath -o loop
		if [ -d $isoMountPath/etc ]
		then
			sudo rsync -ar $isoMountPath/* $buildPath/installImg-$1/
			sudo umount $isoMountPath
		else
			echo "There was an error mounting the install.img. Please correct and rerun this installer."
			exit 0
		fi
	else
		echo "There was an error locating the install.img. Please correct and rerun this installer."
		exit 0
	fi
}

func_eclipse(){
	if [ -f /usr/lib$curArch/eclipse/configuration/config.ini ]
	then
		su -c "echo \"osgi.instance.area.default=@user.home/EMDAworkspace-$ver/src\" >> /usr/lib$curArch/eclipse/configuration/config.ini"
	fi
}

func_rpmPackages(){
	if [ -f $builtPath/EMDA-$1/Packages/emda-rpms-$1.tar.gz ] && [ ! -f $buildPath/EMDA-$1/Packages/* ]
	then
		cd $buildPath/EMDA-$1/Packages
		sudo tar xzf emda-rpms-$1.tar.gz
		sudo rm -fr emda-rpms-$1.tar.gz
	
	elif [ -d $buildPath/EMDA-$1/Packages ]
	then
		cd $buildPath/EMDA-$1/Packages
		sudo rm -fr $buildPath/EMDA-$1/Packages/*
		clear
		echo "Please wait while we download the rpm packages required for EMDA."
		sudo wget -nv http://www.emda.pro/dev/$ver/emda-rpms-$1.tar.gz
		echo "Download complete. Please wait while we extract the packages. Press the enter key to continue..."
		read tmp
		sudo tar xzf emda-rpms-$1.tar.gz
		sudo rm -fr emda-rpms-$1.tar.gz
	else
		echo "There was an error with the rpm packages for emda. Please correct and rerun this installer."
	fi
}

func_svnTrunk(){
	cd $srcPath
	svn checkout http://emda.googlecode.com/svn/trunk/ trunk
	cd $srcPath/trunk
}

main(){
	func_checkDeps
	
	func_checkWorkspace
  clear
	echo "Dependency installation complete. Press the enter key to continue..."
  read tmp
	func_eclipse
	
	func_downloadExtractIsos i386
	func_downloadExtractIsos x86_64

	func_extractImage i386
	func_extractImage x86_64

	func_rpmPackages i386
	func_rpmPackages x86_64

	func_svnTrunk
}

main
clear
echo "EMDA Developer Workstation Installation is complete. Please run $srcPath/trunk/makeISO.sh to create ISO images for EMDA."
echo ""
sudo -K
exit 0