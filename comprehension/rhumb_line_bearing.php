<?php
$debug = TRUE;
$latA = 32.517225; //32 31 2.01N
$lonA = -92.324982; //92 19 29.9352W
$latB = 29.806799; //29 48 24.4764N
$lonB = -82.513139; //82 30 47.2998W
$latC = 32.623054; //32 37 22.9944N
$lonC = -93.282809; //93 16 58.1124W

$ab_bearing = getRhumbLineBearing($latA, $lonA, $latB, $lonB); //validated as 107°53′38″
$ba_bearing = $ab_bearing < 180 ? $ab_bearing + 180 : $ab_bearing - 180; //validated as 287°53′38″ 
$bc_bearing = getRhumbLineBearing($latB, $lonB, $latC, $lonC); //validated as 287°00′16″
if($debug)
{
print "A->B Bearing: ".$ab_bearing." deg\n";
print "B->A Bearing: ".$ba_bearing." deg\n";
print "B->C Bearing: ".$bc_bearing." deg\n";
}


//calc ABC angle in degres
if($ba_bearing - $bc_bearing > 0)
{
    $abc_angle = $ba_bearing - $bc_bearing;
}
else
{
    $abc_angle = $bc_bearing - $ba_bearing;
}

if($debug)
{
	print "ABC Angle: $abc_angle\n";
}

function getRhumbLineBearing($lat1, $lon1, $lat2, $lon2) {
     //difference in longitudinal coordinates
     $dLon = deg2rad($lon2) - deg2rad($lon1);
 
     //difference in the phi of latitudinal coordinates
     $dPhi = log(tan(deg2rad($lat2) / 2 + pi() / 4) / tan(deg2rad($lat1) / 2 + pi() / 4));
 
     //we need to recalculate $dLon if it is greater than pi
     if(abs($dLon) > pi()) {
          if($dLon > 0) {
               $dLon = (2 * pi() - $dLon) * -1;
          }
          else {
               $dLon = 2 * pi() + $dLon;
          }
     }
     //return the angle, normalized
     //return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
     $rlb = rad2deg(atan2($dLon, $dPhi)) + 360;
     if($rlb > 360)
     {
     	$rlb = $rlb - 360;
     }
     return $rlb;
}


// function getRhumbLineBearing($lat1, $lon1, $lat2, $lon2) {
//      //difference in longitudinal coordinates
//      $dLon = deg2rad($lon2) - deg2rad($lon1);
//      //difference in the phi of latitudinal coordinates
//      $dPhi = log(tan(deg2rad($lat2) / 2 + pi() / 4) / tan(deg2rad($lat1) / 2 + pi() / 4));
//      //we need to recalculate $dLon if it is greater than pi
//      if(abs($dLon) > pi()) {
//           if($dLon > 0) {
//                $dLon = (2 * pi() - $dLon) * -1;
//           }
//           else {
//                $dLon = 2 * pi() + $dLon;
//           }
//      }
//      //return the angle, normalized
//      return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
// }
?>