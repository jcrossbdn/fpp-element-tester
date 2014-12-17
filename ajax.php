<?
//error_reporting(E_ERROR);
//ini_set('display_errors', true);

include 'functions.php';
$xmlFile=ReadSettingFromFile("ConfigFileXML", $_GET['plugin']);
if ($xmlFile!==false) {
  $xml=simplexml_load_file($xmlFile);
}
else {
  echo "Please upload your Displays configuration file (in CSV format) in the Element Configuration Section above to use this plugin.";
  exit;
}
if ($xml===false)  {
  echo "ERROR: Malformed Plugin Configuration File.";
  echo "<br><i>$xmlFile</i>";
  //echo "<br><br>Error Details:<br>";
  //echo "<pre>"; var_dump(libxml_get_errors()); var_dump(libxml_get_last_error()); echo "</pre><hr>";
  //showAllErrors(libxml_get_errors(),file_get_contents("/home/pi/media/upload/Channel Assignment.xml"));
  exit;
}

global $confFile;
$groupBase="groups";
$pluginBaseURL="";
$path="";
$colorArray=array("Red"=>"r","Green"=>"g","Blue"=>"b","White"=>"w");
  
if (isset($_GET['fetPath']) && trim($_GET['fetPath']) != '') {
  $pathArr=explode("/",$_GET['fetPath']);
  if ($pathArr[0] != '') {
    for ($inta=0; $inta < count($pathArr)-1; $inta++) {
      $groupBase.="/group[@name='{$pathArr[$inta]}']";
      $path.="{$pathArr[$inta]}/";
    }
    if (trim($pathArr[count($pathArr)-1]) != "")  {
      $groupBase.="/element[@name='{$pathArr[count($pathArr)-1]}']";
      $path.="{$pathArr[count($pathArr)-1]}";
    }
  }
  else { //this string only contains an element
    $groupBase.="/element[@name='{$pathArr[count($pathArr)-1]}']";
    $path="/{$pathArr[count($pathArr)-1]}";
  }
}
$path=ltrim($path,"/");
$curLevel=$xml->xpath($groupBase);
/*
fetPath:
Arches/  = group named arches
All Elements/Arches/  = group named Arches in All Elements
Arches/Arch 1  = element named Arch 1 in group Arches

*** everything before the last / is a group and the value after the last / is an element
*/

$pathArr=explode("/",$path);
$breadCrumb="<pre><a href='#' onclick=\"getElements('$pluginBaseURL&fetPath='); return false;\"> Home </a>/";
if (count($pathArr)>0) {
  foreach ($pathArr as $index=>$pathStr) {
    $breadCrumb.="<a href='#' onclick=\"getElements('$pluginBaseURL&fetPath=";
    for ($inta=0; $inta<=$index; $inta++)  {
      $breadCrumb.="$pathArr[$inta]";
      if ($inta < count($pathArr)-1) $breadCrumb.="/";
    }
    if (trim($pathStr) != '') $breadCrumb.="'); return false;\"> $pathStr </a>/"; else $breadCrumb.="')\"></a>";
  }
}
$breadCrumb.="</pre>";
$out.=$breadCrumb;

//If an ON/OFF command has been sent then ensure we are in test mode
if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName'])) {
  if (getTestMode()===false) {
    if (setTestMode(true)===false) {
      $jGrowl[]="ERROR: Could not turn on Test Mode";
    }
    else {
      $jGrowl[]="Turned on Test Mode";
    }
  }          
}

//process "All Channels" click before groups/element display to ensure proper button refresh
if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName']) && $_GET['fetName']=="-fetAll-" && getTestMode()) {
  $all=$xml->xpath("allChannels/{$_GET['fetColor']}[@ch]");
  if (count($all)) {
    if (isset($all[0]['ch'])) setNodeColors((string)$all[0]['ch'],$_GET['fetValue']);
    $jGrowl[]="Set All Channels ({$_GET['fetColor']} channels) to {$_GET['fetValue']}";
  }
  unset($all);
}


