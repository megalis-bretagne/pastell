<?php include(__DIR__."/DaemonMenu.php");?>


<?php $this->SuivantPrecedent($offset,$limit,$count,"Daemon/job?filtre=$filtre");?>

<?php include(__DIR__."/DaemonJobList.php")?>
