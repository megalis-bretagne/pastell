<?php
/**
 * @var Gabarit $this
 */

if ($this->getLastMessage()->getLastMessage()) : ?>
<div class="alert alert-success <?php echo $this->getLastError()->getCssClass()?>">
    <?php echo $this->getHTMLPurifier()->purify($this->getLastMessage()->getLastMessage()); ?>
</div>
<?php endif;?>

<?php if ($this->getLastError()->getLastError()) : ?>
<div class="alert alert-danger <?php echo $this->getLastError()->getCssClass()?>">
    <?php echo $this->getHTMLPurifier()->purify($this->getLastError()->getLastError()); ?>
</div>
<?php endif;?>
