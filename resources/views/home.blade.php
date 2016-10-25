@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Home</div>

				<div class="panel-body">
                    <img src="{{$avatar}}" alt="Character Portrait" class="pull-right">
                    <p>Welcome {{$name}}.</p>
                    @if($status == 'holder' || $status == 'blue' || $status == 'light-blue')
                        @if($email)
                            <p>Your registered email address is {{ $email }}</p>
                            @if($slackName)
                                <p>Your Slack username is {{ $slackName }}  <- If this says slackbot panic and/or contact Ortho.</p>
                                <p>Login to slack at <a href="https://provibloc.slack.com/">provibloc.slack.com</a></p>
                                <p>&nbsp;</p>
                                @if($groups && count($groups))
                                    <h4>You have admin rights on the following groups:</h4>
                                    <table class="table">
                                        @foreach($groups as $group)
                                            <tr>
                                                <td><a href="/admin/groups/{{$group->id}}">{{ $group->name }}</a></td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            @else
                                <p>Check your email and follow the instructions to sign up.</p>
                                <p>Once you are on slack, this system should notice within 5 minutes and give you
                                    appropriate group access. You can speed this up by typing '/register' on slack.</p>
                            @endif
                            @else
                            <p>Enter your email address to receive a slack invite:</p>
                            {!! Form::open(array('url' => 'form/addEmail')) !!}
                            {!! Form::email('email') !!}
                            {!! Form::submit('Send Slack Invite') !!}
                            {!! Form::close() !!}
                            <p>If you are already registered on slack, type '/register {{ $charId }}' in slack.</p>
                        @endif
                    @else
                        <p>You do not appear to have the correct standings on this character.</p>
                        <p>Corp: {{ $corp or 'API error. Try again in 5 minutes.'}}, Alliance: {{ $alliance or 'none' }}</p>
                        <p>If you have recently switched corps, then come back in an hour to see if the API has updated.</p>
                        <p>If you think this corp/alliance should have access, contact Zenith Bane regarding the access list.</p>
                    @endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
