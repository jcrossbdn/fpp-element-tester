<?php
//echo "<pre>"; var_dump($settings); 
//var_dump(file_get_contents($settings['outputProcessorsFile']));
//exit;
error_reporting(E_ERROR);
ini_set('display_errors', true);
include_once 'functions.php';

if (isset($_POST['fpp-element-tester-submit']) && isset($_FILES['fpp-element-tester-config-file'])) {
  $ext=strtolower(substr($_FILES['fpp-element-tester-config-file']['name'],-3));
  if ($ext != "csv")
    echo "Error uploading Channel Configuration File.  The file must be a CSV.";
  else {
    //include 'functions.php';
    $ret=convertCSVtoXML($_FILES['fpp-element-tester-config-file']);
    if ($ret['csv']===true) {
      $jGrowl[]="XML File Created Successfully.";
    }
    else {
      $jGrowl[]="XML File Creation Failed.";
      echo "<strong><font color='red'>{$ret['csv']}</font></strong>";
    }
    if ($ret['config']===true)  {
      $jGrowl[]="Display Configuration File was Created.";
    }
    else {
      $jGrowl[]="Display Configuration File create/update Failed.";
    }
    if (isset($ret['mapCreated']) && $ret['mapCreated']!==false) $jGrowl[]=$ret['mapCreated'];
    elseif (isset($ret['mapUpdated']) && $ret['mapUpdated']!==false) $jGrowl[]=$ret['mapUpdated'];
    elseif (isset($ret['mapOkay']) && $ret['mapOkay']!==false) $jGrowl[]=$ret['mapOkay'];
    else  {
      $jGrowl[]="Error Creating Pixel Overlay Model";
      echo "<strong><font color='red'>An Error was encountered while creating Pixel Overlay Model</font></strong>";
    }
  }
}

if (isset($jGrowl)) {
  echo "<script type=\"text/javascript\" id=\"fetGrowlMessage\">";
  if (count($jGrowl)) {
    echo "$(document).ready(function() {\n";      
    foreach ($jGrowl as $msg) {
      echo "  $.jGrowl(\"$msg\");";
    }
    echo "});\n";
  }
  echo "</script>";
}


?>
<script>
  function getElements(command) {
    var screenPosition = $(window).scrollTop();
    
    $.ajax({
      type: "GET",
      url: "?plugin=fpp-element-tester&page=ajax.php&nopage=1" + command,
      dataType: "html",
      success: function(data) {
        $('#elementBlock').html(data);
        $(window).scrollTop(screenPosition);
      }
    });
  }
  $(document).ready(function() {
    getElements('');
  });
  
  $(document).ajaxStart(function() {
    $("#ajaxLoading").show();
  });
  $(document).ajaxStop(function() {
    $("#ajaxLoading").hide();
  });
</script>

<div id="elements" class="settings">
<fieldset>
<legend>Element Configuration</legend>
<form method='post' enctype='multipart/form-data'><input type='file' name='fpp-element-tester-config-file'><input type='submit' value='Upload' name='fpp-element-tester-submit'></form>
</fieldset>
</div>
<br>
<div id="elements" class="settings">
<fieldset>
<legend>Display Elements</legend>
<div id='elementBlock'></div>
</fieldset>
</div>

<div id="ajaxLoading" class="ajaxLoading" title="Loading..."><img src="?plugin=fpp-element-tester&page=loading.gif&nopage=1" alt="Loading..."></div>