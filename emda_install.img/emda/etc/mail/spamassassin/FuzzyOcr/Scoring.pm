use strict;
package FuzzyOcr::Scoring;

use base 'Exporter';
our @EXPORT_OK = qw(wrong_ctype corrupt_img known_img_hash wrong_extension);

use lib qw(..);
use FuzzyOcr::Config qw(get_pms get_config);
use FuzzyOcr::Logging qw(infolog);

# Provide custom scoring functions

sub wrong_ctype {
    my $conf = get_config();
    my $pms = get_pms();
    my ( $format, $ctype ) = @_;
    if ($conf->{'focr_wrongctype_score'}) {
        my $debuginfo = "";
        if ( $conf->{"focr_verbose"} > 0 ) {
            $debuginfo = 
              ("Image has format \"$format\" but content-type is \"$ctype\"");
        }
        infolog($debuginfo);
        my $ws = sprintf( "%0.3f", $conf->{'focr_wrongctype_score'} );
        for my $set ( 0 .. 3 ) {
            $pms->{conf}->{scoreset}->[$set]->{"FUZZY_OCR_WRONG_CTYPE"} = $ws;
        }
        $pms->_handle_hit( "FUZZY_OCR_WRONG_CTYPE",
            $conf->{'focr_wrongctype_score'}, "BODY: ",
            $pms->{conf}->{descriptions}->{FUZZY_OCR_WRONG_CTYPE} . "\n$debuginfo" );
    }
}

sub wrong_extension {
    my $conf = get_config();
    my $pms = get_pms();
    my ( $format, $suffix ) = @_;
    if ($conf->{'focr_wrongext_score'}) {
        my $debuginfo = "";
        if ( $conf->{"focr_verbose"} > 0 ) {
            $debuginfo = 
              ("Image has format \"$format\" but file extension is \"$suffix\"");
        }
        infolog($debuginfo);
        my $ws = sprintf( "%0.3f", $conf->{'focr_wrongext_score'} );
        for my $set ( 0 .. 3 ) {
            $pms->{conf}->{scoreset}->[$set]->{"FUZZY_OCR_WRONG_EXTENSION"} = $ws;
        }
        $pms->_handle_hit( "FUZZY_OCR_WRONG_EXTENSION",
            $conf->{'focr_wrongext_score'}, "BODY: ",
            $pms->{conf}->{descriptions}->{FUZZY_OCR_WRONG_EXTENSION} . "\n$debuginfo" );
    }
}

sub corrupt_img {
    my $conf = get_config();
    my $pms = get_pms();
    my ($score, $err) = @_;
    if ($score>0) {
        my $debuginfo = "";
        if ( $conf->{"focr_verbose"} > 0 ) {
            chomp($err);
            $debuginfo = ("Corrupt image: $err");
        }
        infolog($debuginfo);
        my $ws = sprintf( "%0.3f", $score );
        for my $set ( 0 .. 3 ) {
            $pms->{conf}->{scoreset}->[$set]->{"FUZZY_OCR_CORRUPT_IMG"} = $ws;
        }
        $pms->_handle_hit( "FUZZY_OCR_CORRUPT_IMG", $score, "BODY: ",
            $pms->{conf}->{descriptions}->{FUZZY_OCR_CORRUPT_IMG} . "\n$debuginfo" );
    }
}

sub known_img_hash {
    my $conf = get_config();
    my $pms = get_pms();
    my $score = $_[0] || $conf->{'focr_base_score'};
    my $dinfo = $_[1] ? "\n$_[1]" : '';
    my $ws = sprintf( "%0.3f", $score );
    for my $set ( 0 .. 3 ) {
        $pms->{conf}->{scoreset}->[$set]->{"FUZZY_OCR_KNOWN_HASH"} = $ws;
    }
    $pms->_handle_hit( "FUZZY_OCR_KNOWN_HASH", $score, "BODY: ",
        $pms->{conf}->{descriptions}->{FUZZY_OCR_KNOWN_HASH} . $dinfo );
}

1;
