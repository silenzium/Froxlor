<?php

/**
 * Froxlor API Resources-Module
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2010- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * Class Resources
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Resources extends FroxlorModule implements iResources {

	/**
	 * @see iResources::statusResource()
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws ResourcesException
	 * @return array the resource-bean-data
	 */
	public static function statusResource() {

		$ident = self::getParamIdent('ident', 2);

		// set database-parameter
		$dbparam = array(
				':mod' => $ident[0],
				':res' => $ident[1]
		);

		// go find the resource
		$resource = Database::findOne('resources', 'module = :mod AND resource = :res', $dbparam);

		// if null, no setting was found
		if ($resource === null) {
			throw new ResourcesException(404, 'Resource "'.implode('.', $ident).'" not found');
		}

		// return it as array
		return ApiResponse::createResponse(200, null, $resource->export());
	}

	/**
	 * @see iResources::addResource()
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $default a default value for the resources, if empty -1 is used
	 *
	 * @throws ResourcesException if an equal resource exists
	 * @return int id of the new resource-entry
	 */
	public static function addResource() {

		$ident = self::getParamIdent('ident', 2);
		$default = self::getParam('default', true, -1);

		// check if it already exists
		try {
			$res_check = Froxlor::getApi()->apiCall('Resources.statusResource', array('ident' => implode('.', $ident)));
			throw new ResourcesException(406, 'The resource "'.implode('.', $ident).'" does already exist');
		} catch (ResourcesException $e) {
			// all good, the resource does not exist
			// we just go on with our work
		}

		// create new bean
		$res = Database::dispense('resources');
		$res->module = $ident[0];
		$res->resource = $ident[1];
		$res->default = $default;
		$resid = Database::store($res);

		// return success and the id
		return ApiResponse::createResponse(200, null, array('id' => $resid));
	}

	/**
	 * @see iResources::addResourceToUser()
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $limit default is -1
	 *
	 * @throws ResourcesException if the user does not exist
	 * @return bool|mixed success=true if successful otherwise a non-success-apiresponse
	 */
	public static function addResourceToUser() {

		$ident = self::getParamIdent('ident', 2);
		$userid = self::getParam('userid');
		$limit = self::getIntParam('limit', true, -1);

		// get the resource
		$res_resp = Froxlor::getApi()->apiCall(
				'Resources.statusResource',
				array('ident' => implode('.', $ident))
		);

		// did we get the resource?
		if ($res_resp->getResponseCode() == 200) {

			// get response data
			$res_arr = $res_resp->getData();
			// load beans
			$resource = Database::load('resources', $res_arr['id']);
			$user = Database::load('users', $userid);

			// valid user?
			if ($user->id) {
				// check if the user already owns this resource
				foreach ($user->ownUserLimits as $res) {
					if ($res->resourceid == $resource->id) {
						throw new ResourcesException(406, 'User already has resource "'.implode('.', $ident).'" assigned');
					}
				}
				$userlimit = Database::dispense('userlimits');
				$userlimit->resourceid = $resource->id;
				$userlimit->limit = $limit;
				$userlimit->inuse = 0;
				$ulid = Database::store($userlimit);
				$user->ownUserLimits[] = $userlimit;
				return ApiResponse::createResponse(200, null, array('success' => true));
			}

			// user not found
			throw new ResourcesException(404, 'User with the id #'.$userid.' could not be found');
		}

		// return the response which is != 200
		return $res_resp->getResponse();
	}
}
