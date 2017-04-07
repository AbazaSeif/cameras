<?php

namespace App\Console\Commands;

use App\Deployment;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iris:deploy {--branch=master} {--cleanup=true} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy project';

    protected $user;

    protected $repository;

    protected $branches = [];

    protected $deployment;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $repository = env('DEPLOY_REPOSITORY');
        $pieces = explode('/', $repository);
        $this->repository = str_replace('.git', '', array_pop($pieces));
        $base = explode(':', array_pop($pieces));
        $this->user = array_pop($base);
        if ($this->hasValidInfo()) {
            $this->deployment = $this->checkDeploys();
            $this->branches = $this->getBranches();
        }
    }

    public function hasValidInfo(){
        return (strlen($this->user) && strlen($this->repository));
    }

    public function checkDeploys() {
        return collect(GitHub::api('deployment')->all($this->user, $this->repository))->sortByDesc('id')->first();
    }

    public function getBranches() {
        return array_column(GitHub::api('repository')->branches($this->user, $this->repository), 'name');
    }

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {

        $exists = Deployment::find($this->deployment['id']);
        $path = base_path();
        if (!$exists) {
            $deploy = new Deployment($this->deployment);
            if(in_array($deploy->ref, $this->branches) && !$this->option('force')) {
                $branch = $deploy->ref;
            } else {
                $branch =  $this->option('branch');
            }
            $cleanup = $this->option('cleanup');
            $envoy = env('ENVOY_PATH');
            if (is_null($envoy)) {
                throw new \Exception('Envoy path is required');
            }
            $command =  env('ENVOY_PATH') . " run deploy --branch={$branch} --cleanup={$cleanup}";
            $process = new Process($command, $path);
            $process->setTimeout(1800);
            $process->setIdleTimeout(300);
            $process->run(function ($type, $buffer) {
                $this->info($buffer);
            });
            if($process->isSuccessful()) {
                $deploy->save();
            }
        } else {
            $this->info("Project is up to date");
        }
    }
}
