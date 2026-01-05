<?php namespace ProcessWire;

class FieldtypeIconifyIcon extends Fieldtype {

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
	 * Sanitize value for storage
	 *
	 * @param Page $page
	 * @param Field $field
	 * @param string $value
	 * @return string
	 */
	public function sanitizeValue(Page $page, Field $field, $value) {
		return $value;
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
		if(!$value) return WireData([]);

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
		// Remove XML declaration for inline SVG
		$svg = $this->wire()->files->fileGetContents($path);
		$svg = preg_replace('/<\?xml.*?\?>\s*/i', '', $svg);

		// Return the icon data
		$data = [
			'raw' => $value,
			'set' => $setAndName['set'],
			'name' => $setAndName['name'],
			'path' => $path,
			'url' => $localBaseUrl . $setAndName['set'] . '/' . $setAndName['name'] . '.svg',
			'svg' => $svg,
		];
		return WireData($data);
	}

	/**
	 * Database schema
	 *
	 * @param Field $field
	 * @return array
	 */
	public function getDatabaseSchema(Field $field) {
		$schema = parent::getDatabaseSchema($field);
		$len = $this->wire()->database->getMaxIndexLength();
		$schema['data'] = 'text NOT NULL';
		$schema['keys']['data_exact'] = "KEY `data_exact` (`data`($len))";
		$schema['keys']['data'] = 'FULLTEXT KEY `data` (`data`)';
		return $schema;
	}

}
