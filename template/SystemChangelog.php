<?php

$text = file_get_contents(__DIR__."/../CHANGELOG.md");




$parsedown = new Parsedown();
$text = $parsedown->parse($text);

$text = preg_replace("/<h2>/","<h3>",$text);
$text = preg_replace("/<h1>/","<h2>",$text);

?>
<div class="box">
<?php echo $text; ?>
</div>
