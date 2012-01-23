#!/bin/bash

#The purpose of this script is to download all required packages, clean out all old packages, leaving only the most up-to-date packages.
#This script should only be run when the $EMDAversion changes, as some packages that get updated could change the functionality of the system.
#While building version EMDA 1.0, this script should only be run once. When we start working on the next version of EMDA 1.1, this script should
#be run one more time for that version. Basically this is being used to snapshot all required packages to the latest available for a given EMDA release.
#Be warned, this script can take several hours to run, and will download a few gigabytes worth of data.

#Seperate the RPM download list by first letter
#This list should coincide with the comps.xml for mandatory packages to install
uRPMA="acl-*, aic94xx-firmware-*, apr-*, attr-*, audit-*, authconfig-*, atk-*, avahi-libs-*"
uRPMB="basesystem-*, bash-*, bc-*, bfa-firmware-*, binutils-*, bzip2-*"
uRPMC="cairo-*, cloog-ppl-*, cpp-*, cyrus-sasl-*, clamd-*, clamav-*, ca-certificates-*, centos-release-*, checkpolicy-*, chkconfig-*, coreutils-*, coreutils-libs*, cpio-*, cracklib-*, cracklib-dicts-*, cryptsetup-luks-*, cryptsetup-luks-libs-*, curl-*, cyrus-sasl-lib-*"
uRPMD="dejavu-sans-fonts-*, dojo-*, Django-*, django-picklefield-*, django-celery-*, Django-south-*, dosfstools-*, dejavu-fonts-common-*, dejavu-sans-mono-fonts-*, dejavu-lgc-sans-mono-fonts-*, dash-*, db4-*, db4-utils-*, dbus-glib-*, dbus-libs-*, deltarpm-*, device-mapper-*, device-mapper-event-*, device-mapper-event-libs-*, device-mapper-libs-*, device-mapper-multipath-*, device-mapper-multipath-libs-*, dhclient-*, diffutils-*, dracut-*, dracut-kernel-*, dracut-network-*"
uRPME="erlang-*, e2fsprogs-*, e2fsprogs-libs-*, efibootmgr-*, elfutils-libelf-*, ethtool-*, expat-*"
uRPMF="fontpackages-filesystem-*, fontconfig-*, freetype-*, fcoe-utils-*, file-libs-*, filesystem-*, findutils-*, fipscheck-*, fipscheck-lib-*"
uRPMG="GeoIP-*, gtk2-*, gnutls-*, ghostscript-*, gocr*, gcc-*, gcc-c++-*, giflib-*, gifsicle-*, gamin-*, gawk-*, gdbm-*, glib2-*, glibc-*, glibc-common-*, gmp-*, gnupg2-*, gpgme-*, grep-*, grub-*, grubby-*, gzip-*"
uRPMH="hicolor-icon-theme-*, httpd-*, hwdata-*"
uRPMI="info-*, initscripts-*, iproute-*, iptables-*, iptables-ipv6-*, iputils-*, iscsi-initiator-utils-*"
uRPMJ="jasper-*, jython-*"
uRPMK="kbd-*, kbd-misc-*, kernel-*, kernel-firmware-*, keyutils-libs-*, kpartx-*, krb5-libs-*"
uRPML="libXi-*, libXcomposite-*, libtool-ltdl-*, libXdamage-*, libXt-*, libXext-*, libXau-*, libxcb-*, libmpfr-*, libSM-*, libpng-*, libxslt-*, libX11-*, libfreetype-*, less-*, libacl-*, libaio-*, libattr-*, libblkid-*, libcap-*, libcap-ng-*, libcgroup-*, libcom_err-*, libconfig-*, libcurl-*, libdrm-*, libedit-*, libevent-*, libffi-*, libgcc-4.4-*, libxml2-*, lldpad-*, logrotate-*, lua-*, lvm2-*, lvm2-libs-*"
uRPML2="libXrandr-*, libXcursor-*, libXfixes-*, libXxf86vm-*, libXinerama-*, libtasn-*, libXft-*, libthai*,libfontenc-*, libXfont-*, libXrender-*, libXpm-*, libexslt-*, libnl-*, libstdc++-devel-*, libICE-*, libjpeg-*, libtiff-*, libgomp-*, libgcrypt-*, libgpg-error-*, libgssglue-*, libhbaapi-*, libhbalinux-*, libidn*, libnih-*, libpciaccess-*, libselinux-*, libselinux-utils-*, libsemanage-*, libsepol-*, libss-*, libssh2-*, libstdc++-4.4-*, libtirpc-*, libudev-*, libusb-*, libuser-*, libutempter-*, libuuid-*"
uRPMM="MySQL-python-*, make-*, mlocate-*, mesa-dri-drivers-*, mesa-libGL-*, mesa-libGLU-*, mod_wsgi-*, mpfr-*, mailcap-*, mailgraph-*, mysql-*, mysql-client-*, m4-*, MAKEDEV-*, mdadm-*, mingetty-*, module-init-tools-*"
uRPMN="nano-*, netpbm-*, netncurses-*, ncurses-base-*, ncurses-libs-*, net-tools-*, newt-*, newt-python-*, nfs-utils-*, nfs-utils-lib-*, nspr-*, nss-*, nss-softokn*, nss-softokn-freebl-*, nss-sysinit-*, nss-util-*"
uRPMO="openldap-devel-2.4.19-*, openldap-2.4.19-*, openssh-*, openssh-clients-*, openssh-server-*, openssl-*"
uRPMP="pyOpenSSL-*, pyparsing-*, pkgconfig-*, pixman-*, ppl-*, procmail-*, portreserve-*, postfix-*, pyzor-*, perl-*, php-*, pam-*, passwd-*, pciutils-libs-*, pcre-*, pinentry-*, plymouth-*, plymouth-core-libs-*, plymouth-scripts-*, policycoreutils-*, popt-*, procps-*, psmisc-*, pth-*, pygpgme-*, python-*, pango-*"
uRPMQ="ql21*, ql22*, ql23*, ql24*, ql25*"
uRPMR="rabbitmq-server-*, redhat-lsb-printing-*, redhat-lsb-graphics-*, rrdtool-*, readline-*, redhat-logos-*, rootfiles-*, rpcbind-*, rpm-*, rpm-libs-*, rpm-python-*, rsyslog-*"
uRPMS="SDL-*, sqlgrey-*, spamassassin-*, sed-*, selinux-policy-*, selinux-policy-targeted-*, setup-*, shadow-utils-*, slang-*, sqlite-*, system-config-firewall-base-*, sysvinit-tools-*"
uRPMT="tnef-*, tcl-*, tk-*, transfig-*, tar-*, tcp_wrappers-libs-*, tzdata-*"
uRPMU="unixODBC-*, urw-fonts-*, udev-*, upstart-*, ustr-*, util-linux-ng-*"
uRPMV="vconfig-*, vim-minimal-*"
uRPMW="wget-*, which-*"
uRPMX="wxBase-*, wxGTK-*, xorg-x11-font-utils-*, xz-libs-*"
uRPMY="yum-*, yum-metadata-parser-*, yum-plugin-fastestmirror-*, yum-presto-*"
uRPMZ="zlib-*"

