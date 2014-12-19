<h3>FPP Element Tester</h3>
<i>Plugin Version: <?php echo file_get_contents($settings['fppDir']."/plugins/{$_GET['plugin']}/version_fet"); ?></i><br>
<br>
This plugin allows you to load in your displays configuration and test elements based upon group memberships.

<h4>Getting Started</h4>
1) Install the plugin<br>
2) Navigate to the "Status/Control" menu and then the "Element Tester" option<br>
3) Browse and upload your displays CSV configuration file (see below section on creating a CSV configuration file)<br>
4) Enjoy the element testing functionality<br> 

<h4>Known Limitations / Gotchas</h4>
Channels are currently toggled by sending individual commands to each output in a group/element.  This can lead to long processing time when selecting elements/groups with numerous outputs.

<h4>Creating a CSV Configuration File</h4>
1) Open Microsoft Excel, Open Office's Calc, or your favorite spreadsheet editor<br>
2) Create a worksheet with the column headers channel, name, color and group (you may enter as many group columns as you wish to have pixels/outputs assigned to multiple groups)<br>
3) Starting from your lowest universe and channel number enter:<br> 
 <ul> the starting channel for the pixel or output channel in the channel column</ul>
 <ul> a short but descriptive name of the pixel or output channel in the name column</ul>
 <ul> the color channels and order for the pixel or output channel (valid values are R,G,B,W.  e.g. RGB or BRG or W or RGBW)</ul>
 <ul> a short group name if you want to group multiple channels into a group. Use a forward slash '/' to indicate multiple group levels</ul>
4) Save the worksheet onto your computer as a CSV (Comma Seperated Values) document
<br> 
example showing:
<ul>An 8 channel incandescent arch starting at channel 1 with white channels only in a group named Arch 1 inside a group named Arches</ul>
<ul>A 3 pixel ornament starting at channel 9 with color order RGB assigned to a group named Ornaments as well as a group named Ornaments inside a group named Mega Tree</ul>
<ul>A 4 channel DIYC Flood light starting at channel 18 with color order RGBW assigned to a group named Flood Lights</ul>
<ul>A 1 pixel snowflake starting at channel 22 with color order GRB assigned to group Snowflakes inside a group called Mood</ul> 
<ul>A tune to sign starting at channel 25 with a single white channel not assigned to any groups (note that if you do not enter a group name then the name column will be used automatically</ul> 

<table border=1><tr><th>channel</th><th>name</th><th>color</th><th>group</th><th>group</th></tr>
<tr><td>1</td><td>Channel 1</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>2</td><td>Channel 2</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>3</td><td>Channel 3</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>4</td><td>Channel 4</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>5</td><td>Channel 5</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>6</td><td>Channel 6</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>7</td><td>Channel 7</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>8</td><td>Channel 8</td><td>W</td><td>Arches/Arch 1</td><td>&nbsp;</td></tr>
<tr><td>9</td><td>Pixel 1</td><td>RGB</td><td>Ornaments</td><td>Mega Tree/Ornaments</td></tr>
<tr><td>12</td><td>Pixel 2</td><td>RGB</td><td>Ornaments</td><td>Mega Tree/Ornaments</td></tr>
<tr><td>15</td><td>Pixel 3</td><td>RGB</td><td>Ornaments</td><td>Mega Tree/Ornaments</td></tr>
<tr><td>18</td><td>Flood 1</td><td>RGBW</td><td>Flood Lights</td><td>&nbsp;</td></tr>
<tr><td>22</td><td>Snowflake 1</td><td>GRB</td><td>Mood</td><td>&nbsp;</td></tr>
<tr><td>25</td><td>Tune To Sign</td><td>W</td><td>&nbsp;</td><td>&nbsp;</td></tr>
</table>

<br><br>

The above example will generate the following tree inside of the plugin:<br>
Arches<br>
&nbsp;&nbsp;&nbsp;&nbsp;Arch 1<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 1 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 2 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 3 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 4 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 5 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 6 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 7 &nbsp; (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channel 8 &nbsp; (White)<br>
Ornaments<br>
&nbsp;&nbsp;&nbsp;&nbsp;Pixel 1 &nbsp; (Red) (Green) (Blue) (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;Pixel 2 &nbsp; (Red) (Green) (Blue) (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;Pixel 3 &nbsp; (Red) (Green) (Blue) (White)<br>
Mega Tree<br>
&nbsp;&nbsp;&nbsp;&nbsp;Ornaments<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pixel 1 &nbsp; (Red) (Green) (Blue) (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pixel 2 &nbsp; (Red) (Green) (Blue) (White)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pixel 3 &nbsp; (Red) (Green) (Blue) (White)<br>
Flood Lights<br>
&nbsp;&nbsp;&nbsp;&nbsp;Flood 1 &nbsp; (Red) (Green) (Blue) (White)<br>
Mood<br>
&nbsp;&nbsp;&nbsp;&nbsp;Snowflake 1 &nbsp; (Red) (Green) (Blue) (White)<br>
Tune To Sign<br>
&nbsp;&nbsp;&nbsp;&nbsp;Tune To Sign &nbsp; (White)<br>

<br><br>