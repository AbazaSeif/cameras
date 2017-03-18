@extends('layouts.app')

@section('content')
    <div class="col-md-offset-3 col-md-6">
        {!! Form::open(['route' => 'cameras.store', 'autocomplete' => 'off']) !!}
        <div class="panel">
            <div class="panel-heading">
                Register a camera
            </div>
            <div class="panel-body">
                @include('cameras.partials.form_fields')
            </div>
            <div class="panel-footer text-right">
                {!! Form::submit('Save', ['class' => 'btn btn-block btn-primary']) !!}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection