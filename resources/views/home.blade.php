@extends('layouts.app')

@section('content')
    @forelse($cameras->chunk(3) as $chunked)
    <div class="row">
        @foreach($chunked as $camera)
        <div class="col-md-4">
            @include('cameras.partials.video_viewer')
        </div>
        @endforeach
    </div>
    @empty
        <div class="row text-center">
            <i class="fa fa-video-camera fa-4x"></i>
            <h2>Not cameras found</h2>
            @include('cameras.partials.add_btn')
        </div>
    @endforelse
@endsection
