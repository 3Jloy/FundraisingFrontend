<?php

/**
 * These variables need to be in scope when this file is included:
 *
 * @var \WMDE\Fundraising\Frontend\FunFunFactory $ffFactory
 */

declare(strict_types = 1);

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new \Silex\Application();

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->after( function( Request $request, Response $response ) {
	if( $response instanceof JsonResponse ) {
		$response->setEncodingOptions( JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	return $response;
} );

$app->error( function ( \Exception $e, $code ) use ( $ffFactory ) {
	$ffFactory->getLogger()->error( $e->getMessage(), [
		'code' => $e->getCode(),
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'stack_trace' => $e->getTraceAsString()
	] );
	return new JsonResponse(
		[
			'message' => $e->getMessage(),
			'code' => $code
		],
		$code
	);
} );

$ffFactory->getTwig()->addGlobal( 'app', $app );

return require __DIR__ . '/routes.php';