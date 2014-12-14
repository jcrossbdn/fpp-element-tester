<?
//error_reporting(E_ALL);
//ini_set('display_errors', true);

include 'functions.php';
$xml=simplexml_load_file('/home/pi/media/upload/v3Elements.xml');
global $confFile;
$groupBase="groups";
$pluginBaseURL="";
$path="";

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
$breadCrumb="<pre><a href='#' onclick=\"getElements('$pluginBaseURL&fetPath='); return false;\">Home </a>/";
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


if (isset($_GET['setTestMode'])) {
  if ($_GET['setTestMode'] == "on")  {
    if (setTestMode(true))  {
      $jGrowl[]="Turned on Test Mode";
    }      
  }    
  else {
    if (setTestMode(false))  {
      $jGrowl[]="Turned Off Test Mode";
    }
  }          
}



if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName']) && $_GET['fetName']=="-fetAll-" && getTestMode()) { //process All Channels click before groups/element display
  $all=$xml->xpath("physical/outputs");
  if (count($all)) {
    foreach ($all[0]->output as $attribs) {
      $oids[(string)$attribs['oid']]=true;
    }
    $colors=getColorChannels($oids);  
    setNodeColors($colors, $_GET['fetColor'], $_GET['fetValue']);
  }
  $jGrowl[]="Set All Outputs ({$_GET['fetColor']} channels) to {$_GET['fetValue']}";
}


$outItems="";
foreach ($curLevel as $key1=>$data1) {
  if (count($data1) == 0 && isset($data1['name'])) { //display the individual channels assigned to specified element
    //gather oids and then list all channels
    $oids=explode(",",$data1['oid']);
    foreach ($oids as $oid) {
      $oidElements['oids'][$oid]=true;
    }
    $colors=getColorChannels($oidElements['oids']);
    $names=getChannelNames($oidElements['oids']);
    foreach ($names as $oid=>$name) {
      if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName']) && $_GET['fetName']==$name && getTestMode()) {
        $elLevel=$xml->xpath("physical/outputs/output[@name='{$_GET['fetName']}']");
        $oid=(string) $elLevel[0]['oid'];
        
        $colorSet=getColorChannels(array($oid=>true));
        //$ch=(string) $colorSet->{$_GET['fetColor']}->$oid;
        
        //WriteSettingArrToFile(array($ch=>$_GET['fetValue']), $confFile);
        setNodeColors($colorSet, $_GET['fetColor'], $_GET['fetValue']);
        
        $jGrowl[]="Set {$_GET['fetName']} ({$_GET['fetColor']} channel) to {$_GET['fetValue']}";
      } 
      $outItems.="<tr><td><a name='".alphanumeric($name)."'></a>$name</td>";
      if (isset($useOid)) unset($useOid);
      $useOid[$oid]=true;
      if ($colors) {
        foreach ($colors as $color=>$oid) {
          if ($colors->$color) $outItems.="<td>".showColorButton($color, $path, $name, $useOid, alphanumeric($name))."</td>"; else $outItems.="<td></td>";
        }
      }
     $outItems.= "</tr>";
    }
    unset($names);
    unset($oidElements);
    unset($colors);
  }
  else { //this is in a group, recurse it
    foreach ($data1 as $key2=>$data2) {
      if ($key2=="element")  { //if we find an element in this group then work with it here
        $oids=explode(",",$data2['oid']);
        foreach ($oids as $oid) {
          $oidElements['oids'][$oid]=true;
        }
      }
      else {
        $oidElements=RecurseXML($data2);
      }
      $thisPath=ltrim("$path".($key2=="element" ? "{$data2['name']}" : "{$data2['name']}/"),"/");
      $outItems.="<tr><td><a name='".alphanumeric($data2['name'])."'></a>";
      $outItems.="<a href='#' onclick=\"getElements('$pluginBaseURL&fetPath=$thisPath'); return false;\">{$data2['name']}</a></td>";
      $colors=getColorChannels($oidElements['oids']);
      if (isset($_GET['fetColor']) && isset($_GET['fetValue']) && isset($_GET['fetName']) && $_GET['fetName']==$data2['name'] && getTestMode()) { //process change on selected channel if it is identified
        $geLevel=$xml->xpath($groupBase."/group[@name='{$_GET['fetName']}']");
        $selectedBranch=RecurseXML($geLevel);
        
        if ($selectedBranch['count'] == 0)  {
          $geLevel=$xml->xpath($groupBase."/element[@name='{$_GET['fetName']}']");
          $selectedBranch=RecurseXML($geLevel);
        }
        if ($selectedBranch['count'] > 0)  {
          $colorSet=getColorChannels($selectedBranch['oids']);
          setNodeColors($colorSet, $_GET['fetColor'], $_GET['fetValue']);
        }
        $jGrowl[]="Set {$_GET['fetName']} ({$_GET['fetColor']} channels) to {$_GET['fetValue']}";
      }
      if ($colors) {
        foreach ($colors as $color=>$oid) {
          if ($colors->$color) $outItems.="<td>".showColorButton($color, $path, $data2['name'], $oidElements['oids'],alphanumeric($data2['name']))."</td>"; else $outItems.="<td></td>";
        }
      }
      $outItems.="</tr>";
      unset($oidElements);
      unset($oids);
      unset($colors);
      unset($colorSet);
      unset($geLevel);
      unset($selectedBranch);
    }
  }
}

unset($data1);
unset($data2);


if (isOutputValueFileEmpty() && getTestMode()) {
  if (setTestMode(false)) 
    $jGrowl[]="Turned off Test Mode";
  else
    $jGrowl[]="Could not turn off Test Mode";
}

$testMode=getTestMode();
if ($testMode) $out.="<br>Test Mode On: <a href='#' onclick=\"getElements('$pluginBaseURL&fetPath=&setTestMode=off'); return false;\">Turn off</a>";
else $out.="<br>Test Mode Off: <a href='#' onclick=\"getElements('$pluginBaseURL&fetPath=&setTestMode=on'); return false;\">Turn on</a>";


$out.="<br><br><table border=0>";

//All Channels  (All Channels click is handled above the standard element display ($colorSet)
if ($_GET['fetPath']=="") {
  $all=$xml->xpath("physical/outputs");
  if (count($all)) {
    foreach ($all[0]->output as $attribs) {
      $oids[(string)$attribs['oid']]=true;
    }
    $colors=getColorChannels($oids);
    
    $out.="<tr><td>All Channels</td>";
    if ($colors) {
      foreach ($colors as $color=>$oid) {
        if ($colors->$color) $out.="<td>".showColorButton($color, $path, "-fetAll-", $oids, "")."</td>"; else $out.="<td></td>";
      }
    }
    $out.="</tr>";
  }
  unset($all);
  unset($oids);
  unset($colors);
}
unset($xml);

$out.=$outItems;
unset($outItems);

$out.="</table>";

$out.="<br><br>$breadCrumb";

$out.="<script type=\"text/javascript\" id=\"fetGrowlMessage\">";
if (count($jGrowl)) {
  //$jGrowl=array_keys(array_flip($jGrowl));
  foreach ($jGrowl as $msg) {
    $out.="  $.jGrowl(\"$msg\");";
  }
}
$out.="</script>";

echo $out;
unset($out);
?>
