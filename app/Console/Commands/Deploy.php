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
    protected $signature = 'iris:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy project';

    protected $branches;

    protected $deployments;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->deployments = $this->checkDeploys('esalazarv', 'cameras');
        $this->branches = $this->getBranches('esalazarv', 'cameras');
    }

    public function checkDeploys($user, $branch) {
        return $deployments = collect(GitHub::api('deployment')->all($user, $branch))->sortByDesc('id');
    }

    public function getBranches($user, $branch) {
        return array_column(GitHub::api('repo')->branches($user, $branch), 'name');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->deployments as $deployment) {
            $exists = Deployment::find($deployment['id']);
            $path = base_path();
            if (!$exists) {
                $deploy = new Deployment($deployment);
                $branch = in_array($deploy->ref, $this->branches) ? "--branch={$deploy->ref}" : null;
                $command =  env('ENVOY_PATH', '~/.config/composer/vendor/bin/envoy') . " run deploy {$branch}";
                $process = new Process($command, $path);
                $process->setTimeout(1800);
                $process->setIdleTimeout(300);
                $process->run(function ($type, $buffer) {
                    $this->info($buffer);
                });
                if($process->isSuccessful()) {
                    $deploy->save();
                }
            }
        }
    }
}
