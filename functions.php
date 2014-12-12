<?php
$fppmm=$settings['fppDir'].'/bin/fppmm';
$confFile="fetElements.outputValues"; //$settings["configDirectory"] . "/plugin.fetElements.outputValues"; //config file for channel test values

require_once $settings['fppDir'].'/www/common.php';

/*
functions for communication with fpp

*/

function getTestMode() {
  global $fppmm;
  $cmd="$fppmm -t status";
  exec ($cmd,$output,$var);
  
  preg_match("/.*Off\..*/",$output[0],$match);
  if (count($match)) return false;
  else return true;
}

function setTestMode($state=false) { //true/false
  global $fppmm;
  if ($state) $cmd="$fppmm -t on";
  else $cmd="$fppmm -t off";
  exec ($cmd,$output,$var);
  usleep(500000);
  $cur=getTestMode();
  if ($cur && $state) return true;
  if (!$cur && !$state) return true;
  return false;
}

function setChannel($channel, $value=0) { //$channel (1-131072)    $value (0-255)
  global $fppmm;
  global $confFile;
  $cmd="$fppmm -c $channel -s $value";
  //exec($cmd,$output,$var);
  echo "Run Command: $cmd<br>";
  WriteSettingToFile($channel,$value,$confFile);  
}

//function channelStatus($oid,$color,$method="get",$value=null) {
//  $getStatus=0;
//  $colors=getColorChannels(array($oid=>true));
// if (count($colors->$color)) {
//    foreach ($colors as $oid=>$channel) {
//      if (count($channel) == 0) { //white channels (made from RGB) are 1 level depper due to duplication of the OID
//        echo "<pre>"; var_dump($channel);
//        if ($method=="get") {
//          $thisValue=ReadSettingFromFile("$channel.$color",$confFile);
//          if ($thisValue > 0) $getStatus=$thisValue;
//        }
//        if ($method=="set") {
//          $thisValue=ReadSettingFromFile("$channel.$color",$confFile);
//          if ($thisValue != $value) WriteSettingToFile("$channel.$color",$value,$confFile);
//        }
//      }
//      else {
//        foreach ($channel as $channelD) {
//          if ($method=="get") {
//            $thisValue=ReadSettingFromFile("$channelD.$color",$confFile);
//            if ($thisValue > 0) $getStatus=$thisValue;
//          }
//          if ($method=="set") {
//            $thisValue=ReadSettingFromFile("$channelD.$color",$confFile);
//            if ($thisValue != $value) WriteSettingToFile("$channelD.$color",$value,$confFile);
//          }
//        }
//      }
//    }
//  }
  //echo "<pre>"; var_dump($oid); var_dump($colors); exit;
  
//  if ($method=="get") return ReadSettingFromFile("$oid.$color",$confFile);
//  else {
//    WriteSettingToFile("$oid.$color", $value, $confFile);
//    if (ReadSettingFromFile("$oid.$color",$confFile) == $value) return true;
//    else return false;
//  }
//}





function alphanumeric($string) {
  return preg_replace("/[A-Z][a-z][0-9]/","",$string);
}
  
function setNodeColors($oidColorObj, $color, $value) { //set color values for an element or group of elements
  if (count($oidColorObj->$color)) {
    foreach ($oidColorObj->$color as $oid=>$channel) {
      if (count($channel) == 0) setChannel($channel,$value); //white channels (made from RGB) are 1 level deeper due to duplication of the OID
      else {
        foreach ($channel as $channelD) {
          setChannel($channelD, $value);
        }
      }
    }
  }
} 

function hex2rgb($hex) { //taken from: http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}
function rgb2hex($rgb) { //taken from: http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
   $hex = "#";
   $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

   return $hex; // returns the hex value including the number sign (#)
}
  
  
/*
functions for working with xml
*/  
function getColorChannels($oidArr) { //creates a list of colors and associated physical output channels
  global $xml;
  $color->red=false; //set the color order
  $color->green=false;
  $color->blue=false;
  $color->white=false;
  foreach ($oidArr as $oid=>$value) {
    $ooid=$xml->xpath("physical/outputs/output[@oid='$oid']");
    if (count($ooid)) {
      if (isset($ooid[0]['r'])) $color->red->$oid=$ooid[0]['r'];
      if (isset($ooid[0]['g'])) $color->green->$oid=$ooid[0]['g'];
      if (isset($ooid[0]['b'])) $color->blue->$oid=$ooid[0]['b'];
      if (isset($ooid[0]['w'])) $color->white->$oid=$ooid[0]['w'];
      if (isset($ooid[0]['r']) && isset($ooid[0]['g']) && isset($ooid[0]['b'])) { //for RGB channels assign them all to the white group
        $color->white->$oid=(object)array($ooid[0]['r'],$ooid[0]['g'],$ooid[0]['b']);
      }
    }
  }
  return $color;
}

function getChannelNames($oidArr) { //creates a list of output names
  global $xml;
  foreach ($oidArr as $oid=>$value) {
    $ooid=$xml->xpath("physical/outputs/output[@oid='$oid']");
    if (count($ooid)) {
      $names[$oid]=$ooid[0]['name'];
    }
  }
  return $names;
}

function showColorButton($color, $path, $name, $oidArr, $aName=false, $selectedOID=0) {
  global $pluginBaseURL;
  $checked='';
  if (count($oidArr)) {
    foreach ($oidArr as $oid=>$value) {
      //if (intval(ReadSettingFromFile("$oid.$color",$settings["configDirectory"] . "/plugin.fetElements.outputValues")) > 0) $checked=' checked';
      //if (intval(channelStatus($oid,$color)) > 0) $checked=' checked'; 
    }
  }
  return "<div class='switch switch$color'><input type='checkbox' onclick='javascript:getElements(\"\&fetPath=$path&fetName=$name&fetColor=$color\&fetValue=".($checked ? "0" : "255").($selectedOID > 0 ? "\&fetoid=$selectedOID" : "").($aName === false ? "" : "#$aName")."\"); return false;' $checked><label></label></div>";
}


$oidAssign="";  
function RecurseXML($xml, $parent, $mustBeFalse=false) //mustBeFalse is used to prevent $oidAssign variable clearing when recursing. Do not set this variable to anything but false
{ //taken from php.net
   global $oidAssign;
   if (!$mustBeFalse) $oidAssign="";
   
   $child_count = 0;
   foreach($xml as $key=>$value)
   {
      $child_count++;    
      $recurse=RecurseXML($value, $parent."/".$key, $oidAssign);
      if($recurse['count'] == 0)  // no childern, aka "leaf node"
      {
        if (isset($value['oid']))  {
          $oids=explode(",",$value['oid']);
          foreach ($oids as $oid) {
            $oidAssign[$oid]=true;
          }
        }       
      }
   }
   return array('count'=>$child_count, 'oids'=>$oidAssign);
}   
?>
