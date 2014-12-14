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
<legend>Display Elements</legend>
<div id='elementBlock'></div>
</fieldset>
</div>

<div id="ajaxLoading" class="ajaxLoading" title="Loading..."><img src="?plugin=fpp-element-tester&page=loading.gif&nopage=1" alt="Loading..."></div>
