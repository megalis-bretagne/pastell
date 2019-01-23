<?php
$elapsedTime = round($this->PastellTimer->getElapsedTime(),3);

?>

<br/>
<div id="bottom">

    <div class="bloc_copyright">
        <div class="bloc_logo_libriciel">
            <a href='https://www.libriciel.fr/'>
                <img src="img/commun/Libriciel_white_h24px.png" alt="Libriciel" />
            </a>
        </div>
		<div class="bloc_mentions">
            <p>	<a href='https://www.libriciel.fr/pastell/' target="_blank">Pastell</a>


                <?php if (isset($roleUtilisateur) && $roleUtilisateur->hasOneDroit($authentification->getId(),"system:lecture")) :?>
                    <a href="System/Changelog"><?php echo $manifest_info['version-complete'] ?></a>
                <?php else: ?>
                    <?php echo $manifest_info['version-complete'] ?>
                <?php endif; ?>
                 -
                <a href="https://www.libriciel.fr" target="_blank">Libriciel SCOP</a>
                - <em><?php echo $elapsedTime ?>s</em></p>
		</div>
    <div id="bloc_left">

    </div>

	</div>
</div>
