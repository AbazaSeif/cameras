<div class="form-group">
    {!! Form::label('label', 'Label') !!}
    {!! Form::text('label', null, ['class' => 'form-control']) !!}
    @if($errors->has('label'))
        <small class="text-danger">{{ $errors->first('label') }}</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('ip', 'IP') !!}
    {!! Form::text('ip', null, ['class' => 'form-control']) !!}
    @if($errors->has('ip'))
        <small class="text-danger">{{ $errors->first('ip') }}</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('port', 'Port') !!}
    {!! Form::text('port', null, ['class' => 'form-control']) !!}
    @if($errors->has('port'))
        <small class="text-danger">{{ $errors->first('port') }}</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('user', 'User') !!}
    {!! Form::text('user', null, ['class' => 'form-control']) !!}
    @if($errors->has('user'))
        <small class="text-danger">{{ $errors->first('user') }}</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('password', 'Password') !!}
    {!! Form::password('password', ['class' => 'form-control']) !!}
    @if($errors->has('password'))
        <small class="text-danger">{{ $errors->first('password') }}</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('password_confirmation', 'Password confirm') !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
</div>