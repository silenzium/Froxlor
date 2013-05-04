<?php

/**
 * ApiException class
 *
 * This class extends the default Exception class
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2013- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */

/**
 * Class ApiException
 *
 * This class extends the default Exception class
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class ApiException extends Exception implements iException {

	/**
	 * Exception message
	 * @var string
	 */
	protected $_message = 'No detail information given';

	/**
	 * User-defined exception code
	 * @var int
	 */
	protected $_code = 0;

	/**
	 * Source filename of exception
	 * @var string
	 */
	protected $_file;

	/**
	 * Source line of exception
	 * @var string
	 */
	protected $_line;

	/**
	 * (non-PHPdoc)
	 * @see iException::__construct()
	 *
	 * @param int    $code    custom error code (default = -1, unkown exception)
	 * @param string $message error-message (optional)
	 *
	 * @throws ApiException
	 */
	public function __construct($code = -1, $message = null) {
		if (!$message) {
			$message = 'Unknown exception';
		}
		parent::__construct($message, $code);
	}

	/**
	 * (non-PHPdoc)
	 * @see iException::__toString()
	 *
	 * @return array in api php-array structure
	 */
	public function __toString() {
		try {
			$code = 500;
			if ($this->getCode() > 0) {
				$code = $this->getCode();
			}
			$result = ApiResponse::createResponse(
					$code,
					$this->getMessage(),
					array(
							'class' => get_class($this),
							'file' => $this->getFile(),
							'line' => $this->getLine(),
							'trace' => $this->getTraceAsString()
					)
			);
			return serialize($result);
		} catch (ApiException $e) {
			// this should *never* happen!
			return $e->getMessage();
		}
	}
}
