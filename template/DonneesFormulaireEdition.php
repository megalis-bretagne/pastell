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
if (isset($inject['key'])){
	$mailsec_key = $inject['key'];
} else {
	$mailsec_key = '';
}

?>
		<form action='<?php echo $action_url ?>' method='post' enctype="multipart/form-data" id="document_edition">
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
					<?php elseif($field->getType() == 'textarea') : ?>
						<textarea class='textarea_affiche_formulaire' rows='10' cols='40' id='<?php echo $field->getName();?>'  name='<?php echo $field->getName()?>' <?php echo $donneesFormulaire->isEditable($field->getName())?:"disabled='disabled'" ?>><?php echo $this->donneesFormulaire->get($field->getName(),$field->getDefault())?></textarea>
					<?php elseif($field->getType() == 'file') :?>
							<?php if ($donneesFormulaire->isEditable($field->getName())) : ?>
								<?php if ($field->isMultiple()) : ?>

								<?php if (! $field->getProperties('progress_bar')):  ?>

                    <input type='file' id='<?php echo $field->getName();?>'  name='<?php echo $field->getName()?>[]' multiple />
                        <button type='submit' name='ajouter' class='btn' value='Ajouter'><i class='icon-plus'></i>Ajouter</button>
                    <br/>
                                <?php else: ?>

                        <div class="pastell-flow-upload" id="pastell-flow-upload-<?php echo $field->getName() ?>"></div>
                        <script>
                            $(document).ready(function(){
                                var query_param = {
                                    target: '<?php echo $mailsec_key?'chunk_upload.php':'/Document/chunkUpload'; ?>',
                                    id_e: '<?php echo $id_e ?>',
                                    id_d: '<?php echo $id_d ?>',
                                    page: '<?php echo $page ?>',
                                    field: '<?php echo $field->getName() ?>',
                                    key: '<?php echo $mailsec_key ?>',
                                    token_value: '<?php  echo $this->getCSRFToken()->getCSRFToken(); ?>',
                                    single_file: false
                                };
                                addFlowControl(query_param,$("#pastell-flow-upload-<?php echo $field->getName(); ?>"));
                            });
                        </script>

                                <?php endif; ?>





								<?php elseif (! $this->donneesFormulaire->get($field->getName())) : ?>
                                    <?php if ($field->getProperties('progress_bar')):  ?>
                                        <div class="pastell-flow-upload" id="pastell-flow-upload-<?php echo $field->getName() ?>"></div>
                                        <script>
                                            $(document).ready(function(){
                                                var query_param = {
                                                    target: '<?php echo $mailsec_key?'chunk_upload.php':'/Document/chunkUpload'; ?>',
                                                    id_e: '<?php echo $id_e ?>',
                                                    id_d: '<?php echo $id_d ?>',
                                                    page: '<?php echo $page ?>',
                                                    field: '<?php echo $field->getName() ?>',
                                                    key: '<?php echo $mailsec_key ?>',
                                                    token_value: '<?php  echo $this->getCSRFToken()->getCSRFToken(); ?>',
                                                    single_file: true
                                                };
                                                addFlowControl(query_param,$("#pastell-flow-upload-<?php echo $field->getName(); ?>"));
                                            });
                                        </script>
                                    <?php else: ?>
                                        <input type='file' id='<?php echo $field->getName();?>'  name='<?php echo $field->getName()?>'  />
                                    <?php endif; ?>

								<?php endif; ?>
							<?php endif;?>
							<?php if ($this->donneesFormulaire->get($field->getName())) : 
									foreach($this->donneesFormulaire->get($field->getName()) as $num => $fileName ): ?>
											<a href='<?php echo $recuperation_fichier_url ?>&field=<?php echo $field->getName()?>&num=<?php echo $num ?>'><?php echo $fileName ?></a>
											&nbsp;&nbsp;
											<?php if ($donneesFormulaire->isEditable($field->getName())) : ?>
												<a style='margin:4px 0' class='btn btn-mini btn-danger' href='<?php echo $suppression_fichier_url ?>&field=<?php echo $field->getName() ?>&num=<?php echo $num ?>'>Supprimer</a>
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
								<a href='<?php echo  $externalDataURL ?>?id_ce=<?php echo $id_ce ?>&field=<?php echo $field->getName()?>'><?php echo $field->getProperties('link_name')?></a>
							<?php elseif($field->isEnabled($id_e,$id_d) && isset($id_e)) :?>
								<a href='<?php echo  $externalDataURL ?>?id_e=<?php echo $id_e ?>&id_d=<?php echo $id_d ?>&page=<?php echo $page_number?>&field=<?php echo $field->getName()?>'><?php echo $field->getProperties('link_name')?></a>
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
							<?php echo $this->donneesFormulaire->geth($field->getName())?>&nbsp;
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
				<input type='submit' name='precedent' class='btn' value='« Précédent' />
			<?php endif; ?>
			<input type='submit' name='enregistrer' class='btn' value='Enregistrer' id="donnees_formulaire_edition_enregister"/>
			<?php if ( ($donneesFormulaire->getFormulaire()->getNbPage() > 1) && ($donneesFormulaire->getFormulaire()->getNbPage() > $page_number + 1)): ?>
				<input type='submit' name='suivant' class='btn' value='Suivant »' />
			<?php endif; ?>
		</form>
