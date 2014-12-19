<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

if (isset($_POST['fpp-submit']) && isset($_FILES['fpp-config-file'])) {
  $ext=strtolower(substr($_FILES['fpp-config-file']['name'],-3));
  if ($ext != "csv")
    echo "Error uploading Channel Configuration File.  The file must be a CSV.";
  else {
    include 'functions.php';
    if (convertCSVtoXML($_FILES['fpp-config-file']))  {
      $jGrowl[]="Display Configuration File was Created.";
    }
    else $jGrowl[]="Display Configuration File Failed.";
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
    //alert ("getElements function ran with command: " + command);
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
<form method='post' enctype='multipart/form-data'><input type='file' name='fpp-config-file'><input type='submit' value='Upload' name='fpp-submit'></form>
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