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
</script>

<div id="elements" class="settings">
<fieldset>
<legend>Display Elements</legend>
<div id='elementBlock'></div>
</fieldset>
</div>
