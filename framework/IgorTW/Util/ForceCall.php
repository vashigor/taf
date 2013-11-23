<?php

/**
 * Force Call - Wrapper for call that allows try to call the method a 
 * couple of times before the exception is realy thrown.
 * 
 * @author Igor Tkachenko
 * @package IgorTW.Util
 */
class IgorTW_Util_ForceCall
{
	
	/**
	 * 
	 * @var integer
	 */
	protected $number;
	
	/**
	 *
	 * @var integer
	 */
	protected $initialNumber;
	
	/**
	 * 
	 * @var string (Class)
	 */
	protected $exceptionType;
	
	/**
	 * 
	 * @var string (regex)
	 */
	protected $messagePattern;
	
	/**
	 * 
	 * @var integer
	 */
	protected $sleep = 3;
	
	/**
	 * Contructor of force call
	 * 
	 * @param string $exceptionType Class name of allowed excpetion (by default = Excpetion)
	 * @param string $messagePatter Pattern for check message of allowed exceptions (by default = null)
	 * @param number $number Number of tries (by default = 3)
	 */
	public function __construct( $exceptionType = 'Exception' , $messagePatter = null , $number = 3 )
	{
		$this->exceptionType = $exceptionType;
		$this->messagePattern = $messagePatter;
		$this->initialNumber = $number;
	}
	
	/**
	 * 
	 * @param integer $sleep
	 */
	public function setSleep( $sleep = 3 )
	{
		$this->sleep = $sleep;
	}
	
	/**
	 * Try call method a couple of times until throw real exception.
	 * 
	 * @param Object $object
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function tryCall( $object , $method , array $arguments )
	{
		$this->number = $this->initialNumber;
		while (true)
		{
			try
			{
				return call_user_func_array(array($object,$method),$arguments);
			}
			catch( Exception $exc )
			{
				$this->validateException($exc);
			}
			if (!is_null($this->sleep))
			{
				sleep($this->sleep);
			}
		}
	}
	
	/**
	 * Validate excepation to decide wheter throw it further or not.
	 * 
	 * @param Exception $exc
	 * @throws string
	 * @throws Ambigous <Exception, string>
	 */
	protected function validateException( Exception $exc )
	{
		if (!$exc instanceof $this->exceptionType)
		{
			throw $exc;
		}
		if (!is_null($this->messagePattern))
		{
			if (!preg_match($this->messagePattern, $exc->getMessage()))
			{
				throw $exc;
			}
		}
		if (--$this->number <= 0)
		{
			throw $exc;
		}
	}
	
}