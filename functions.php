<?php
$fppmm=$settings['fppDir'].'/bin.pi/fppmm';
$confFile=$_GET['plugin'].".outputValues"; //config file for channel test values
$memMapStatus=$settings['configDirectory']."/plugin.{$_GET['plugin']}.memoryMapStatus";

require_once $settings['fppDir'].'/www/common.php';


function getTestMode() {
  global $fppmm;
  //$cmd="$fppmm -m {$_GET['plugin']} -t status";
  $cmd="$fppmm -t status";
  //echo $cmd."<br>";
  exec ($cmd,$output,$var);
  //preg_match("/Status.*/",$output[2],$match);
  //if (strstr($match[0],"Test mode is currently On.")!==false) return true;
  //elseif (strstr($match[0],"Test mode is currently Off.")!==false) return false;
  if (strstr($output[0],"Test mode is currently On.")!==false) return true;
  elseif (strstr($output[0],"Test mode is currently Off.")!==false) return false;
  else return false;
}

function setTestMode($state=false) { //true/false
  global $fppmm;
  global $memMapStatus;
  global $settings;
  
  if ($state===true) { //get currently active models and save so we can turn them back on later
    $mmf=file_get_contents('/home/fpp/media/channelmemorymaps');
    $map=false;
    if (trim($mmf) != '') {
      $lines=explode("\n",$mmf);
      foreach ($lines as $cols) {
        $output=false;
        if (trim($cols) != '') {
          $col=explode(",",$cols);
          $cmd="$fppmm -m {$col[0]}";
          exec($cmd,$output,$var);
          if (strstr($output[2],": Idle")!==false) $map[$col[0]]=false;
          else  {
            $map[$col[0]]=true;
            $cmd="$fppmm -m {$col[0]} -o off"; //turn off during our test
            exec($cmd);
          }
        }
      }
    }
    file_put_contents($memMapStatus, ($map !== false ? json_encode($map) : ""));
  }
  else { //if there were any models previously active then turn them back on now
    $mmf=file_get_contents($memMapStatus);
    if (trim($mmf) != '') {
      $map=json_decode($mmf);
      if (count($map)) {
        foreach ($map as $model=>$value) {
          if ($value===true) {
            $cmd="$fppmm -m $model -o on";
            exec($cmd);
          }
        }
      }
      file_put_contents($memMapStatus,''); //blank out the file
    }
  }
  
  //if ($state) $cmd="$fppmm -m {$_GET['plugin']} -t on";
  //else $cmd="$fppmm -m {$_GET['plugin']} -t off";
  if ($state) $cmd="$fppmm -t on";
  else $cmd="$fppmm -t off";
  exec ($cmd,$output,$var);
  $cur=getTestMode();
  if ($cur && $state) return true;
  if (!$cur && !$state) return true;
  return false;
}

function setChannel($channel, $value=0) { //$channel (1-131072)    $value (0-255)
  global $fppmm;
  global $confFile;

  $cmd="$fppmm -c $channel -s $value";
  exec($cmd,$output,$var);
  //echo "Run Command: $cmd<br>";
  if ($output[0]=="Set memory mapped channel $channel to $value") return true;
  else return false;
}

function updateMemoryMappedChannels() {
  global $confFile;
  global $settings;                                   
  if (isset($settings['pluginDirectory'])) $cmd="perl {$settings['pluginDirectory']}/{$_GET['plugin']}/memoryMap.pl";
  elseif (is_dir("/opt/fpp/plugins/{$_GET['plugin']}")) $cmd="perl /opt/fpp/plugins/{$_GET['plugin']}/memoryMap.pl";
  elseif (is_dir("/home/pi/media/plugins/{$_GET['plugin']}")) $cmd="perl /home/pi/media/plugins/{$_GET['plugin']}/memoryMap.pl";
  else return false;
  exec($cmd,$output,$var);
  if ($output[0]=="Success") return true;
  
  error_log("fpp-element-tester Error returned by updateMemoryMappedChannels: $output[0]");
  return false;
}

