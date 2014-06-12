<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   \
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id$
 */


/* cubi rest web service entry point
  request example:
  url
    http://host/cubi/rest.php/system/users?start=10&limit=10
*/

include_once 'bin/app_init.php';
include_once OPENBIZ_HOME."/bin/ErrorHandler.php";

require 'bin/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// GET with query
$app->get('/:module/:resource/q', function ($module,$resource) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->query($resource, $app->request(), $app->response());
});
$app->get('/:module/:resource/:id/:childresource/q', function ($module,$resource,$id,$childresource) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->queryChildren($resource, $id, $childresource, $app->request(), $app->response());
});

// GET request
$app->get('/:module/:resource/:id', function ($module,$resource,$id) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->get($resource, $id, $app->request(), $app->response());
});

// POST request
$app->post('/:module/:resource/',  function ($module,$resource) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->post($resource, $app->request(), $app->response());
});

// by default angular use POST instead of PUT to update data
$app->post('/:module/:resource/:id/:childresource',  function ($module,$resource,$id,$childresource) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->putChildren($resource, $id, $childresource, $app->request(), $app->response());
});
$app->post('/:module/:resource/:id',  function ($module,$resource,$id) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->put($resource, $id, $app->request(), $app->response());
});

/*// PUT request
$app->put('/:module/:resource/:id',  function ($module,$resource,$id) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->put($resource, $id, $app->request(), $app->response());
});*/

// DELETE request
$app->delete('/:module/:resource/:id/:childresource/:childid',  function ($module,$resource,$id,$childresource,$childid) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->deleteChild($resource, $id, $childresource, $childid, $app->request(), $app->response());
});
$app->delete('/:module/:resource/:id',  function ($module,$resource,$id) {
	$app = \Slim\Slim::getInstance();
	// forward to module rest service implementation
	$restServiceName = $module.".websvc."."RestService";
	$restSvc = BizSystem::getObject($restServiceName);
	$restSvc->delete($resource, $id, $app->request(), $app->response());
});

$app->run();
?>