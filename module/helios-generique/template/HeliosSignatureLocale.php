
<a class='btn btn-mini' href='document/edition.php?id_d=<?php echo $id_d?>&id_e=<?php echo $id_e?>&page=<?php echo $page?>'><i class='icon-circle-arrow-left'></i><?php echo $info['titre']? $info['titre']:$info['id_d']?></a>


<?php
/** @var $libersignConnecteur Libersign */
$libersignConnecteur->displayLibersignJS();
/** @var $signatureInfo array */
/** @var DonneesFormulaire $libersign_properties */
?>

<script>
    $(window).load(function() {

        $(document).ready(function () {

            $("#box_result").hide();

            var siginfos = [];

            siginfos.push({
                hash: "<?php echo ($signatureInfo['isbordereau'] == true) ? $signatureInfo['bordereau_hash'] : $signatureInfo['flux_hash'] ?>",
                pesid: "<?php echo ($signatureInfo['isbordereau'] == true) ? $signatureInfo['bordereau_id'] : $signatureInfo['flux_id'] ?>",
                pespolicyid: "urn:oid:1.2.250.1.131.1.5.18.21.1.4",
                pespolicydesc: "Politique de signature Helios de la DGFiP",
                pespolicyhash: "Jkdb+aba0Hz6+ZPKmKNhPByzQ+Q=",
                pespuri: "https://portail.dgfip.finances.gouv.fr/documents/PS_Helios_DGFiP.pdf",
                pescity: "<?php hecho($libersign_properties->get('libersign_city'))?>",
                pespostalcode: "<?php hecho($libersign_properties->get('libersign_cp'))?>",
                pescountryname: "France",
                pesclaimedrole: "Ordonnateur",
                pesencoding: "iso-8859-1",
                format: "XADES-env"
            });

            $(".libersign").libersign({
                iconType: "glyphicon",
                signatureInformations: siginfos
            }).on('libersign.sign', function (event, signatures) {
                //console.log(signatures);
                $("#signature_1").val(signatures[0]);
                $("#form_sign").submit();
            });
        });
    });
</script>



<div id='box_signature' class='box' style="width:920px" >
    <h2>Signature</h2>
    <div class="libersign"></div>
</div>

<form action='document/external-data-controler.php' id='form_sign' method='post'>
    <input type='hidden' name='id_d' value='<?php echo $id_d?>' />
    <input type='hidden' name='id_e' value='<?php echo $id_e?>' />
    <input type='hidden' name='page' value='<?php echo $page?>' />
    <input type='hidden' name='field' value='<?php echo $field?>' />
    <input type='hidden' name='nb_signature'  value='1'/>
    <input type='hidden' name='signature_id_1' value='<?php echo ($signatureInfo['isbordereau'] == true ) ? $signatureInfo['bordereau_id'] : $signatureInfo['flux_id'] ?>' />
    <input type='hidden' name='signature_1' id='signature_1' value=''/>
    <input type='hidden' name='is_bordereau' id='is_bordereau' value='<?php echo $signatureInfo['isbordereau'] ?>'/>
</form>