<?php
namespace App\Services;

use App\Deployment;
use GrahamCampbell\GitHub\Facades\GitHub;
use Symfony\Component\Process\Process;

class DeployManager
{
    protected $client;

    public function __construct()
    {
        $this->client = GitHub::api('deployment');
    }

    public function checkDeploys($user, $branch) {
        $deployments = collect($this->client->all($user, $branch))->sortByDesc('id');
        foreach ($deployments as $deployment) {
            $exists = Deployment::find($deployment['id']);
            if (!$exists) {
                $this->run(new Deployment($deployment));
                dd('ok');
            }
        }
    }

    public function run(Deployment $deployment) {
        $path = base_path();
        $command = "/home/vagrant/.config/composer/vendor/bin/envoy run deploy --branch={$deployment->ref}";
        $process = new Process($command, $path);
        $process->run(function ($type, $buffer) {
            var_dump($buffer);
        });
    }
}