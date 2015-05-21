@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">All Groups</div>
                    <div class="panel-body">
                        <a class="pull-right btn btn-default" href="{{ url('admin/groups/create') }}">New</a>
                        @if (count($groups))
                            <table class="table table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Owners</th>
                                </tr>
                                </thead>
                            @foreach ($groups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->owners }}</td>
                                </tr>
                            @endforeach
                            </table>
                        @else
                            <p>No groups to display.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
