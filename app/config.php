<?php
/**
 * Created by: gellu
 * Date: 12.09.2013 16:02
 */

$configGlobal = [
	'slim' => array(

	),
];


require_once 'config-env.php';

return array_merge($configEnv, $configGlobal);