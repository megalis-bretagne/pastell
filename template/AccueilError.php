<p>Une erreur est survenue !</p>

<?php /** @var Exception $the_exception */ if($the_exception): ?>
<div class="alert alert-error">
	<?php hecho($the_exception->getMessage()) ?>
</div>
<?php endif; ?>

