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
    protected $signature = 'iris:deploy 
        {--branch= : The branch to deploy } 
        {--force : Force the deployment to last commit, not store a record a deploy in database } 
        {--no-cleanup : Not remove lasts releases  }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy project';

    protected $config;

    protected $repo;

    protected $envoy;

    protected $branches = [];

    protected $deployment;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->configure();
    }

    protected  function hasValidConfig($repo) {
        return (strlen($repo->owner) && strlen($repo->name));
    }

    protected function configure() {
        $this->config = config('deploy');
        $this->repo = (object) $this->config['repository'];
        $this->envoy = config('deploy.envoy', 'envoy');
        if (!$this->hasValidConfig($this->repo)) {
            $pieces = explode('/', $this->repo->url);
            $this->repo->name = str_replace('.git', '', array_pop($pieces));
            $pieces = explode(':', array_pop($pieces));
            $this->repo->owner = array_pop($pieces);
        }
    }

    protected function getDeployments() {
        return collect(GitHub::api('deployment')
            ->all($this->repo->owner, $this->repo->name));
    }

    protected function getMostRecentDeployment() {
        $data = $this->getDeployments()
            ->sortByDesc('id')
            ->first();
        return new Deployment($data);
    }

    protected function getBranches() {
        return array_column(GitHub::api('repository')
            ->branches($this->repo->owner, $this->repo->name), 'name');
    }

    protected function projectIsUpToDate($deployment_id) {
        $result = Deployment::find($deployment_id);
        return ($result instanceOf Deployment);
    }

    protected function isValidBranch($branch) {
        return in_array($branch, $this->getBranches());
    }

    protected function isDeployable($branch) {
        return true;
    }

    protected function buildOptions($branch) {
        $options[] =  "--branch={$branch}";
        if ($this->option('no-cleanup')) {
            $options[] = '--no-cleanup';
        }
        return implode(' ', $options);
    }

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->deployment = $this->getMostRecentDeployment();
        $branch = $this->deployment->ref;
        if ($this->option('force') || !$this->projectIsUpToDate($this->deployment->id)) {

            if(strlen($this->option('branch'))) {
                if(!$this->isValidBranch($this->option('branch'))) {
                    throw new \Exception("'{$this->option('branch')}' is not a valid branch");
                }
                $branch = $this->option('branch');
            } elseif (!$this->isValidBranch($branch)) {
                throw new \Exception("'{$this->option('branch')}' is not a valid branch");
            }

            if (!$this->isDeployable($branch)) {
                throw new \Exception("'{$branch}' has not an Envoy.blade.php file");
            }
            $options = $this->buildOptions($branch);
            $command =  $this->envoy . " run deploy {$options}";
            $process = new Process($command, base_path());
            $process->setTimeout(1800);
            $process->setIdleTimeout(300);
            $process->run(function ($type, $buffer) {
                $this->info($buffer);
            });
            if($process->isSuccessful() && !$this->option('force')) {
                $this->deployment->save();
            }
        } else {
            $this->info("Project is up to date");
        }
    }
}
