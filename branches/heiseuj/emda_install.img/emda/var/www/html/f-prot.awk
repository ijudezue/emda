#
# MailWatch for MailScanner
#  Copyright (C) 2003-2011  Steve Freegard (steve@freegard.name)
#  Copyright (C) 2011  Garrod Alwood (garrod.alwood@lorodoes.com)
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# 2005-10-07
# F-Prot status by Hubert Nachbaur modified from F-Secure status by Steve Freegard

BEGIN {
 FS = ": ";
 print "<TABLE CLASS=\"SOPHOS\" CELLPADDING=1 CELLSPACING=1>";
 print " <THEAD>";
 print "  <TH COLSPAN=4>F-Prot Information</TH>";
 print " </THEAD>";
}

/Program version/||/Engine version/ {
  print " <TR><TD>"$1":</TD><TD COLSPAN=3>"$2"</TD></TR>";
}

/VIRUS SIGNATURE FILES/ {
  print " <TR>";
  print "  <TH>File</TH>";
  print "  <TH>Date</TH>";
  print " </TR>";
}

/DEF/ {
  split($1, array, " ");
  v_name = array[1];
  v_date = array[3] " " array[4] " " array[5];
  print " <TR>";
  print "  <TD>"v_name"</TD>";
  print "  <TD>"v_date"</TD>";
  print " </TR>";
}

END { print "</TABLE>" }

