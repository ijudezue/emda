#!/bin/bash

#update the rpms in the working i386 build dirctory
rsync --archive /home/emda/workspace/rpmUpdates/i386/ /home/emda/workspace/builds/EMDA-1.0-i386/Packages/

#remove the old rpms for i386 arch in build directory
repomanage --old /home/emda/workspace/builds/EMDA-1.0-i386/Packages | xargs rm -f

#remove old data from i386 repodata directory
rm -fr /home/emda/workspace/builds/EMDA-1.0-i386/repodata/*

#copy comps.xml into i386 repodata directory
rsync --archive /home/emda/workspace/src/repodata/comps.xml /home/emda/workspace/builds/EMDA-1.0-i386/repodata/

#create the repo tree for i386
#createrepo -g /home/emda/workspace/builds/EMDA-1.0-i386/repodata/comps.xml /home/emda/workspace/builds/EMDA-1.0-i386/.
cd /home/emda/workspace/builds/EMDA-1.0-i386
createrepo -g repodata/comps.xml .

#update the rpms in the working x86_64 build dirctory
rsync --archive /home/emda/workspace/rpmUpdates/x86_64/ /home/emda/workspace/builds/EMDA-1.0-x86_64/Packages/

#remove the old rpms for x86_64 arch in build directory
repomanage --old /home/emda/workspace/builds/EMDA-1.0-x86_64/Packages | xargs rm -f

#remove old data from x86_64 repodata directory
rm -fr /home/emda/workspace/builds/EMDA-1.0-x86_64/repodata/*

#copy comps.xml into x86_64 repodata directory
rsync --archive /home/emda/workspace/src/repodata/comps.xml /home/emda/workspace/builds/EMDA-1.0-x86_64/repodata/

#create the repo tree for x86_64
#createrepo -g /home/emda/workspace/builds/EMDA-1.0-x86_64/repodata/comps.xml /home/emda/workspace/builds/EMDA-1.0-x86_64/.
cd /home/emda/workspace/builds/EMDA-1.0-x86_64
createrepo -g repodata/comps.xml .

exit 0
