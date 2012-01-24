#!/bin/bash

emdaVer="`cat /home/emda/workspace/src/trunk/version`"
echo $emdaVer > /home/emda/workspace/src/trunk/emda_install.img/emda/etc/emdaVersion
#create these symlinks since eclipse/svn doesn't like keeping symlinks 
ls -sf /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/fpdf152 /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/fpdf
ls -sf /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/jpgraph-1.12.1 /home/emda/workspace/src/trunk/emda_install.img/emda/var/www/html/jpgraph 

#make i386 EMDA $emdaVer
rsync -ar /home/emda/workspace/src/trunk/emda_install.img/emda /home/emda/workspace/src/trunk/emda_install.img/i386/
rsync --archive /home/emda/workspace/src/trunk/repodata/comps.xml /home/emda/workspace/builds/EMDA-i386/repodata/
mksquashfs /home/emda/workspace/src/trunk/emda_install.img/i386 /home/emda/workspace/builds/EMDA-i386/images/install.img -noappend -no-sparse -no-recovery -info -no-fragments -no-duplicates
rsync -ar /home/emda/workspace/src/trunk/isolinux/* /home/emda/workspace/builds/EMDA-i386/isolinux/;
sed -i "/menu title Welcome to EMDA ver arch Bit!/ c\menu title Welcome to EMDA $emdaVer 32 Bit!" /home/emda/workspace/builds/EMDA-i386/isolinux/isolinux.cfg
sed -i "/  menu label ^Install EMDA ver arch/ c\  menu label ^Install EMDA $emdaVer i386" /home/emda/workspace/builds/EMDA-i386/isolinux/isolinux.cfg
rsync -ar /home/emda/workspace/src/trunk/repodata/* /home/emda/workspace/builds/EMDA-i386/repodata/;
rsync -ar /home/emda/workspace/src/trunk/Packages/* /home/emda/workspace/builds/EMDA-i386/Packages/;
#create the repo tree for i386
cd /home/emda/workspace/builds/EMDA-i386
createrepo -g repodata/comps.xml .
mkisofs -r -R -J -T -v -no-emul-boot -boot-load-size 4 -boot-info-table -V "EMDA $emdaVer i386" -p "Team EMDA" -A "EMDA $emdaVer i386" -b isolinux/isolinux.bin -c isolinux/boot.cat -x "lost+found" -x "makeISO.*" -x "~*" -x "*~" -x "*.sh" -o /home/emda/workspace/iso/EMDA-$emdaVer-i386.iso .;
#cleanup
rm -fr /home/emda/workspace/src/trunk/emda_install.img/i386/emda
rm -fr /home/emda/workspace/builds/EMDA-i386/isolinux/isolinux.cfg
rm -fr /home/emda/workspace/builds/EMDA-i386/install.img
rm -fr /home/emda/workspace/builds/EMDA-i386/repodata/*

#make x86_64 EMDA $emdaVer
rsync -ar /home/emda/workspace/src/trunk/emda_install.img/emda /home/emda/workspace/src/trunk/emda_install.img/x86_64/
rsync --archive /home/emda/workspace/src/trunk/repodata/comps.xml /home/emda/workspace/builds/EMDA-x86_64/repodata/
mksquashfs /home/emda/workspace/src/trunk/emda_install.img/x86_64 /home/emda/workspace/builds/EMDA-x86_64/images/install.img -noappend -no-sparse -no-recovery -info -no-fragments -no-duplicates
rsync -ar /home/emda/workspace/src/trunk/isolinux/* /home/emda/workspace/builds/EMDA-x86_64/isolinux/;
sed -i "/menu title Welcome to EMDA ver arch Bit!/ c\menu title Welcome to EMDA $emdaVer 64 Bit!" /home/emda/workspace/builds/EMDA-x86_64/isolinux/isolinux.cfg
sed -i "/  menu label ^Install EMDA ver arch/ c\  menu label ^Install EMDA $emdaVer x86_64" /home/emda/workspace/builds/EMDA-x86_64/isolinux/isolinux.cfg
rsync -ar /home/emda/workspace/src/trunk/repodata/* /home/emda/workspace/builds/EMDA-x86_64/repodata/;
rsync -ar /home/emda/workspace/src/trunk/Packages/* /home/emda/workspace/builds/EMDA-x86_64/Packages/;
#create the repo tree for x86_64
cd /home/emda/workspace/builds/EMDA-x86_64
createrepo -g repodata/comps.xml .
mkisofs -r -R -J -T -v -no-emul-boot -boot-load-size 4 -boot-info-table -V "EMDA $emdaVer x86_64" -p "Team EMDA" -A "EMDA $emdaVer x86_64" -b isolinux/isolinux.bin -c isolinux/boot.cat -x "lost+found" -x "makeISO.*" -x "~*" -x "*~" -x "*.sh" -o /home/emda/workspace/iso/EMDA-$emdaVer-x86_64.iso .;
#cleanup
rm -fr /home/emda/workspace/src/trunk/emda_install.img/x86_64/emda
rm -fr /home/emda/workspace/builds/EMDA-x86_64/isolinux/isolinux.cfg
rm -fr /home/emda/workspace/builds/EMDA-x86_64/install.img
rm -fr /home/emda/workspace/builds/EMDA-x86_64/repodata/*

rm /home/emda/workspace/src/trunk/emda_install.img/emda/etc/emdaVersion

exit 0;
