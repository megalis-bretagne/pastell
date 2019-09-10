<?php
/** @var Gabarit $this */
/**@var string $externalSystem */
?>
<div class="alert alert-danger">
    Erreur lors de la connexion au serveur distant (<?php hecho($externalSystem); ?>)
</div>

<a href="<?php $this->url("Connexion/logout"); ?>">Se déconnecter</a>
