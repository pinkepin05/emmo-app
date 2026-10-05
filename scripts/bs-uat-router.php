<?php
// Local-only router for the isolated database, never register in application routes.
$urlPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$public = realpath(__DIR__.'/../public');
$file = realpath($public.$urlPath);
if ($urlPath !== '/' && $file && str_starts_with($file, $public.DIRECTORY_SEPARATOR) && is_file($file)) return false;
[$app] = require __DIR__.'/bs-verification-bootstrap.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
