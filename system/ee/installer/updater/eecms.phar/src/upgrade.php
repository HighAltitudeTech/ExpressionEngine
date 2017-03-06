<?php

$updater_boot = SYSPATH.'/ee/updater/boot.php';
if (file_exists($updater_boot))
{
	require_once $updater_boot;
}

class Command {

	public function __construct($params = [])
	{
		if (isset($params['microapp']))
		{
			$step = (isset($params['step'])) ? $params['step'] : NULL;
			return $this->updaterMicroapp($step);
		}

		if (isset($params['rollback']))
		{
			return $this->updaterMicroapp('rollback');
		}

		$this->start();
	}

	public function start()
	{
		ee()->load->library('el_pings');
		$version_file = ee()->el_pings->get_version_info();
		$to_version = $version_file[0][0];

		if (version_compare(APP_VER, $to_version, '>='))
		{
			exit('ExpressionEngine '.APP_VER.' is already up-to-date!');
		}

		// Preflight checks, download and unpack update
		ee('Updater/Runner')->run();

		// Launch into microapp
		runCommandExternally('upgrade --microapp --no-bootstrap');
	}

	public function updaterMicroapp($step = NULL)
	{
		$runner = new EllisLab\ExpressionEngine\Updater\Service\Updater\Runner();

		if ( ! $step)
		{
			$step = $runner->getFirstStep();
		}

		$runner->runStep($step);

		if (($next_step = $runner->getNextStep()) !== FALSE)
		{
			$cmd = 'upgrade --microapp --step="'.$next_step.'"';

			if (strpos($next_step, 'updateDatabase') === FALSE)
			{
				$cmd .= ' --no-bootstrap';
			}

			runCommandExternally($cmd);
		}
	}
}

// EOF
