{!! Form::open(['route' => ['cameras.delete', $camera], 'method' => 'DELETE', 'class' => 'form-horizontal']) !!}
<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
{!! Form::close() !!}