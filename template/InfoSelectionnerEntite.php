<?php
$id_e_value = isset($_GET['id_e']) ? $_GET['id_e'] : null;
// @info: marche seulement si on a un vhost
$show = empty($id_e_value)
  && (
    strpos($_SERVER['REQUEST_URI'], '/Document/') === 0
    || $_SERVER['REQUEST_URI'] === '/'
  );
  // @info: par élimination
  //&& preg_match('/^\/(Aide|Daemon|Entite|Journal|Role|System|Utilisateur)\//', $_SERVER['REQUEST_URI']) !== 1;
?>
<?php if ($show): ?>
<div id="title-choose" class="alert alert-info">
  Veuillez sélectionner une entité afin de pouvoir visualiser des flux.</h2>
</div>
<?php endif; ?>
