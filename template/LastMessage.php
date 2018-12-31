<?php 
if ($this->LastMessage->getLastMessage()) : ?>
<div class="alert alert-success <?php echo $this->LastError->getCssClass()?>">
	<?php echo $this->LastMessage->getLastMessage()?>
</div>
<?php endif;?>

<?php if ($this->LastError->getLastError()) : ?>
<div class="alert alert-danger <?php echo $this->LastError->getCssClass()?>">
	<?php echo $this->LastError->getLastError()?>
</div>
<?php endif;?>
