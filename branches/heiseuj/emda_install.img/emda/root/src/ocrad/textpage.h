/*  GNU Ocrad - Optical Character Recognition program
    Copyright (C) 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011
    Antonio Diaz Diaz.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Textblock;

class Textpage : public Rectangle
  {
  const std::string name;
  std::vector< Textblock * > tbpv;

public:
  Textpage( const Page_image & page_image, const char * const filename,
            const Control & control, const bool layout );
  ~Textpage() throw();

  const Textblock & textblock( const int i ) const throw();
  int textblocks() const throw() { return tbpv.size(); }
  int textlines() const throw();
  int characters() const throw();

  void print( const Control & control ) const throw();
  void xprint( const Control & control ) const throw();
  };
