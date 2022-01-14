<div class="box w700">
    <h2>Ce message est protégé par un mot de passe</h2>
    <form action='password-controler.php' method='post'>
        <input type='hidden' name='key' value='<?php

        hecho($the_key) ?>' />
        <table class='table table-striped '>
            <tr>
                <th class="w300">Veuillez saisir le mot de passe</th>
                <td ><input type='password' name='password' />
            </tr>
        </table>
        <input type='submit' class='btn btn-primary' />

    </form> 

</div>
