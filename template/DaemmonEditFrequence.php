<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("Daemon/config") ?>'>
	<i class='icon-circle-arrow-left'></i>
	Retour à la liste des fréquences</a>


<div class="box">
	<h2>Création d'une nouvelle fréquence</h2>
	<form action='<?php $this->url("Daemon/doEditFrequence") ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<table class='table table-striped'>
			<tr>
				<th class='w200'>
                    <label for="type_connecteur">Type de connecteur</label>
                </th>
				<td>
                    <select name="type_connecteur" id="type_connecteur">
                        <option value="all">Tous les types</option>
                        <option value="global">Connecteur global</option>
                        <option value="entite">Connecteur d'entité</option>
                    </select>
				</td>
			</tr>
            <tr>
                <th class='w200'>
                    <label for="famille_connecteur">Famille de connecteur</label>
                </th>
                <td>
                    <select name="famille_connecteur" id="famille_connecteur">
                    </select>
                </td>
            </tr>
		</table>
	</form>

</div>

<script type="text/javascript">
$(document).ready(function() {
   $("#type_connecteur").change(function(){
       console.log("toto");
   })
});
</script>