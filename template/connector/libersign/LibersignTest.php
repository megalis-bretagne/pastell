<?php

/**
 * @var Gabarit $this
 * @var Libersign $libersignConnecteur
 * @var int $id_ce
 * @var string $field
 */
?>

<a
        class='btn btn-mini'
        href='Connecteur/edition?id_ce=<?php echo $id_ce;?>'
>
    <i class="fa fa-arrow-left"></i>&nbsp;Retour au connecteur
</a>

<div id='box_signature' class='box' style="width:920px;" >
    <h2>Signature</h2>
    <div class="libersign">
        <?php $libersignConnecteur->displayLibersignJS(); ?>
    </div>
</div>

<form action='Connecteur/doExternalData' id='form_sign' method='post'>
    <?php $this->displayCSRFInput(); ?>
    <input type='hidden' name='id_ce' value='<?php echo $id_ce; ?>'/>
    <input type='hidden' name='field' value='<?php echo $field; ?>'/>
    <input type='hidden' name='publicCertificate' id='publicCertificate' value=''/>
    <input type='hidden' name='generatedDataToSign' id='generatedDataToSign' value=''/>
    <input type='hidden' name='dataToSignList' id='dataToSignList' value=''/>
</form>

<script>

    $(document).ready(function () {
        $("ls-lib-libersign")
            .on('sign', function (cert) {
                const publicCertificate = cert.detail.PUBKEY;
                console.log(publicCertificate);
                $("#publicCertificate").val(publicCertificate);

                $.post(
                    'Connecteur/doExternalDataApi',
                    {
                        id_ce: '<?php echo $id_ce; ?>',
                        field: '<?php echo $field; ?>',
                        publicCertificate: publicCertificate,
                        csrf_token: '<?php echo $this->getCSRFToken()->getCSRFToken(); ?>',
                    },
                    function (data, status) {
                        console.log(data);
                        let jsonObject = JSON.parse(data);
                        // console.log(jsonObject);
                        let dataToSign = jsonObject.dataToSignList.map(x => x.dataToSignBase64);

                        $("#generatedDataToSign").val(data);
                        $("ls-lib-libersign").attr("data-to-sign", JSON.stringify(dataToSign));
                    });
            })
            .on('signed', function (event) {
                console.log(event);
                $("#dataToSignList").val(JSON.stringify(event.detail));
                $("#form_sign").submit();
            });
    });
</script>
