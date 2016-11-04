@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Create new group</div>
                    <div class="panel-body">
                        {!! Form::open(['route' => 'groups.store']) !!}
                        {!! Form::text('name') !!}
                        {!! Form::submit('Create group') !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