#Download all packages for i386
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMA" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMB" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMC" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMD" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPME" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMF" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMG" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMH" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMI" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMJ" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMK" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML2" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMM" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMN" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMO" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMP" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMQ" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMR" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMS" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMT" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMU" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMV" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMW" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMX" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMY" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMZ" http://mirror.centos.org/centos/6/os/i386/Packages/

#Download all packages for i386 if not available in /os/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMA" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMB" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMC" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMD" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPME" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMF" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMG" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMH" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMI" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMJ" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMK" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML2" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMM" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMN" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMO" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMP" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMQ" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMR" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMS" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMT" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMU" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMV" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMW" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMX" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMY" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMZ" http://mirror.centos.org/centos/6/cr/i386/RPMS/

#Download Enterprise Linux 6 packages for i386 http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMA" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMB" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMC" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMD" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPME" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMF" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMG" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMH" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMI" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMJ" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMK" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML2" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMM" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMN" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMO" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMP" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMQ" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMR" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMS" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMT" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMU" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMV" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMW" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMX" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMY" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMZ" http://download.fedora.redhat.com/pub/epel/6/i386/

#Download all updates for i386
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMA" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMB" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMC" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMD" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPME" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMF" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMG" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMH" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMI" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMJ" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMK" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPML2" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMM" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMN" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMO" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMP" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMQ" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMR" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMS" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMT" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMU" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMV" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMW" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMX" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMY" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPMZ" http://mirror.centos.org/centos/6/updates/i386/RPMS/