function GetChannelMemMaps()
{
	global $settings;
	$memmapFile = $settings['channelMemoryMapsFile'];

	$result = Array();

	$f = fopen($memmapFile, "r");
	if($f == FALSE)
	{
		fclose($f);
		returnJSON($result);
	}

	while (!feof($f))
	{
		$line = trim(fgets($f));

		if ($line == "")
			continue;
		
		if (substr($line, 0, 1) == "#")
			continue;

		$memmap = explode(",",$line,10);

		$elem = Array();
		$elem['BlockName']        = $memmap[0];
		$elem['StartChannel']     = $memmap[1];
		$elem['ChannelCount']     = $memmap[2];
		$elem['Orientation']      = $memmap[3];
		$elem['StartCorner']      = $memmap[4];
		$elem['StringCount']      = $memmap[5];
		$elem['StrandsPerString'] = $memmap[6];

		$result[] = $elem;
	}
	fclose($f);

	return $result;
}

function SetChannelMemMaps($dataArr)
{
	global $args;
	global $settings;

	$memmapFile = $settings['channelMemoryMapsFile'];

	//$data = json_decode($args['data'], true);
	$f = fopen($memmapFile, "w");
	if($f == FALSE)
	{
		fclose($f);
		returnJSON($result);
	}

	foreach ($dataArr as $memmap) {
		fprintf($f, "%s,%d,%d,%s,%s,%d,%d\n",
			$memmap['BlockName'], $memmap['StartChannel'],
			$memmap['ChannelCount'], $memmap['Orientation'],
			$memmap['StartCorner'], $memmap['StringCount'],
			$memmap['StrandsPerString']);
	}
	fclose($f);
}


function WriteSettingArrToFile($settingArr, $plugin = "") //write output values > 0 to a special configuration file for output level tracking
{
	global $settingsFile;
	global $settings;
	$filename = $settingsFile;

	if ($plugin != "") {
		$filename = $settings['configDirectory'] . "/plugin." . $plugin;
	}

	$settingsStr = "";
	$tmpSettings = parse_ini_file($filename);
	if (count($settingArr)) {
    foreach ($settingArr as $key=>$value) {
      $tmpSettings[$key] = $value;
    }
  }
  else return false;
  
  if (isset($tmpSettings[0])) unset($tmpSettings[0]);

	foreach ($tmpSettings as $key => $value) {
		if ($value > 0) $settingsStr .= $key . " = " . $value . "\n";
	}
  unset($tmpSettings);
  file_put_contents($filename, $settingsStr);
  unset($settingsStr);
}

function getGroupChannelStatus($channels,$color) {  //channels=csv of channels or array('r'=>csv of channels, 'g'=>csv of channels, 'b'=>csv of channels, 'w'=>csv of channels)
  global $settings;
  global $confFile;
  
  if (is_array($channels)) {
    foreach ($channels as $color=>$ch) {
      $out.="$ch,";
    }
    unset($channels);
    $channels=substr($out,0,-1);
    unset($ch);
  }
  $on=parse_ini_file($settings['configDirectory'] . "/plugin." . $confFile);
  if (count($on)) {
    $onList=implode(",",array_keys($on));
    
    $chArr=explode(",",$channels);
    foreach ($chArr as $ch) {
      if (strstr($ch,"|") !== false) { //this is a RGB channel
        $chArr2=explode("|",$ch);
        $rgb=0;
        foreach ($chArr2 as $ch2) {
          if (findChannel($ch2,$onList)) $rgb++;
          if ($rgb==3) return true;
        }
      }
      else {
        if (findChannel($ch,$onList)) return true;
      }
    }
  }
  return false;
}

function findChannel($channel, $channelList) { //find channel in channel list
  $channelList=",$channelList,";
  if (strstr($channelList,",$channel,") === false) return false;
  else return true;
}

function isOutputValueFileEmpty() {
  global $confFile;
  global $settings;
  
  $f=file_get_contents($settings['configDirectory']."/plugin.".$confFile);
  if (trim(file_get_contents($settings['configDirectory'] . "/plugin." . $confFile)) == "") return true;
  else return false;
}

function alphanumeric($string) {
  return preg_replace("/[A-Z][a-z][0-9]/","",$string);
}
  
