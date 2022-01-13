<div id='daemon-content'>
<?php

include(__DIR__ . "/DaemonIndexContent.php"); ?>
</div>


<script type="text/javascript">

function reload(){
    $("#daemon-content").load("Daemon/indexContent");
}

$(document).ready(function(){
    setInterval(function(){reload();},1000);
});
</script>