#Download all packages for x86_64
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMA" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMB" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMC" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMD" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPME" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMF" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMG" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMH" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMI" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMJ" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMK" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML2" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMM" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMN" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMO" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMP" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMQ" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMR" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMS" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMT" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMU" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMV" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMW" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMX" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMY" http://mirror.centos.org/centos/6/os/x86_64/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMZ" http://mirror.centos.org/centos/6/os/x86_64/Packages/

#Download all packages for x86_64 if not available in /os/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMA" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMB" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMC" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMD" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPME" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMF" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMG" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMH" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMI" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMJ" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMK" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML2" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMM" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMN" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMO" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMP" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMQ" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMR" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMS" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMT" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMU" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMV" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMW" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMX" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMY" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMZ" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/

#Download Enterprise Linux 6 packages for x86_64 http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMA" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMB" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMC" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMD" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPME" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMF" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMG" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMH" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMI" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMJ" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMK" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML2" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMM" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMN" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMO" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMP" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMQ" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMR" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMS" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMT" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMU" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMV" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMW" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMX" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMY" http://download.fedora.redhat.com/pub/epel/6/x86_64/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMZ" http://download.fedora.redhat.com/pub/epel/6/x86_64/

#Download all updates for x86_64
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMA" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMB" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMC" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMD" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPME" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMF" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMG" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMH" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMI" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMJ" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMK" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPML2" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMM" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMN" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMO" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMP" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMQ" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMR" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMS" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMT" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMU" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMV" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMW" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMX" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMY" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPMZ" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/


#custom rpm packages
#webmin
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -a "webmin-1.57*" http://download.webmin.com/download/yum/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -a "webmin-1.57*" http://download.webmin.com/download/yum/
#baruwa-release adds repos to baruwa for updates...breaks many things in CentOS 6 if enabled.
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ http://repo.baruwa.org/el6/i386/baruwa-release-6-0.noarch.rpm
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ http://repo.baruwa.org/el6/x86_64/baruwa-release-6-0.noarch.rpm
#baruwa
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ http://repo.baruwa.org/el6/x86_64/baruwa-1.1.1-1.el6.noarch.rpm
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ http://repo.baruwa.org/el6/x86_64/baruwa-1.1.1-1.el6.noarch.rpm
#dcc
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ http://repo.baruwa.org/el6/i386/dcc-1.3.140-1.el6.i686.rpm
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ http://repo.baruwa.org/el6/x86_64/dcc-1.3.140-1.el6.x86_64.rpm
#dcc-client
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ http://repo.baruwa.org/el6/i386/dcc-client-1.3.140-1.el6.i686.rpm
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ http://repo.baruwa.org/el6/x86_64/dcc-client-1.3.140-1.el6.x86_64.rpm
#dcc-server
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ http://repo.baruwa.org/el6/i386/dcc-server-1.3.140-1.el6.i686.rpm
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ http://repo.baruwa.org/el6/x86_64/dcc-server-1.3.140-1.el6.x86_64.rpm
#mailscanner
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ http://repo.baruwa.org/el6/i386/mailscanner-4.84.3-2.el6.noarch.rpm
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ http://repo.baruwa.org/el6/i386/mailscanner-4.84.3-2.el6.noarch.rpm



#cleanup bogus files
rm /home/emda/workspace/rpmUpdates/i386/*.htm*
rm /home/emda/workspace/rpmUpdates/i386/*.txt
rm /home/emda/workspace/rpmUpdates/i386/mirrorlist
#rm /home/emda/workspace/rpmUpdates/i386/python-dateutil-1.4*

#remove the old rpms for i386 arch in update directory
repomanage --old /home/emda/workspace/rpmUpdates/i386 | xargs rm -f

#cleanup bogus files
rm /home/emda/workspace/rpmUpdates/x86_64/*.htm*
rm /home/emda/workspace/rpmUpdates/x86_64/*.txt
rm /home/emda/workspace/rpmUpdates/x86_64/mirrorlist
rm /home/emda/workspace/rpmUpdates/x86_64/*.i686.rpm
#rm /home/emda/workspace/rpmUpdates/x86_64/python-dateutil-1.4*

#remove the old rpms for x86_64 arch in update directory
repomanage --old /home/emda/workspace/rpmUpdates/x86_64 | xargs rm -f

exit 0
