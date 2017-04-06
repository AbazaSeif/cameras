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

    protected $user;

    protected $repository;

    protected $branches = [];

    protected $deployments = [];

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
            $this->branches = $this->getBranches();
        }
    }

    public function hasValidInfo(){
        return (strlen($this->user) && strlen($this->repository));
    }

    public function getBranches() {
        return array_column(GitHub::api('repository')->branches($this->user, $this->repository), 'name');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $branch = $this->option('branch');
        if (in_array($branch, $this->branches) && $this->hasValidInfo()) {
            $data = GitHub::api('deployment')->create($this->user, $this->repository, array('ref' => $branch));
            if(is_array($data) && isset($data['id'])){
                $this->info("Deployment for '{$branch}' branch was created with id: {$data['id']}.");
            }
        }
    }
}
