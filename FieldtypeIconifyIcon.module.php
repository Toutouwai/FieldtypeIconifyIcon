<?php namespace ProcessWire;

class FieldtypeIconifyIcon extends FieldtypeText {

	/**
	 * Return the associated inputfield
	 *
	 * @param Page $page
	 * @param Field $field
	 * @return Inputfield
	 */
	public function getInputfield(Page $page, Field $field) {
		return $this->wire()->modules->get('InputfieldIconifyIcon');
	}

	/**
	 * Fieldtype does not support textformatters
	 *
	 * @param bool|null
	 * @return bool
	 */
	protected function allowTextFormatters($allow = null) {
		return false;
	}

	/**
	 * Format value for output
	 *
	 * @param Page $page
	 * @param Field $field
	 * @param string $value
	 * @return string
	 */
	public function ___formatValue(Page $page, Field $field, $value) {
		$config = $this->wire()->config;
		$value = (string) $value;

		/** @var InputfieldIconifyIcon $iii */
		$iii = $this->wire()->modules->get('InputfieldIconifyIcon');
		$setAndName = $iii->getSetAndName($value);
		$localBasePath = $config->paths->assets . 'iconify/';
		$localBaseUrl = $config->urls->assets . 'iconify/';
		$path = $localBasePath . $setAndName['set'] . '/' . $setAndName['name'] . '.svg';

		// Download the icon if it doesn't exist
		if(!is_file($path)) {
			$iii->downloadIcon($value);
		}
		// Return the icon data
		$data = [
			'raw' => $value,
			'set' => $setAndName['set'],
			'name' => $setAndName['name'],
			'path' => $path,
			'url' => $localBaseUrl . $setAndName['set'] . '/' . $setAndName['name'] . '.svg',
			'svg' => $this->wire()->files->fileGetContents($path),
		];
		return WireData($data);
	}

}
