@extends('layouts.app')

@section('content')
    <div class="col-md-4">
        @include('cameras.partials.video_viewer')
    </div>
    <div class="col-md-8">
        <div class="panel">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-10">
                        Edit camera data
                    </div>
                    <div class="col-xs-2 text-right">
                        @include('cameras.partials.delete_btn')
                    </div>
                </div>
            </div>
            {!! Form::model($camera, ['route' => ['cameras.update', $camera], 'method' => 'PUT', 'autocomplete' => 'off']) !!}
            <div class="panel-body">
                @include('cameras.partials.form_fields')
            </div>
            <div class="panel-footer text-right">
                {!! Form::submit('Save', ['class' => 'btn btn-block btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection