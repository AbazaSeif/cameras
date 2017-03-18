@extends('layouts.app')

@section('content')
    @include('cameras.partials.add_btn')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Label</th>
            <th>Ip</th>
            <th>Port</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
        @forelse($cameras as $camera)
            <tr>
                <td>{{ $camera->label }}</td>
                <td>{{ $camera->ip }}</td>
                <td>{{ $camera->port }}</td>
                <td>
                    @include('cameras.partials.edit_btn')
                </td>
                <td>
                    @include('cameras.partials.delete_btn')
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No records found</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection