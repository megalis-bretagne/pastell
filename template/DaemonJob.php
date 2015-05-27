<?php include(__DIR__."/DaemonMenu.php");?>


<?php $this->SuivantPrecedent($offset,$limit,$count,"daemon/job.php?filtre=$filtre");?>

<?php include(__DIR__."/DaemonJobList.php")?>
