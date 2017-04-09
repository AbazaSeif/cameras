<?php

namespace App\Console\Commands;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Console\Command;

class CreateDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iris:create-deploy {--branch=master}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This create a deploy on github';

    protected $config;

    protected $repo;

    protected $envoy;

    protected $branches = [];

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->configure();
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

    protected  function hasValidConfig($repo) {
        return (strlen($repo->owner) && strlen($repo->name));
    }

    protected function getBranches() {
        return array_column(GitHub::api('repository')
            ->branches($this->repo->owner, $this->repo->name), 'name');
    }

    protected function isValidBranch($branch) {
        return in_array($branch, $this->getBranches());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $branch = $this->option('branch');
        if ($this->isValidBranch($branch) && $this->hasValidConfig($this->repo)) {
            $data = GitHub::api('deployment')->create($this->repo->owner, $this->repo->name, array('ref' => $branch));
            if(is_array($data) && isset($data['id'])){
                $this->info("Deployment for '{$branch}' branch was created with id: {$data['id']}.");
            }
        }
    }
}
