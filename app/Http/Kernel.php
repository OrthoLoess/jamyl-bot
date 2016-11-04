<?php namespace JamylBot\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
	];

	/**
	 * The application's route middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [
		'web' => [
			\JamylBot\Http\Middleware\EncryptCookies::class,
			\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			\Illuminate\Session\Middleware\StartSession::class,
			\Illuminate\View\Middleware\ShareErrorsFromSession::class,
			\JamylBot\Http\Middleware\VerifyCsrfToken::class,
			\Illuminate\Routing\Middleware\SubstituteBindings::class,
		],
		'api' => [
			'throttle:60,1',
			'bindings',
		],
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'JamylBot\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
		'can' => \Illuminate\Auth\Middleware\Authorize::class,
		'guest' => 'JamylBot\Http\Middleware\RedirectIfAuthenticated',
		'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'admin' => 'JamylBot\Http\Middleware\MustBeAdmin',
	];

}
