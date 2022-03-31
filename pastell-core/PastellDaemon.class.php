<?php

class PastellDaemon
{
    private $workerSQL;
    private $jobQueueSQL;
    private $actionExecutorFactory;
    private $document;
    private $notificationMail;
    private $daemon_log_file;
    private $logger;

    public function __construct(
        WorkerSQL $workerSQL,
        JobQueueSQL $jobQueueSQL,
        ActionExecutorFactory $actionExecutorFactory,
        Document $document,
        NotificationMail $notificationMail,
        $daemon_log_file,
        Monolog\Logger $logger
    ) {
        $this->workerSQL = $workerSQL;
        $this->jobQueueSQL = $jobQueueSQL;
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->document = $document;
        $this->notificationMail = $notificationMail;
        $this->daemon_log_file = $daemon_log_file;
        $this->logger = $logger;
    }

    public function jobMaster()
    {
        $this->logger->addInfo("Daemon starting");

        if (UNLOK_JOB_ERROR_AT_STARTUP) {
            //ajout d'un flag "UNLOK_JOB_ERROR_AT_STARTUP" pour ne pas verrouiller les jobs qui ne se sont pas terminés correctement.
            //suite à un arrêt brutal du serveur (ex: restart apache sans avoir arrêté le daemon avec des worker actifs). (r1992)
            $workerSQL = $this->workerSQL;
            foreach ($workerSQL->getAllRunningWorker() as $info) {
                if (! posix_getpgid($info['pid'])) {
                    $workerSQL->success($info['id_worker']); // supprime le worker
                    $this->logger->alert("Daemon detects and cleans a worker that did not end correctly", $info);
                }
            }
        }

        while (true) {
            $this->jobMasterOneRun();
            sleep(1);
        }
    }

    public function runningWorker()
    {
        try {
            $this->runningWorkerThrow();
        } catch (Exception $e) {
            $this->logger->error("Worker ends with an error : " . $e->getMessage());
            return;
        }
        $this->logger->info("Worker " . getmypid() . " exits normally");
    }

    private function runningWorkerThrow()
    {
        $id_job = get_argv(1);
        if (! $id_job) {
            global $argv;
            echo "Usage : {$argv[0]} id_job";
            return;
        }
        $this->logger->pushProcessor(function ($record) use ($id_job) {
            $record['extra']['id_job'] = $id_job;
            return $record;
        });
        $this->launchJob($id_job);
    }


    private function launchWorker($id_job)
    {
        $job = $this->jobQueueSQL->getJob($id_job);
        if (! $job) {
            return;
        }

        if (! $job->isTypeOK()) {
            throw new Exception("Ce type de job n'est pas traité par ce worker");
        }

        $workerSQL = $this->workerSQL;
        $another_worker_info = $workerSQL->getRunningWorkerInfo($id_job);
        if ($another_worker_info) {
            throw new Exception("Le job $id_job est déjà attaché au worker  #{$another_worker_info['id_worker']}");
        }

        //Le master lock le job jusqu'à ce que son worker le délock pour éviter que le master ne sélectionne à nouveau ce job (si le lancement du worker est plus lent que la boucle du master)
        $this->jobQueueSQL->lock($id_job);

        $script = realpath(__DIR__ . "/../batch/pastell-job-worker.php");
        $command = "nohup " . PHP_PATH . " $script $id_job >>  {$this->daemon_log_file} 2>&1 &";
        $this->logger->addInfo("Daemon starts worker for job #$id_job : " . json_encode($job));
        exec($command);
    }

    private function jobMasterOneRun()
    {
        $workerSQL = $this->workerSQL;

        foreach ($workerSQL->getAllRunningWorker() as $info) {
            if (! posix_getpgid($info['pid'])) {
                $workerInfo = $workerSQL->getInfo($info['id_worker']);
                if (!$workerInfo || $workerInfo['termine'] === '1') {
                    $this->logger->addWarning("Worker has already finished his job, Skipping...", $info);
                    continue;
                }
                $this->jobQueueSQL->lock($info['id_job']);
                $workerSQL->error($info['id_worker'], "Message du job master : ce worker ne s'est pas terminé correctement");
                $this->logger->addError("Daemon detects a dead worker", $info);
            }
        }
        $nb_worker_alive = count($workerSQL->getAllRunningWorker());

        $nb_worker_to_launch = NB_WORKERS - $nb_worker_alive;

        $job_id_list = $workerSQL->getJobToLaunch($nb_worker_to_launch);

        foreach ($job_id_list as $id_job) {
            $this->launchWorker($id_job);
        }
    }

    private function launchJob($id_job)
    {
        $this->logger->info("Worker " . getmypid() . " looks for job $id_job");
        $job = $this->jobQueueSQL->getJob($id_job);
        if (! $job) {
            throw new Exception("Aucun job trouvé pour l'id_job $id_job");
        }

        if (! $job->isTypeOK()) {
            throw new Exception("Ce type de job n'est pas traité par ce worker");
        }

        $this->logger->pushProcessor(function ($record) use ($job) {
            $record['extra']['id_verrou'] = $job->id_verrou;
            return $record;
        });

        $workerSQL = $this->workerSQL;

        $another_worker_info = $workerSQL->getRunningWorkerInfo($id_job);
        if ($another_worker_info) {
            throw new Exception("Le job $id_job est déjà attaché au worker  #{$another_worker_info['id_worker']}");
        }

        $pid = getmypid();
        $id_worker = $workerSQL->create($pid);
        $workerSQL->attachJob($id_worker, $id_job);
        $this->jobQueueSQL->unlock($id_job);

        if ($job->type == Job::TYPE_DOCUMENT) {
            $this->actionExecutorFactory->executeOnDocument($job->id_e, $job->id_u, $job->id_d, $job->etat_cible, [], true, [], $id_worker);
        } elseif ($job->type == Job::TYPE_TRAITEMENT_LOT) {
            $result = $this->actionExecutorFactory->executeOnDocument($job->id_e, $job->id_u, $job->id_d, $job->etat_cible, [], true, [], $id_worker);
            if (!$result) {
                $info = $this->document->getInfo($job->id_d);
                $message = "Echec de l'execution de l'action dans la cadre d'un traitement par lot : " . $this->actionExecutorFactory->getLastMessage();
                $this->logger->addError($message . ' ' . $job->asString());
                $this->notificationMail->notify($job->id_e, $job->id_d, $job->etat_cible, $info['type'], $message);
            }
        } elseif ($job->type == Job::TYPE_CONNECTEUR) {
            $this->actionExecutorFactory->executeOnConnecteur($job->id_ce, $job->id_u, $job->etat_cible, true, [], $id_worker);
        } else {
            throw new Exception("Type de job {$job->type} inconnu");
        }

        $workerSQL->success($id_worker);
    }
}
