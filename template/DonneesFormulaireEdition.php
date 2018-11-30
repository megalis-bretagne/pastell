<?php
/** @var Gabarit $this */
/** @var  $page */
/** @var  DonneesFormulaire $donneesFormulaire */
/** @var  array $inject */
/** @var  $action_url */
/** @var  $my_role */
/** @var $recuperation_fichier_url */
/** @var $suppression_fichier_url */
/** @var $externalDataURL */


$page_number = $page;

$donneesFormulaire->getFormulaire()->setTabNumber($page_number);

$id_ce = $inject['id_ce'];
$id_d = $inject['id_d'];
$action = $inject['action'];
$id_e = $inject['id_e'];
?>

<form action='<?php echo $action_url ?>' method='post' enctype="multipart/form-data" id="document_edition">


<?php
if ($donneesFormulaire->getFormulaire()->getNbPage() > 1 ) {
	?>
    <ul class="nav nav-pills" style="margin-top:10px;">
		<?php foreach ($donneesFormulaire->getFormulaire()->getTab() as $page_num => $name) : ?>
            <li <?php echo ($page_num == $page)?'class="active"':'' ?>>
                <button type="submit" class="btn-link" name="enregistrer" value="<?php echo $page_num ?>">
                    <?php echo ($page_num + 1) . ". " . $name?>
                </button>

            </li>
		<?php endforeach;?>
    </ul>
	<?php
}
?>
<div class="box">

			<?php $this->displayCSRFInput() ?>
            <!-- prevent autocomplete -->
            <input style="display:none">
            <input type="password" style="display:none">

			<input type='hidden' name='page' value='<?php echo $page_number?>' />
			<?php foreach($this->inject as $name => $value ) : ?>
				<input type='hidden' name='<?php hecho($name); ?>' value='<?php hecho($value); ?>' />
			<?php endforeach;?>
			
			<table class='table table-striped'>
			<?php
			/** @var FieldData $fieldData */
			foreach ($donneesFormulaire->getFieldDataList($my_role,$page_number) as $fieldData) :

				$field = $fieldData->getField();						
				if ($field->getProperties('read-only') && $field->getType() == 'file'){
					continue;
				}
			?>
				<tr>
					<th class='w500'>
						<label for="<?php echo $field->getName() ?>"><?php echo $field->getLibelle() ?><?php if ($field->isRequired()) : ?><span class='obl'>*</span><?php endif;?></label>
						
						<?php if ($field->isMultiple()): ?>
							(plusieurs <?php echo ($field->getType() == 'file')?"ajouts":"valeurs" ?> possibles)
						<?php endif;?>
						<?php if ($field->getProperties('commentaire')) : ?>
							<p class='form_commentaire'><?php echo $field->getProperties('commentaire') ?></p>
						<?php endif;?>
					</th>
					<td> 
					
					<?php if ($field->getType() == 'checkbox') :?>
						<?php if ($field->getProperties('depend') && $this->donneesFormulaire->get($field->getProperties('depend'))) : 
						
						?>
							<?php foreach($this->donneesFormulaire->get($field->getProperties('depend')) as $i => $file) :  ?>
								<input type='checkbox' name='<?php echo $field->getName()."_$i"; ?>' id='<?php echo $field->getName()."_$i";?>' 
									<?php echo $this->donneesFormulaire->geth($field->getName()."_$i")?"checked='checked'":'' ?>
									<?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>
									/><?php echo $file ?> 
									<br/>
							<?php endforeach;?>
						<?php else:?>
							<input type='checkbox' name='<?php echo $field->getName(); ?>' id='<?php echo $field->getName();?>' 
									<?php echo $this->donneesFormulaire->geth($field->getName())?"checked='checked'":'' ?>
									<?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>
									/>
						<?php endif; ?>
					<?php elseif($field->getType() == 'textarea' && (! $field->getProperties('read-only'))) : ?>
						<textarea class='textarea_affiche_formulaire' rows='10' cols='40' id='<?php echo $field->getName();?>'  name='<?php echo $field->getName()?>' <?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>><?php echo $this->donneesFormulaire->get($field->getName(),$field->getDefault())?></textarea>
					<?php elseif($field->getType() == 'file') :?>
							<?php if ($donneesFormulaire->isEditable($field->getName())) : ?>
								<?php if ($field->isMultiple()) : ?>
									<input type='file' id='<?php echo $field->getName();?>'  name='<?php echo $field->getName()?>[]' multiple />
									<button type='submit' name='ajouter' class='btn' value='Ajouter'><i class='fa fa-plus-circle'></i>&nbsp;Ajouter</button>
									<br/>
								<?php elseif (! $this->donneesFormulaire->get($field->getName())) : ?>
									<input type='file' id='<?php echo $field->getName();?>'  name='<?php echo $field->getName()?>' />
									<br/>
								<?php endif; ?>
							<?php endif;?>
							<?php if ($this->donneesFormulaire->get($field->getName())) : 
									foreach($this->donneesFormulaire->get($field->getName()) as $num => $fileName ): ?>
											<a href='<?php echo $recuperation_fichier_url ?>&field=<?php echo $field->getName()?>&num=<?php echo $num ?>'><?php echo $fileName ?></a>
											&nbsp;&nbsp;
											<?php if ($donneesFormulaire->isEditable($field->getName())) : ?>
												<a style='margin:4px 0' class='btn btn-danger' href='<?php echo $suppression_fichier_url ?>&field=<?php echo $field->getName() ?>&num=<?php echo $num ?>'>
                                                    <i class="fa fa-trash"></i>
                                                    &nbsp;Supprimer
                                                </a>
											<?php endif;?>
										<br/>
							<?php endforeach;?>
							<?php endif;?>
					<?php elseif(($field->getType() == 'select') && ! $field->getProperties('read-only')) : ?>
					
						<?php if ($field->getProperties('depend') && $this->donneesFormulaire->get($field->getProperties('depend'))) : 
						
						?>
							<?php foreach($this->donneesFormulaire->get($field->getProperties('depend')) as $i => $file) :  ?>

									<br/>
									<?php echo $file ?>  <select name='<?php echo $field->getName()."_$i";?>' <?php echo $donneesFormulaire->isEditable($field->getName()."_$i")?:"disabled='disabled'" ?>>
							<option value=''>...</option>
							<?php foreach($field->getSelect() as $value => $name) : ?>
								<option <?php 
									if ($this->donneesFormulaire->geth($field->getName()."_$i") == $value){
										echo "selected='selected'";
									}
								?> value='<?php echo $value ?>'><?php echo $name ?></option>
							<?php endforeach;?>
						</select>
							<?php endforeach;?>
                        <?php else :?>
                            <select id='<?php echo $field->getName()?>' name='<?php echo $field->getName()?>' <?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>>
                                <option value=''>...</option>
                                <?php foreach($field->getSelect() as $value => $name) : ?>
                                    <option <?php
                                        if ($this->donneesFormulaire->geth($field->getName()) == $value){
                                            echo "selected='selected'";
                                        }
                                    ?> value='<?php echo $value ?>'><?php echo $name ?></option>
                                <?php endforeach;?>
                            </select>
                        <?php endif;?>
					<?php elseif ($field->getType() == 'externalData') :?>
						<?php if ($donneesFormulaire->isEditable($field->getName())) : ?>
							<?php if($id_ce) : ?>
                                <button type="submit" class="btn" name="external_data_button" value="<?php echo  urlencode("$externalDataURL?id_ce=$id_ce&field=".$field->getName()); ?>">
                                    <i class="fa fa-hand-o-up"></i>&nbsp; <?php echo $field->getProperties('link_name')?>
                                </button>
							<?php elseif($field->isEnabled($id_e,$id_d) && isset($id_e)) :?>
                                <button type="submit" class="btn" name="external_data_button" value="<?php echo  urlencode("$externalDataURL?id_e=$id_e&id_d=$id_d&page=$page_number&field=".$field->getName()); ?>">
                                    <i class="fa fa-hand-o-up"></i>&nbsp; <?php echo $field->getProperties('link_name')?>
                                </button>
					<?php else:?>
								non disponible
							<?php endif;?>
						<?php endif;?>
						<?php echo $this->donneesFormulaire->get($field->getName())?>&nbsp;
					<?php elseif ($field->getType() == 'password') : ?>
						<input 	type='password'
								id='<?php echo $field->getName();?>' 
								name='<?php echo $field->getName(); ?>' 
								value='' 
								size='16'
								
                                  autocomplete="new-password-42"
								<?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>
						/>
					<?php elseif( $field->getType() == 'link') : ?>
						<?php if ($donneesFormulaire->isEditable($field->getName())) : ?>
							<a href='<?php echo SITE_BASE . $field->getProperties('script')?>?id_e=<?php echo $id_e?>'><?php echo $field->getProperties('link_name')?></a>
						<?php else: ?>
							<?php echo $field->getProperties('link_name')?>
						<?php endif;?>				
					<?php else : ?>
						<?php if ($field->getProperties('read-only')) : ?>
							<?php echo $this->donneesFormulaire->geth($field->getName()) ?>&nbsp;
							<input type='hidden' name='<?php echo $field->getName(); ?>' value='<?php echo $this->donneesFormulaire->geth($field->getName())?>'/>
						<?php elseif( $field->getType() == 'date') : ?>
							
						<input 	type='text' 	
								id='<?php echo $field->getName();?>' 
								name='<?php echo $field->getName(); ?>' 
								value='<?php echo date_iso_to_fr($this->donneesFormulaire->geth($field->getName(),$field->getDefault()))?>' 
								size='40'
								<?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>
								/>
														
							<script type="text/javascript">
						   		 jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
								$(function() {
									$("#<?php echo $field->getName()?>").datepicker( { dateFormat: 'dd/mm/yy' });
									
								});
							</script>
						<?php else : ?>
						<input 	type='text' 	
								id='<?php echo $field->getName();?>' 
								name='<?php echo $field->getName(); ?>' 
								value='<?php echo $this->donneesFormulaire->geth($field->getName(),$field->getDefault())?>' 
								size='40'
								<?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>
								/>
						<?php endif;?>
						<?php if ($field->getProperties('autocomplete')) : ?>
						 <script>
 							 $(document).ready(function(){
									$("#<?php echo $field->getName();?>").pastellAutocomplete("<?php echo $field->getProperties('autocomplete')?>",<?php echo $id_e?>,false);
							});
						</script>
						<?php endif;?>
					<?php endif;?>						
					</td>
				</tr>				
			<?php 	endforeach; ?>
			</table>
		
			<?php if ($page_number > 0 ): ?>
                <button type="submit" class="btn" name="precedent" value="precedent">
                    <i class="fa fa-arrow-left"></i>&nbsp;Précédent
                </button>
			<?php endif; ?>
            <button type="submit" class="btn" name="enregistrer" value="enregistrer">
                <i class="fa fa-floppy-o"></i>&nbsp; Enregistrer
            </button>
			<?php if ( ($donneesFormulaire->getFormulaire()->getNbPage() > 1) && ($donneesFormulaire->getFormulaire()->getNbPage() > $page_number + 1)): ?>
                <button type="submit" class="btn" name="suivant" value="suivant" id="suivant">
                    <i class="fa fa-arrow-right"></i>&nbsp;Suivant
                </button>
			<?php endif; ?>


</div>

</form>