<?php
/**
 * @var ConnecteurFrequence $connecteurFrequence
 * @var Gabarit $this
 */
?>
<a class='btn btn-mini' href='<?php $this->url("Daemon/config") ?>'>
	<i class='icon-circle-arrow-left'></i>Retour à la liste des fréquences
</a>
<div class="box">
	<h2>Détail d'une fréquence</h2>
	<table class='table table-striped'>
		<tr>
			<th class='w200'>Type</th>
			<td><?php hecho($connecteurFrequence->type_connecteur?:'Tous') ?></td>
		</tr>
		<tr>
			<th class='w200'>Famille de connecteurs</th>
			<td><?php hecho($connecteurFrequence->famille_connecteur?:'Toutes') ?></td>
		</tr>
		<tr>
			<th class='w200'>Connecteur</th>
			<td><?php hecho($connecteurFrequence->id_connecteur?:'Tous') ?></td>
		</tr>
        <tr>
            <th class='w200'>Instance de connecteur</th>
            <td>
                <?php if ($connecteurFrequence->id_ce) : ?>
                <a href="<?php $this->url("Connecteur/edition?id_ce={$connecteurFrequence->id_ce}")?>">
                    <?php hecho($connecteurFrequence->getInstanceConnecteurAsString()) ?>
                </a>
                <?php else : ?>
                    Toutes
                <?php endif ?>
            </td>
        </tr>

        <tr>
			<th class='w200'>Type d'action</th>
			<td><?php hecho($connecteurFrequence->action_type?:'Tous') ?></td>
		</tr>
		<?php if($connecteurFrequence->action_type == 'document') : ?>
		<tr>
			<th class='w200'>Type de document</th>
			<td><?php hecho($connecteurFrequence->type_document?:'Tous') ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th class='w200'>Action</th>
			<td><?php hecho($connecteurFrequence->action?:'Toutes') ?></td>
		</tr>
		<tr>
			<th class='w200'>Fréquence</th>
			<td>
				<?php echo nl2br(get_hecho($connecteurFrequence->getExpressionAsString())) ?>
			</td>
		</tr>
		<tr>
			<th class='w200'>Verrou</th>
			<td><?php hecho($connecteurFrequence->id_verrou) ?></td>
		</tr>

	</table>
	<a class='btn'
	   href="<?php $this->url("Daemon/editFrequence?id_cf={$connecteurFrequence->id_cf}") ?>"
	>
		Modifier
	</a>
	<a class='btn btn-danger'
	   href="<?php $this->url("Daemon/deleteFrequence?id_cf={$connecteurFrequence->id_cf}") ?>"
	>
		Supprimer
	</a>
</div>