<?php namespace ProcessWire;

$info = array(
	'title' => 'Iconify Icon: Admin',
	'summary' => 'Allows Iconify icons to be used in the ProcessWire admin.',
	'version' => '0.1.3',
	'author' => 'Robin Sallis',
	'href' => 'https://github.com/Toutouwai/IconifyIcon',
	'icon' => 'puzzle-piece',
	'autoload' => 'template=admin',
	'requires' => 'ProcessWire>=3.0.227, PHP>=7.0.0, InputfieldIconifyIcon, FileValidatorSvgSanitizer',
);
