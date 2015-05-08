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
                    @if($email)
                        <p>Your registered email address is {{$email}}</p>
                        @if($slackName)
                            <p>Your Slack username is {{$slackName}}</p>
                        @else
                            <p>Type '/register' on slack to continue.</p>
                        @endif
                    @else
                        <p>Enter your email address to receive a slack invite:</p>
                        {!! Form::open(array('url' => 'form/addEmail')) !!}
                        {!! Form::email('email') !!}
                        {!! Form::submit('Send Slack Invite') !!}
                        {!! Form::close() !!}
                    @endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
