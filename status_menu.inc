<?php

$pages = Array(
	'name' => "FPP Element Tester",
	'type' => "status",
	'page' => "fpp-element-tester.php");

?>

<li><a href="<?php echo preg_replace('/.*\/plugins\/(.*)/', 'plugin.php?plugin=$1&page=fpp-element-tester.php', dirname(__FILE__)); ?>">Element Tester</a></li>
