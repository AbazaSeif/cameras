<?php
namespace App\Services;

use App\Deployment;
use GrahamCampbell\GitHub\Facades\GitHub;

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
            }
        }
    }

    public function run(Deployment $deployment) {
        var_dump($deployment->creator);
    }
}