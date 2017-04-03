<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'sha',
        'ref',
        'task',
        'payload',
        'environment',
        'description',
        'creator',
        'statuses_url',
        'repository_url'
    ];

    protected function setPayloadAttribute($value) {
        $this->attributes['payload'] = json_encode($value);
    }

    protected function setCreatorAttribute($value) {
        $this->attributes['creator'] = json_encode($value);
    }

    protected function getPayloadAttribute() {
        return json_decode($this->attributes['payload']);
    }

    protected function getCreatorAttribute() {
        return json_decode($this->attributes['creator']);
    }
}
