#!/usr/bin/perl
$| = 1; # Turn off I/O buffering
use Digest::MD5 qw(md5 md5_hex md5_base64);
$str = <STDIN>;
chomp($str);
$digest = md5_hex($str);
print "$digest\n";