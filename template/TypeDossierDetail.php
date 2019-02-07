<?php
/**
 * @var array $type_dossier_definition
 * @var array $type_de_dossier_info
 * @var int $id_t
 */
?>
<a class='btn btn-link' href='<?php $this->url("TypeDossier/list")?>'>
	<i class="fa fa-arrow-left"></i>&nbsp;Liste des types de dossier
</a>


<div class="box">
	<h2>Information sur le type de dossier</h2>

	<table class="table table-striped">
		<tr>
			<th class='w200'>Identifiant</th>
			<td><?php hecho($type_de_dossier_info['id_type_dossier']) ?></td>
		</tr>
		<tr>
			<th class='w200'>Libellé</th>
			<td><?php hecho($type_dossier_definition['nom']) ?></td>
		</tr>
		<tr>
			<th class='w200'>Libellé du classement</th>
			<td><?php hecho($type_dossier_definition['type'])?></td>
		</tr>
		<tr>
			<th class='w200'>Description</th>
			<td><?php echo nl2br(get_hecho($type_dossier_definition['description']))?></td>
		</tr>
	</table>

	<a class='btn btn-primary inline' href='<?php $this->url("/TypeDossier/editionLibelle?id_t={$id_t}") ?>'>
		<i class='fa fa-pencil'></i>&nbsp;Modifier
	</a>

</div>

<div class="box">
	<h2>Formulaire</h2>
	<?php if (empty($type_dossier_definition['formulaire'])) : ?>
		<div class="alert alert-warning">
			Ce formulaire ne contient pas d'élement
		</div>
	<?php else : ?>
		<table class="table table-striped">
			<tr>
				<th>Identifiant de l'élément</th>
				<th>Libellé</th>
				<th>Type</th>
				<th>Propriétés</th>
				<th>Action</th>
			</tr>
			<?php foreach($type_dossier_definition['formulaire'] as $element_id => $element_formulaire) : ?>
				<tr>
					<td><?php hecho($element_id) ?></td>
					<td><?php hecho($type_dossier_definition['formulaire'][$element_id]['name']) ?></td>
					<td><?php hecho(TypeDossierDefinition::getTypeElementLibelle($type_dossier_definition['formulaire'][$element_id]['type'])) ?></td>
					<td>
						<?php if($type_dossier_definition['formulaire'][$element_id]['requis']) :?>
                            <p class="badge badge-danger">Obligatoire</p>
                        <?php endif;?>
						<?php if($type_dossier_definition['formulaire'][$element_id]['champs-affiches']) :?>
                            <p class="badge badge-info">Affiché sur la liste</p>
						<?php endif;?>
						<?php if($type_dossier_definition['formulaire'][$element_id]['champs-recherche-avancee']) :?>
                            <p class="badge badge-info">Recherche avancée</p>
						<?php endif;?>
					</td>
					<td>
						<a class='btn btn-primary' href="<?php $this->url("/TypeDossier/editionElement?id_t={$id_t}&element_id={$element_id}") ?>"><i class='fa fa-pencil'></i>&nbsp;Modifier</a>
						&nbsp;
						<a class='btn btn-danger' href="<?php $this->url("/TypeDossier/deleteElement?id_t={$id_t}&element_id={$element_id}") ?>"><i class='fa fa-warning'></i>&nbsp;Supprimer</a>
					</td>
				</tr>
			<?php endforeach;?>
		</table>
	<?php endif; ?>
	<a class='btn btn-primary inline' href='<?php $this->url("/TypeDossier/editionElement?id_t={$id_t}") ?>'>
		<i class='fa fa-plus-circle'></i>&nbsp;Ajouter
	</a>
</div>


<div class="box">
	<h2>Cheminement</h2>
	<?php if (empty($type_dossier_definition['cheminement'])) : ?>
        <div class="alert alert-warning">
            Le cheminement de ce type de dossier est vide.
        </div>
    <?php else: ?>
        <table class="table table-striped">
            <tr>
                <th>Numéro de l'étape</th>
                <th>Type de l'étape</th>
                <th>Propriétés</th>
                <th>Action</th>
            </tr>
			<?php foreach($type_dossier_definition['cheminement'] as $num_etape => $element_etape) : ?>
                <tr>
                    <td><?php hecho($num_etape + 1) ?></td>
                    <td><?php hecho(TypeDossierDefinition::getTypeEtapeLibelle($type_dossier_definition['cheminement'][$num_etape]['type'])) ?></td>
                    <td>
						<?php if($type_dossier_definition['cheminement'][$num_etape]['requis']) :?>
                            <p class="badge badge-danger">Obligatoire</p>
                        <?php else: ?>
                            <p class="badge badge-warning">Facultative</p>
						<?php endif;?>
                    </td>
                    <td>
                        <a class='btn btn-primary' href="<?php $this->url("/TypeDossier/editionEtape?id_t={$id_t}&num_etape={$num_etape}") ?>"><i class='fa fa-pencil'></i>&nbsp;Modifier</a>
                        &nbsp;
                        <a class='btn btn-danger' href="<?php $this->url("/TypeDossier/deleteEtape?id_t={$id_t}&num_etape={$num_etape}") ?>"><i class='fa fa-warning'></i>&nbsp;Supprimer</a>
                    </td>
                </tr>
			<?php endforeach;?>
        </table>
    <?php endif; ?>
	<a class='btn btn-primary inline' href='<?php $this->url("/TypeDossier/editionEtape?id_t={$id_t}&num_etape=new") ?>'>
		<i class='fa fa-plus-circle'></i>&nbsp;Ajouter
	</a>
</div>
