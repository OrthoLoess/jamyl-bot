@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ $title }}</div>
                    <div class="panel-body">
                        @if (count($users))
                            <table class="table table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Char name</th>
                                    <th>Email</th>
                                    <th>Slack Name</th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->char_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->slack_name }}</td>
                                        <td>
                                            @unless ($user->admin)
                                                {!! Form::open(['action' => ['UserController@deleteIndex', 'id' => $user->id], 'method' => 'delete']) !!}
                                                {!! Form::submit('Delete') !!}
                                                {!! Form::close() !!}
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <p>No users to display.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