//Display and process all groups/elements at current path
$outItems="";
foreach ($curLevel as $key1=>$data1) {
  if (count($data1) == 0 && isset($data1['name'])) { //display the individual channels assigned to specified element
    $detail=getOutputDetail((string)$data1['oid']);
    foreach ($detail as $output) {
      if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName']) && $_GET['fetName']==$output['name'] && getTestMode()) {
        $short=$colorArray[$_GET['fetColor']];
        if ($short != '' && isset($output[$short])) setNodeColors((string)$output[$short],$_GET['fetValue']);
        unset($short);
        $jGrowl[]="Set {$_GET['fetName']} ({$_GET['fetColor']} channels) to {$_GET['fetValue']}";
      }
      $outItems.="<tr><td><a name='".alphanumeric((string)$output['name'])."'></a>{$output['name']}</td>";
      foreach ($colorArray as $long=>$short) {
        if (isset($output[$short])) $outItems.="<td>".showGroupColorButton($long, $path, $output['name'], getGroupChannelStatus((string)$output[$short],$long), alphanumeric($output['name']))."</td>";
        else $outItems.="<td></td>";
      }
    }
  }
  else { //this is in a group, recurse it
    $count=0;
    foreach ($data1 as $key2=>$data2) {
      $count++;
      $thisPath=ltrim("$path".($key2=="element" ? "{$data2['name']}" : "{$data2['name']}/"),"/");
      $outItems.="<tr><td><a name='".alphanumeric($data2['name'])."'></a>";
      $outItems.="<a href='#' onclick=\"getElements('$pluginBaseURL&fetPath=$thisPath'); return false;\">{$data2['name']}</a></td>";
      
      if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName']) && $_GET['fetName']==$data2['name'] && getTestMode()) { //process change on selected channel if it is identified
        $geLevel=$xml->xpath($groupBase."/group[@name='{$_GET['fetName']}']");
        if (!count($geLevel)) 
          $geLevel=$xml->xpath($groupBase."/element[@name='{$_GET['fetName']}']");
          
        if (count($geLevel)) {
          $short=$colorArray[$_GET['fetColor']];
          if ($short != '' && isset($geLevel[0][$short])) setNodeColors((string)$geLevel[0][$short],$_GET['fetValue']);
          unset($short);
          
          $jGrowl[]="Set {$_GET['fetName']} ({$_GET['fetColor']} channels) to {$_GET['fetValue']}";
        }
      }
      
      foreach ($colorArray as $long=>$short) {
        if (isset($data2[$short])) $outItems.="<td>".showGroupColorButton($long, $path, $data2['name'], getGroupChannelStatus((string)$data2[$short],$long), alphanumeric($output['name']))."</td>";
        else $outItems.="<td></td>";
      }

      $outItems.="</tr>";
      unset($geLevel);
    }
    unset($data2);
  }
}

unset($data1);


if (isOutputValueFileEmpty() && getTestMode()) {
  if (setTestMode(false)) 
    $jGrowl[]="Turned off Test Mode";
  else
    $jGrowl[]="Could not turn off Test Mode";
}

$out.="<br><br><table border=0>";

//All Channels Display (All Channels CLICK events are handled above the standard element display ($colorSet)
if ($_GET['fetPath']=="") {
  $out.="<tr><td>All Channels</td>";
  $colors=array("Red"=>"r","Green"=>"g","Blue"=>"b","White"=>"w");
  
  foreach ($colors as $long=>$short) {
    $all=$xml->xpath("allChannels/{$long}[@ch]");
    if (count($all)) {
      if (isset($all[0]['ch'])) $out.="<td>".showGroupColorButton($long, $path, "-fetAll-", getGroupChannelStatus((string)$all[0]['ch'],$long))."</td>";
      else $out.="<td></td>";
    }
  }
}

unset($xml);

$out.=$outItems;
unset($outItems);

$out.="</table>";

$out.="<br><br>$breadCrumb";

$out.="<script type=\"text/javascript\" id=\"fetGrowlMessage\">";
if (count($jGrowl)) {
  foreach ($jGrowl as $msg) {
    $out.="  $.jGrowl(\"$msg\");";
  }
}
$out.="</script>";

echo $out;
unset($out);
?>