function setNodeColors($channelsIn,$value) { //$channelsIn is a CSV string or array of CSV strings
  global $confFile;
  $outArr=array();
  
  if (is_array($channelsIn)) {
    foreach ($channelsIn as $chData) {
      $channelStr.=$chData.",";
    }
    $channelStr=rtrim($channelStr,",");
  }
  else $channelStr=$channelsIn;
  
  $channelStr=str_replace("|",",",$channelStr); //remove RGB color seperators if they are present
  
  $channels=explode(",",$channelStr);
  if (count($channels)) {
    foreach ($channels as $channel) {
      //setChannel($channel,$value);
      $outArr[intval($channel)]=$value;
    }
    WriteSettingArrToFile($outArr,$confFile);
    updateMemoryMappedChannels();
    unset($outArr);
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
  
function getOutputDetail($oidCSV) { //creates a list of output names
  global $xml;
  
  $out=false;
  $oidArr=explode(",",$oidCSV);
  foreach ($oidArr as $oid) {
    $ooid=$xml->xpath("physical/outputs/output[@oid='$oid']");
    if (count($ooid)) {
      $out[]=$ooid[0];
    }
  }
  return $out;
}

function showGroupColorButton($color, $path, $name, $checked=false, $aName=false) {
  return "<div class='switch switch$color'><input type='checkbox' onclick='javascript:getElements(\"\&fetPath=$path&fetName=$name&fetColor=$color\&fetValue=".($checked===false ? "255" : "0").($aName === false ? "" : "#$aName")."\"); return false;' ".($checked===true ? "checked" : "")."><label></label></div>";
}

function RecurseXML($xml, $parent, $mustBeFalse=false) //mustBeFalse is used to prevent $oidAssign variable clearing when recursing. Do not set this variable to anything but false
{ //taken from php.net
 
   $child_count = 0;
   foreach($xml as $key=>$value)
   {
      $child_count++;    
      $recurse=RecurseXML($value, $parent."/".$key, $oidAssign);
      if($recurse['count'] == 0)  // no childern, aka "leaf node"
      {
        if (isset($value['oid']))  {
          $oidAssign.="{$value['oid']},";
        }       
      }
   }
   return array('count'=>$child_count, 'oids'=>$oidAssign);
}  




/*
* * * * * * * * * * FUNCTIONS FOR VIEWING XML ERRORS * * * * * * * * * *
*/

function showAllErrors($errors, $xmlString) {
  echo "<pre>"; var_dump($errors); var_dump($xmlString); echo "</pre>";
  $xml=explode("\n",$xmlString);
  foreach ($errors as $error) {
    echo display_xml_error($error,$xml);
  }
  echo "done";
}

function display_xml_error($error, $xml) //taken from: http://php.net/manual/en/function.libxml-get-errors.php
{
    $return  = $xml[$error->line - 1] . "<br>";
    $return .= str_repeat('-', $error->column) . "^<br>";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "<br> &nbsp; Line: $error->line" .
               "<br> &nbsp; Column: $error->column";

    if ($error->file) {
        $return .= "<br> &nbsp; File: $error->file";
    }

    return "$return<br><br>--------------------------------------------<br><br>";
}



/*
* * * * * * * * * * FUNCTIONS FOR CONVERTING CSV FILE TO XML * * * * * * * * * *
*/

$xmlFile="";
$outputs="";
$colorArray=array();

function convertCSVtoXML($files) { //$files is $_FILES
  global $xmlFile;
  global $settings;
  global $colorArray;
  global $outputs;
  
  $xmlOutputDirectory=$settings['uploadDirectory'];
  $oid=1;
  $outputs="";
  $groups=array();
  $header=false;
  $lastChannel=1;
  $retArr=array();
  
  $fileRow=explode("\n",file_get_contents($files['tmp_name']));
  if (count($fileRow)) {
    foreach ($fileRow as $row) {
      if ($header===false) { //grab the header from the file
        $cols=explode(",",$row);
        foreach ($cols as $col=>$name) {
          switch (strtolower(trim($name))) {
            case "channel":
            case "name":
            case "color": $header[$name]=$col; break;
            case "group": $header['group'][]=$col; break;
          }
        }
        if (!isset($header['channel']) || !isset($header['name']) || !isset($header['color'])) return array('csv'=>"ERROR: Proper Header Not Found in CSV File. (channel, name, color are required values)");
      }
      else {
        if (trim(str_replace(",","",$row))=="") break; //skip blank lines
        $cols=explode(",",$row);
        $channel=$cols[$header['channel']];
        $name=$cols[$header['name']];
        $color=trim(strtoupper($cols[$header['color']]));
        if (trim($name)=="") break; //skip names that are blank
        
        //add channels to output array
        $inta=$cols[$header['channel']];
        $colorArr="";
        for ($inta=0; $inta<strlen($color); $inta++) {
          $colorArr[substr($color,$inta,1)]=intval($channel)+$inta;
        }
        $out['name']=$name;
        if (isset($colorArr['R'])) $out['R']=$colorArr['R'];
        if (isset($colorArr['G'])) $out['G']=$colorArr['G'];
        if (isset($colorArr['B'])) $out['B']=$colorArr['B'];
        if (isset($colorArr['W'])) $out['W']=$colorArr['W'];

        $outputs[$oid]=$out;
        unset($out);
        
        
        //now add oids to groups
        $foundGroup=false;
        foreach ($header['group'] as $key=>$grpCol) {
          $groupName=trim($cols[$grpCol]);
          if ($groupName != "") {
            //$group[$groupName][$oid]=true;
            $groups[$groupName][$oid]=true;
            $foundGroup=true;
          }
        }
        if (!$foundGroup) $groups[$name][$oid]=true;//$group[$name][$oid]=true;
//        echo "<pre>"; var_dump($group); echo "<hr></pre>"; 
        //$groups=array_merge_recursive($groups,$group);
        //echo "<pre>"; var_dump($groups); echo "<hr></pre>";
        //unset($group);
        $oid++;
      }
    }
    //exit;
    //echo "<pre>"; var_dump($groups); exit;
    
    //write output data to file
    $xmlOutputs="";
    $xmlFile=$xmlOutputDirectory."/".substr($files['name'],0,-3)."xml";
    $xmlFile=preg_replace("/[^A-Za-z0-9\/\.]/","",$xmlFile);

    $xmlOutput="<?xml version=\"1.0\" encoding=\"utf-8\"".chr(63).">
<fppCSVConverter>
<physical>
<outputs>\n";
    file_put_contents($xmlFile,$xmlOutput);
    foreach ($outputs as $oid=>$data) {
      $xmlOutput="<output oid=\"$oid\" name=\"{$data['name']}\"";
      if (isset($data['R'])) $xmlOutput.=" r=\"{$data['R']}\"";
      if (isset($data['G'])) $xmlOutput.=" g=\"{$data['G']}\"";
      if (isset($data['B'])) $xmlOutput.=" b=\"{$data['B']}\"";
      if (isset($data['W'])) $xmlOutput.=" w=\"{$data['W']}\"";
      $xmlOutput.=" />\n";
      file_put_contents($xmlFile,$xmlOutput,FILE_APPEND);
      
      if (isset($data['R']) && $data['R'] > $lastChannel) $lastChannel=$data['R'];
      if (isset($data['G']) && $data['G'] > $lastChannel) $lastChannel=$data['G'];
      if (isset($data['B']) && $data['B'] > $lastChannel) $lastChannel=$data['B'];
      if (isset($data['W']) && $data['W'] > $lastChannel) $lastChannel=$data['W'];
    }
    $xmlOutput="</outputs>\n</physical>\n";
    file_put_contents($xmlFile,$xmlOutput,FILE_APPEND);
    
    //change OID arrays to a CSV
    //echo "<pre>"; var_dump($groups); exit;
    foreach ($groups as $group=>$data1) {
      if (trim($group) != "") {
        $oidStr="";
        foreach ($data1 as $oid=>$nul) {
          $oidStr.="$oid,";
        }       
        //echo "<pre>$oidStr<br></pre>";
        $groups[$group]=substr($oidStr,0,-1);
      }  
      else unset($groups[$group]);
    }
    
    //create array of groups
    $groupArr=array();
    foreach ($groups as $grpName=>$grpOid) { //http://stackoverflow.com/questions/3857033/variable-containing-a-path-as-a-string-to-multi-dimensional-array
      $parts=explode("/",$grpName);
      $originalCount=count($parts);
      $arr=array();
      while ($bottom=array_pop($parts)) {
        if ($originalCount==count($parts)+1) $arr=array($bottom=>$grpOid);
        else $arr=array($bottom=>$arr);
      }
      $groupArr=array_merge_recursive($groupArr,$arr);
    }        

    unset($groups);

    //write group data to xml file

    file_put_contents($xmlFile,"<groups>\n",FILE_APPEND);
    recurseGroupArray($groupArr);
    file_put_contents($xmlFile,"</groups>\n",FILE_APPEND);

    //write all channel detail to xml file        
    file_put_contents($xmlFile,"<allChannels>\n",FILE_APPEND);
    recurseGroupArrayAllColors($groupArr);
    if (isset($colorArray['R'])) file_put_contents($xmlFile,"<Red ch=\"".rtrim($colorArray['R'],",")."\" />\n",FILE_APPEND);
    if (isset($colorArray['G'])) file_put_contents($xmlFile,"<Green ch=\"".rtrim($colorArray['G'],",")."\" />\n",FILE_APPEND);
    if (isset($colorArray['B'])) file_put_contents($xmlFile,"<Blue ch=\"".rtrim($colorArray['B'],",")."\" />\n",FILE_APPEND);
    if (isset($colorArray['W'])) file_put_contents($xmlFile,"<White ch=\"".rtrim($colorArray['W'],",")."\" />\n",FILE_APPEND);
    file_put_contents($xmlFile,"</allChannels>\n",FILE_APPEND);
    
    //close xml tags
    file_put_contents($xmlFile,"</fppCSVConverter>\n",FILE_APPEND);
    
    unset ($outputs);
    unset ($groupArr);
    unset ($allChannels);
    unset ($colorArr);
    unset ($colorArray);
  }
  else return array('csv'=>"ERROR: CSV File Contains no rows.");
  $retArr['csv']=true; //csv was converted successfully
  
  //Create plugin config file
  WriteSettingToFile("ConfigFileXML", $xmlFile, $_GET['plugin']);
  $retArr['config']=true; //config was created successfully
  
  //create a Pixel Overlay model for this display using channels 1 through $lastChannel
  $memMaps=GetChannelMemMaps();
  if (count($memMaps)) {
    $foundFET=false;
    $update=false;
    foreach ($memMaps as $index=>$memMap) {
      if ($memMap['BlockName']==$_GET['plugin']) {
        $foundFET=true;
        if ($memMap['StartChannel'] != 1) {$memMaps[$index]['StartChannel']=1; $update=true;}
        if ($memMap['ChannelCount'] != $lastChannel) {$memMaps[$index]['ChannelCount']=$lastChannel; $update=true;}
      }
    }
  }
  if (!$foundFET) {$retArr['mapCreated']="Pixel Overlay Model created with $lastChannel channels."; $memMaps[]=array('BlockName'=>$_GET['plugin'], 'StartChannel'=>1, 'ChannelCount'=>$lastChannel, 'Orientation'=>'horizontal', 'StartCorner'=>'TL', 'StringCount'=>1, 'StrandsPerString'=>1); $update=true;}
  if ($update===true) {
    if ($foundFET) $retArr['mapUpdated']="Pixel Overlay Model updated to $lastChannel channels.";
    SetChannelMemMaps($memMaps);
    echo <<<EOF
<script type='text/javascript'>
  $(document).ready(function() {
    SetRestartFlag();
  });
</script>
EOF;
  }
  else $retArr['mapOkay']="Pixel Overlay Model did not require modification";
  return $retArr;
}    
    
function recurseGroupArrayAllColors($array) {  //used to get all color channels for all outputs
  global $outputs;
  global $colorArray;
  
  foreach ($array as $key=>$value) {
    if (is_array($value)) {
      $colors=recurseGroupArrayColors($value);
      if (isset($colors['R'])) $colorArray['R'].=$colors['R'];
      if (isset($colors['G'])) $colorArray['G'].=$colors['G'];
      if (isset($colors['B'])) $colorArray['B'].=$colors['B'];
      if (isset($colors['W'])) $colorArray['W'].=$colors['W'];
      recurseGroupArrayAllColors($value);
    }
    else {
      $colors=array();
      $oidArr=explode(",",$value);
      foreach ($oidArr as $oid) {
        if (isset($outputs[$oid]['R'])) $colorArray['R'].=$outputs[$oid]['R'].",";
        if (isset($outputs[$oid]['G'])) $colorArray['G'].=$outputs[$oid]['G'].",";
        if (isset($outputs[$oid]['B'])) $colorArray['B'].=$outputs[$oid]['B'].",";
        if (isset($outputs[$oid]['W'])) $colorArray['W'].=$outputs[$oid]['W'].",";
        if (isset($outputs[$oid]['R']) && isset($outputs[$oid]['G']) && isset($outputs[$oid]['B'])) $colorArray['W'].=$outputs[$oid]['R']."|".$outputs[$oid]['G']."|".$outputs[$oid]['B'].",";
      }
    }
  }
}

function recurseGroupArray($array) { //used to get group memberships as well as color channels for each group
  global $xmlFile;
  global $outputs;
  
  foreach ($array as $key=>$value) {
    if (is_array($value)) {
      $colors=recurseGroupArrayColors($value);
      $str="";
      if (isset($colors['R'])) $str.=" r=\"".rtrim($colors['R'],",")."\"";
      if (isset($colors['G'])) $str.=" g=\"".rtrim($colors['G'],",")."\"";
      if (isset($colors['B'])) $str.=" b=\"".rtrim($colors['B'],",")."\"";
      if (isset($colors['W'])) $str.=" w=\"".rtrim($colors['W'],",")."\"";
      file_put_contents($xmlFile,"<group name=\"$key\"$str>\n",FILE_APPEND);
      recurseGroupArray($value);
      file_put_contents($xmlFile,"</group>\n",FILE_APPEND);
    }
    else {
      $colors=array();
      $oidArr=explode(",",$value);
      foreach ($oidArr as $oid) {
        if (isset($outputs[$oid]['R'])) $colors['R'].=$outputs[$oid]['R'].",";
        if (isset($outputs[$oid]['G'])) $colors['G'].=$outputs[$oid]['G'].",";
        if (isset($outputs[$oid]['B'])) $colors['B'].=$outputs[$oid]['B'].",";
        if (isset($outputs[$oid]['W'])) $colors['W'].=$outputs[$oid]['W'].",";
        if (isset($outputs[$oid]['R']) && isset($outputs[$oid]['G']) && isset($outputs[$oid]['B'])) $colors['W'].=$outputs[$oid]['R']."|".$outputs[$oid]['G']."|".$outputs[$oid]['B'].",";
      }
      $str="";
      if (isset($colors['R'])) $str.=" r=\"".rtrim($colors['R'],",")."\"";
      if (isset($colors['G'])) $str.=" g=\"".rtrim($colors['G'],",")."\"";
      if (isset($colors['B'])) $str.=" b=\"".rtrim($colors['B'],",")."\"";
      if (isset($colors['W'])) $str.=" w=\"".rtrim($colors['W'],",")."\"";

      file_put_contents($xmlFile,"<element name=\"$key\" oid=\"$value\"$str />\n",FILE_APPEND);
    }
  }
}

function recurseGroupArrayColors($array) { //Used to gather all color detail for the current iteration
  global $outputs;
  foreach ($array as $key=>$value) {
    if (is_array($value)) {
      //$colors=array_merge_recursive(recurseGroupArrayColors($value),$colors);
      $colors=recurseGroupArrayColors($value);
    }
    else {
      $oidArr=explode(",",$value);
      foreach ($oidArr as $oid) {
        if (isset($outputs[$oid]['R'])) $colors['R'].=$outputs[$oid]['R'].",";
        if (isset($outputs[$oid]['G'])) $colors['G'].=$outputs[$oid]['G'].",";
        if (isset($outputs[$oid]['B'])) $colors['B'].=$outputs[$oid]['B'].",";
        if (isset($outputs[$oid]['W'])) $colors['W'].=$outputs[$oid]['W'].",";
        if (isset($outputs[$oid]['R']) && isset($outputs[$oid]['G']) && isset($outputs[$oid]['B'])) $colors['W'].=$outputs[$oid]['R']."|".$outputs[$oid]['G']."|".$outputs[$oid]['B'].",";
      }
    }
  }  
  return $colors;
} 
?>