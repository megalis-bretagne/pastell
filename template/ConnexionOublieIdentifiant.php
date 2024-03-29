<?php
/** @var Gabarit $this */
?>
<div class="w500">

<div class="box clearfix">


	<?php if ($config && $config->get("procedure_recup")) : ?>
	
		<h2>Information</h2>
		<p>
		<?php echo nl2br($config->get('message'))?>
		</p>
		<p>&nbsp;&nbsp;</p>
	<?php else : ?>

		
		<div class='alert alert-info'>
		Afin que nous puissions permettre la réinitialisation du mot de passe, 
		veuillez indiquer l'une des deux informations suivantes :
		</div>
		
		<form action='Connexion/doOublieIdentifiant' method='post' >
			<?php $this->displayCSRFInput() ?>
		<table>
		<tr>
		<th class="w50pc"><label for="login">Votre identifiant</label></th>
		<td class="w50pc"><input type="text" name="login" id="login" class='noautocomplete'/></td>
		</tr>
			<tr>
		<th class="w50pc"><label for="email"><b>OU</b> Votre email</label></th>
		<td class="w50pc"><input type="text" name="email" id="email" class='noautocomplete'/></td>
		</tr>
		</table>
		

		
		<div class="align_right">
		<input type="submit" value="Envoyer" class="btn" />
		</div>
		
		</form>
		
		<hr/>
		<div class="align_center">
		<a href="<?php $this->url("Connexion/connexion"); ?>">Retourner à la connexion</a>
		</div>
	
	<?php endif;?>

	
</div>
</div>