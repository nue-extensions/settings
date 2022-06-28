<?php

Route::group([
	'namespace' => 'Nue\Setting\Http\Controllers', 
	'prefix' => 'helpers'
], function() {

	Route::resource('terminal', 'TerminalController')->only(['index', 'store']);
	Route::resource('generate', 'GenerateController')->only(['index', 'store']);
	Route::resource('routes', 'RouteController')->only(['index']);

});