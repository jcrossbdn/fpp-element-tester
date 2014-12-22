#!/usr/bin/perl
use lib "/opt/fpp/lib/perl";
use FPP::MemoryMap;

my $name = "fpp-element-tester";

my $fppmm = new FPP::MemoryMap;

$fppmm->OpenMaps();

my $blk = $fppmm->GetBlockInfo($name);

$fppmm->SetBlockColor($blk,0,0,0);

$file = '/home/pi/media/config/plugin.fpp-element-tester.outputValues';
open (F, $file) || die ("Could not open $file!");

while ($line = <F>)
{
 ($channel,$value)=split ' = ', $line;
 $fppmm->SetChannel($channel - 1, $value);
}
close (F);

$fppmm->CloseMaps();
print ("Success");
exit(0);