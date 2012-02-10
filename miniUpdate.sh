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

#instead of having to reload the entire repoUpdate.sh script, this can be used to download a very small subset of packages
#if the packages that are listed here are needed, they should also be listed in repoUpdate.sh
#example: uRPM="MySQL-python-*, SDL-*"
uRPM="patch-*, elfutils-libs-*, file-*, gdb-*, unzip-*, xz-*, xz-lzma-compat-*"

wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPM" http://mirror.centos.org/centos/6/os/i386/Packages/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPM" http://mirror.centos.org/centos/6/os/x86_64/Packages/

wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPM" http://mirror.centos.org/centos/6/cr/i386/RPMS/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPM" http://mirror.centos.org/centos/6/cr/x86_64/RPMS/

wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPM" http://download.fedora.redhat.com/pub/epel/6/i386/
wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPM" http://download.fedora.redhat.com/pub/epel/6/x86_64/

#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/i386/ -A "$uRPM" http://mirror.centos.org/centos/6/updates/i386/RPMS/
#wget -r -nc -nv -nH -nd -l1 --no-parent -P /home/emda/workspace/rpmUpdates/x86_64/ -A "$uRPM" http://mirror.centos.org/centos/6/updates/x86_64/RPMS/

#cleanup bogus files
rm /home/emda/workspace/rpmUpdates/i386/*.htm*
rm /home/emda/workspace/rpmUpdates/i386/*.txt
rm /home/emda/workspace/rpmUpdates/i386/mirrorlist

#remove the old rpms for i386 arch in update directory
repomanage --old /home/emda/workspace/rpmUpdates/i386 | xargs rm -f

#cleanup bogus files
rm /home/emda/workspace/rpmUpdates/x86_64/*.htm*
rm /home/emda/workspace/rpmUpdates/x86_64/*.txt
rm /home/emda/workspace/rpmUpdates/x86_64/mirrorlist
rm /home/emda/workspace/rpmUpdates/x86_64/*.i686.rpm

#remove the old rpms for x86_64 arch in update directory
repomanage --old /home/emda/workspace/rpmUpdates/x86_64 | xargs rm -f

exit 0
