<div>
    <div class="box">
        <h2>Réinitialisation du mot de passe</h2>
        <br/><br/>
        <form class="form-horizontal" action='<?php $this->url("Connexion/doModifPassword") ?>' method='post'>
			<?php $this->displayCSRFInput() ?>
            <input type='hidden' name='mail_verif_password' value='<?php echo $mail_verif_password?>'/>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label align_right" for="password">Mot de passe *</label>
                <div class="col-sm-8">
                    <input type="password" name="password" id="password" placeholder="Mot de passe" class="form-control"/>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label align_right" for="password2">Mot de passe (confirmer)*</label>
                <div class="col-sm-8">
                    <input type="password" name="password2" id="password2" placeholder="Mot de passe" class="form-control"/>
                </div>
            </div>

            <div class="align_right">
                <button type="submit" class="btn btn-connect"><i class="fa fa-floppy-o"></i>&nbsp; Enregistrer</button>
            </div>
        </form>
        <div class="align_center">
            <a href="<?php $this->url("Connexion/connexion") ?>">Retourner sur la page de connexion</a>
        </div>
    </div>
</div>
