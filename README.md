# FPP Element Tester

This plugin allows you to load in your displays configuration and test elements based upon defined element group memberships.

### Known limitations / Gotchas
This plugin utilizes a pixel overlay model which is created automatically encompassing all channels in your display.
When using this plugin to test any channels all existing overlay models that are activated will be deactivated and will have to be manually reactivated once you are done with testing.

### Getting Started
1. Install the plugin
   - manually greater than fpp v1.0: from the command line type: sudo git clone git://github.com/jcrossbdn/fpp-element-tester /home/pi/media/plugins/fpp-element-tester
   - manually in fpp v1.0: from the command line type: sudo git clone git://github.com/jcrossbdn/fpp-element-tester /opt/fpp/plugins/fpp-element-tester
2. Navigate to the "Status/Control" menu and then the "Element Tester" option. If you dont see this option then refresh your browser window.
3. Browse and upload your displays CSV configuration file (see below section on creating a CSV configuration file)
4. Enjoy the element testing functionality 

### Creating a CSV Configuration File
1. Open Microsoft Excel, Open Office's Calc, or your favorite spreadsheet editor
2. Create a worksheet with the column headers channel, name, color and group (you may enter as many group columns as you wish to have pixels/outputs assigned to multiple groups)
3. Starting from your lowest universe and channel number enter: 
   - the starting channel for the pixel or output channel in the channel column
   - a short but descriptive name of the pixel or output channel in the name column
   - the color channels and order for the pixel or output channel (valid values are R,G,B,W.  e.g. RGB or BRG or W or RGBW) Please ensure all channels assigned have a color identifier.
   - a short group name if you want to group multiple channels into a group. Use a forward slash '/' to indicate multiple group levels
4. Save the worksheet onto your computer as a CSV (Comma Seperated Values) document


### Example
##### Showing:
1. An 8 channel incandescent arch starting at channel 1 with white channels only in a group named Arch 1 inside a group named Arches
2. A 3 pixel ornament starting at channel 9 with color order RGB assigned to a group named Ornaments as well as a group named Ornaments inside a group named Mega Tree
3. A 4 channel DIYC Flood light starting at channel 18 with color order RGBW assigned to a group named Flood Lights
4. A 1 pixel snowflake starting at channel 22 with color order GRB assigned to group Snowflakes inside a group called Mood
5. A tune to sign starting at channel 25 with a single white channel not assigned to any groups (note that if you do not enter a group name then the name column will be used automatically

| channel | name         | color | group         | group               |
|---------|--------------|-------|---------------|---------------------|
| 1       | Channel 1    | W     | Arches/Arch 1 |                     |
| 2       | Channel 2    | W     | Arches/Arch 1 |                     |
| 3       | Channel 3    | W     | Arches/Arch 1 |                     |
| 4       | Channel 4    | W     | Arches/Arch 1 |                     |
| 5       | Channel 5    | W     | Arches/Arch 1 |                     |
| 6       | Channel 6    | W     | Arches/Arch 1 |                     |
| 7       | Channel 7    | W     | Arches/Arch 1 |                     |
| 8       | Channel 8    | W     | Arches/Arch 1 |                     |
| 9       | Pixel 1      | RGB   | Ornaments     | Mega Tree/Ornaments |
| 12      | Pixel 2      | RGB   | Ornaments     | Mega Tree/Ornaments |
| 15      | Pixel 3      | RGB   | Ornaments     | Mega Tree/Ornaments |
| 18      | DIYC Flood 1 | RGBW  | Flood Lights  |                     |
| 22      | Snowflake 1  | GRB   | Mood          |                     |
| 25      | Tune To Sign |       |               |                     |




The above example will generate the following tree inside of the plugin:
```
Arches
  Arch 1
    Channel 1 - (White)
    Channel 2 - (White)
    Channel 3 - (White)
    Channel 4 - (White)
    Channel 5 - (White)
    Channel 6 - (White)
    Channel 7 - (White)
    Channel 8 - (White)
Ornaments
  Pixel 1 - (Red) (Green) (Blue) (White)
  Pixel 2 - (Red) (Green) (Blue) (White)
  Pixel 3 - (Red) (Green) (Blue) (White)
Mega Tree
  Ornaments
    Pixel 1 - (Red) (Green) (Blue) (White)
    Pixel 2 - (Red) (Green) (Blue) (White)
    Pixel 3 - (Red) (Green) (Blue) (White)
Flood Lights
  Flood 1 - (Red) (Green) (Blue) (White)
Mood
  Snowflake 1 - (Red) (Green) (Blue) (White)
Tune To Sign
  Tune To Sign - (White)
```
