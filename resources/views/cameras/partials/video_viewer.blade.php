<div class="panel">
    <div class="panel-heading">Camera: {{ $camera->label }}</div>
    <div class="panel-body">
        <video-component source="{{ route('cameras.proxy', $camera) }}"></video-component>
        <div>
            <label for="address">http://{{ $camera->ip }}:{{ $camera->port }}/video.mjpg</label>
        </div>
    </div>
</div>