<a class='btn btn-mini' href='Document/edition?id_d=<?php echo $id_d?>&id_e=<?php echo $id_e?>&page=<?php echo $page?>'><i class='icon-circle-arrow-left'></i><?php echo $info['titre']? $info['titre']:$info['id_d']?></a>

<?php
/** @var $libersignConnecteur Libersign */
$libersignConnecteur->displayLibersignJS();
/** @var $tab_included_files array */
?>

<script>
    $(window).load(function() {

        $(document).ready(function () {

            $("#box_result").hide();

            var siginfos = [];

			<?php foreach($tab_included_files as $i => $included_file) : ?>
            siginfos.push({
                hash: "<?php echo $included_file['sha1'] ?>",
                format: "CMS"
            });
			<?php endforeach;?>

            $(".libersign").libersign({
                iconType: "glyphicon",
                signatureInformations: siginfos
            }).on('libersign.sign', function (event, signatures) {
				<?php foreach($tab_included_files as $i => $included_file) : ?>
                $("#signature_<?php echo $i + 1?>").val(signatures[<?php echo $i ?>]);
				<?php endforeach;?>
                $("#form_sign").submit();
            });

        });
    });
</script>

<div id='box_signature' class='box' style="width:920px" >
    <h2>Signature</h2>
    <div class="libersign"></div>
</div>

<form action='Document/doExternalData' method='post' id='form_sign'>
    <?php $this->displayCSRFInput();?>
    <input type='hidden' name='id_d' value='<?php echo $id_d?>' />
    <input type='hidden' name='id_e' value='<?php echo $id_e?>' />
    <input type='hidden' name='page' value='<?php echo $page?>' />
    <input type='hidden' name='field' value='<?php echo $field?>' />
    <input type='hidden' name='id' id='form_sign_id' value='<?php echo $id_d?>'/>
    <input type='hidden' name='nb_signature'  value='<?php echo count($tab_included_files)?>'/>
	<?php foreach($tab_included_files as $i => $included_file) : ?>
        <input type='hidden' name='signature_id_<?php echo $i +1?>' value='<?php echo $included_file['id']?>' />
        <input type='hidden' name='signature_<?php echo $i +1?>' id='signature_<?php echo $i +1?>' value=''/>
	<?php endforeach;?>
</form>
