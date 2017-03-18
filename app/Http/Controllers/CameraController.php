<?php

namespace App\Http\Controllers;

use App\Camera;
use App\Http\Requests\CameraRequest;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    public function index(Camera $camera) {
        $cameras = $camera->paginate();
        return view('cameras.index', compact('cameras'));
    }

    public function create() {
        return view('cameras.create');
    }

    public function store(CameraRequest $request, Camera $camera) {
        $camera = $camera->create($request->all());
        return redirect(route('cameras.edit', $camera))->with('success', 'Camera data saved successfully!');
    }

    public function edit(Camera $camera) {
        return view('cameras.edit', compact('camera'));
    }

    public function update(CameraRequest $request, Camera $camera) {
        $params = strlen($request->get('password')) ? $request->all() : $request->except(['password']);
        $camera = $camera->update($params);
        return redirect(route('cameras.edit', $camera))->with('success', 'Camera data updated successfully!');
    }

    public function delete(Camera $camera) {
        $camera->delete();
        return redirect(route('cameras'))->with('success', 'Camera data removed successfully!');
    }

    public function proxy(Camera $camera) {
        try {
            $boundary = "myboundary";
            $credentials = null;
            if (strlen($camera->user) && strlen($camera->password)) {
                $credentials = "{$camera->user}:{$camera->password}@";
            }
            $address = "{$camera->ip}:{$camera->port}/video.mjpg";
            header("Cache-Control: no-cache");
            header("Cache-Control: private");
            header("Pragma: no-cache");
            header("Content-type: multipart/x-mixed-replace; boundary=$boundary");
            return readfile("http://{$credentials}$address", "rb");
        } catch (\Throwable $e) {
            $image = 'images/video_unknown_error.gif';
            if(str_contains($e->getMessage(),'Unauthorized')) {
                $image = 'images/video_auth_fail.gif';
            }
            if(str_contains($e->getMessage(),'Connection timed out')) {
                $image = 'images/video_timed_out.gif';
            }
            if(str_contains($e->getMessage(),'Connection refused')) {
                $image = 'images/video_refused.gif';
            }
            return response()->file(public_path($image), [
                'Cache-Control' => 'private',
                'Pragma' => 'no-cache',
                'Content-type' => 'image/gif'
            ]);
        }
    }
}
