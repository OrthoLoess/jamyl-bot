@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Info for {{ $name }} ({{$id}})</h4>
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-12">
                            <h4>Linked Channels</h4>
                        </div>
                        <div class="col-sm-9">
                            @forelse ($channels as $channel)
                                <div>
                                    {!! Form::open(['url' => 'admin/groups/'.$id.'/remove-channel']) !!}
                                    {!! Form::button('<i class="glyphicon glyphicon-remove" style="vertical-align: baseline"></i>', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                                    {{ $channel->name }}
                                    {!! Form::hidden('channel', $channel->id) !!}
                                    {!! Form::close() !!}
                                </div>
                            @empty
                                <div>
                                    No linked channels
                                </div>
                            @endforelse
                        </div>
                        <div class="col-sm-3">
                            {!! Form::open(['url' => 'admin/groups/'.$id.'/add-channel', 'class' => 'channel-action-form']) !!}
                            {!! Form::select('channel', array(''=>'Select channel')+$menuChannels, null, array('class' => 'form-control')) !!}<br>
                            {!! Form::submit('Add', array('class' => 'btn btn-success btn-block')) !!}
                            {!! Form::close() !!}
                        </div>
                        <div class="col-sm-12"><hr></div>
                        @if ($admin)
                        <div class="col-sm-12">
                            <h4>Owners</h4>
                        </div>
                        <div class="col-sm-9">
                            @forelse ($owners as $owner)
                                <div>
                                    {!! Form::open(['url' => 'admin/groups/'.$id.'/remove-owner', 'class' => 'owner-action-form']) !!}
                                    {!! Form::button('<i class="glyphicon glyphicon-remove" style="vertical-align: baseline"></i>', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                                    {{ $owner->char_name }}
                                    {!! Form::hidden('owner', $owner->id) !!}
                                    {!! Form::close() !!}
                                </div>
                            @empty
                                <div>
                                    This group has no owners.
                                </div>
                            @endforelse
                        </div>
                        <div class="col-sm-3">
                            {!! Form::open(['url' => 'admin/groups/'.$id.'/add-owner', 'class' => 'add-owner-form']) !!}
                            {!! Form::select('owner', array(''=>'Select user')+$menuOwners, null, array('class' => 'form-control')) !!}<br>
                            {!! Form::submit('Add', array('class' => 'btn btn-success btn-block')) !!}
                            {!! Form::close() !!}
                        </div>
                        <div class="col-sm-12"><hr></div>
                        @endif
                        <div class="col-sm-12">
                            <h4>Corps</h4>
                        </div>
                        <div class="col-sm-9">
                            @forelse ($corps as $corp_id => $corp_name)
                                <div>
                                    {!! Form::open(['url' => 'admin/groups/'.$id.'/remove-corp', 'class' => 'owner-action-form']) !!}
                                    {!! Form::button('<i class="glyphicon glyphicon-remove" style="vertical-align: baseline"></i>', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                                    {{ $corp_name }}
                                    {!! Form::hidden('corp', $corp_id) !!}
                                    {!! Form::close() !!}
                                </div>
                            @empty
                                <div>
                                    This group has no corps.
                                </div>
                            @endforelse
                        </div>
                        <div class="col-sm-3">
                            {!! Form::open(['url' => 'admin/groups/'.$id.'/add-corp', 'class' => 'add-corp-form']) !!}
                            {!! Form::select('corp', array(''=>'Select corp')+$menuCorps, null, array('class' => 'form-control')) !!}<br>
                            {!! Form::submit('Add', array('class' => 'btn btn-success btn-block')) !!}
                            {!! Form::close() !!}
                        </div>
                        <div class="col-sm-12">
                            <hr>
                        </div>
                        <div class="col-sm-12">
                            <h4>Members</h4>
                        </div>
                        <div class="col-sm-9">
                            @forelse($users as $user)
                                @if (count($corps) == 0)
                                    {!! Form::open(['url' => 'admin/groups/'.$id.'/remove-user', 'class' => 'user-action-form']) !!}
                                    {!! Form::button('<i class="glyphicon glyphicon-remove" style="vertical-align: baseline"></i>', array('type' => 'submit', 'class' => 'btn btn-danger btn-xs')) !!}
                                    {{ $user->char_name }} @if($user->email) <{{ $user->email }}> @endif
                                    {!! Form::hidden('user', $user->id) !!}
                                    {!! Form::close() !!}
                                @else
                                    {{ $user->char_name }} @if($user->email) <{{ $user->email }}> @endif
                                @endif
                            @empty
                                <div>
                                    This group has no members.
                                </div>
                            @endforelse
                        </div>
                        <div class="col-sm-3">
                            @if (count($corps) == 0)
                                {!! Form::open(['url' => 'admin/groups/'.$id.'/add-user', 'class' => 'add-user-form']) !!}
                                {!! Form::select('user', array(''=>'Select user')+$menuUsers, null, array('class' => 'form-control')) !!}<br>
                                {!! Form::submit('Add user', array('class' => 'btn btn-success btn-block')) !!}
                                {!! Form::close() !!}
                            @else
                                Members are added through corp membership.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
