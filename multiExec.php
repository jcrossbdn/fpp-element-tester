<?php
include 'functions.php';

if (isset($_GET['plugin']) && isset($_POST['channels']) && isset($_GET['value'])) {
  global $fppmm;
  global $confFile;
  $channels=explode(",",$_POST['channels']);
  foreach ($channels as $channel) {
    $cmd="$fppmm -c $channel -s {$_GET['value']}";
    //exec($cmd,$output,$var);
    //echo "Run Command: $cmd<br>";
    //if ($output[0]=="Set memory mapped channel $channel to {$_GET['value']}") return true;
    //else return false;
    if ($output[0]=="Set memory mapped channel $channel to {$_GET['value']}") echo "$channel=true\n";
    else echo "$channel=false\n";
  }
}
else echo "false";
?>