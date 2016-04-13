<?php 

namespace ApartmentApi\Exceptions;

use Exception;

class InvalidFieldException extends Exception 
{
	private $errorBag;
	
	function __construct($message, $errorBag, $code = 0, $previous = null)
	{
		$this->setErrorBag($errorBag);
		
		parent::__construct($message, $code, $previous);
	}
	
	public function setErrorBag(array $errorBag) 
	{
		$this->errorBag = $errorBag;
	}
	
	public function getErrorBag()
	{
		return $this->errorBag;
	}
}
