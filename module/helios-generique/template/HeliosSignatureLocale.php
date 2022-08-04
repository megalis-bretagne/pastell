<?php

/**
 * @var Gabarit $this
 * @var Libersign $libersignConnecteur
 * @var string $id_d
 * @var int $id_e
 * @var int $page
 * @var string $title
 * @var string $field
 */

?>

<a
        class='btn btn-mini'
        href='Document/edition?id_d=<?php echo $id_d; ?>&id_e=<?php echo $id_e; ?>&page=<?php echo $page; ?>'
>
    <i class="fa fa-arrow-left"></i>&nbsp;<?php hecho($title); ?>
</a>

<div id='box_signature' class='box' style="width:920px">
    <h2>Signature</h2>
    <div class="libersign">
        <?php $libersignConnecteur->displayLibersignJS(); ?>
    </div>
</div>

<form action='Document/doExternalData' id='form_sign' method='post'>
    <?php $this->displayCSRFInput(); ?>
    <input type='hidden' name='id_d' value='<?php echo $id_d; ?>'/>
    <input type='hidden' name='id_e' value='<?php echo $id_e; ?>'/>
    <input type='hidden' name='page' value='<?php echo $page; ?>'/>
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
                    'Document/doExternalDataApi',
                    {
                        id_d: '<?php echo $id_d; ?>',
                        id_e: '<?php echo $id_e; ?>',
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
