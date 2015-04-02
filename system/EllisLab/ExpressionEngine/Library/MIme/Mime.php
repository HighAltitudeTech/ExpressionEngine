<?php

namespace EllisLab\ExpressionEngine\Library\Mime;

use Exception;

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2015, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 2.9.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Mime Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Mime {

	protected $whitelist = array();
	protected $images    = array();

	public function __construct($mimes = '')
	{
		if (is_array($mimes))
		{
			$this->whitelist = $mimes;
		}
		else
		{
			if (empty($mime_file))
			{
				$mime_file = APPPATH.'config/mimes.php';
			}

			if (file_exists($mime_file) && is_readable($mime_file))
			{
				include($mime_file);
				$this->whitelist = $whitelist;
			}
			else
			{
				throw new Exception("Cannot import your mime-type whitelist: the file " . $mime_file . " does not exist or cannot be read.");
			}
		}
	}

	public function ofFile($path)
	{
		if ( ! file_exists($path))
		{
			throw new Exception("File " . $path . " does not exist.");
		}

		// Set a default
		$mime = 'application/octet-stream';

		$finfo = @finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo !== FALSE)
		{
			$fres = @finfo_file($finfo, $path);
			if ( ($fres !== FALSE)
				&& is_string($fres)
				&& (strlen($fres)>0))
			{
				$mime = $fres;
			}

			@finfo_close($finfo);
		}

		return $mime;
	}

	protected function divineImages()
	{
		if (empty($images))
		{
			foreach ($this->whitelist as $mime)
			{
				if (strpos($mime, 'image/') === 0)
				{
					$this->images[] = $mime;
				}
			}
		}

		return $this->images;
	}

	public function fileIsImage($path)
	{
		return in_array($this->ofFile($path), $this->divineImages());
	}

	public function isImage($mime)
	{
		return in_array($mime, $this->divineImages());
	}


	public function fileIsSafeForUpload($path)
	{
		return in_array($this->ofFile($path), $this->whitelist);

	}

	public function isSafeForUpload($mime)
	{
		return in_array($mime, $this->whitelist);
	}

}

