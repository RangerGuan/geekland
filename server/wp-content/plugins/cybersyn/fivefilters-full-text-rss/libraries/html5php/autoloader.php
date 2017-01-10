<?php
// autoloader
spl_autoload_register(array(new HTML5PHP_Autoloader(), 'autoload'));

/**
 * Autoloader class
 */
class HTML5PHP_Autoloader
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->path = dirname(__FILE__);
	}

	/**
	 * Autoloader
	 *
	 * @param string $class The name of the class to attempt to load.
	 */
	public function autoload($class)
	{
		// Only load the class if it starts with "HTML5"
		if (strpos($class, 'Masterminds\HTML5') !== 0)
		{
			return;
		}
		$class = substr($class, 12);
		//die($class);

		$filename = $this->path . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
		include $filename;
	}
}