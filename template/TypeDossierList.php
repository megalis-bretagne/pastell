<?php
/**
 * @var string $droit_edition
 */
?>
<div class="box" style="min-height: 500px;">
	<?php if ($droit_edition) : ?>
		<a href="TypeDossier/edition" class='btn btn-primary grow'><i class="fa fa-plus-circle"></i>&nbsp;Créer</a>
	<?php endif;?>

	<?php if (empty($type_dossier_list)) : ?>
		<br/><br/>
		<div class="alert-info alert">
			Il n'y a pas de type de dossier personnalisé sur cette plateforme Pastell.
		</div>
	<?php else: ?>

            <table class="table table-striped">
                <tr>
                    <th class='w200'>Type de dossier</th>
                    <th>Action</th>
                </tr>
				<?php foreach($type_dossier_list as $type_dossier_info) : ?>
                    <tr>
                        <td>
                            <a href='<?php $this->url("TypeDossier/detail?id_t={$type_dossier_info['id_t']}") ?>'>
                                <?php hecho($type_dossier_info['id_type_dossier']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php $this->url("TypeDossier/detail?id_t={$type_dossier_info['id_t']}") ?>" class="btn btn-primary">
                                <i class="fa fa-eye"></i>&nbsp;Voir
                            </a>
                            &nbsp;
                            <a href="<?php $this->url("TypeDossier/edition?id_t={$type_dossier_info['id_t']}") ?>" class="btn btn-warning">
                                <i class="fa fa-warning"></i>&nbsp;Modifier l'identifiant
                            </a>
                            &nbsp;
                            <a href="<?php $this->url("TypeDossier/delete?id_t={$type_dossier_info['id_t']}") ?>" class="btn btn-danger">
                                <i class="fa fa-warning"></i>&nbsp;Supprimer
                            </a>
                            &nbsp;
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>

	<?php endif;?>

</div>
