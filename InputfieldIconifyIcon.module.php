<?php namespace ProcessWire;

class InputfieldIconifyIcon extends InputfieldText implements InputfieldHasTextValue {

	protected $localBasePath;

	protected $localBaseUrl;

	/**
	 * Init
	 */
	public function init() {
		$config = $this->wire()->config;
		parent::init();
		$this->setAttribute('prefixes', '');
		$this->setAttribute('previewSize', 100);
		$this->localBasePath = $config->paths->assets . 'iconify/';
		$this->localBaseUrl = $config->urls->assets . 'iconify/';
	}

	/**
	 * Process input
	 *
	 * @param WireInputData $input User input where value should be pulled from (typically `$input->post`)
	 * @return $this
	 */
	public function ___processInput(WireInputData $input) {
		parent::___processInput($input);
		if($this->value) {
			$this->downloadIcon($this->value);
		}
		return $this;
	}

	/**
	 * Get the Iconify icon set and icon name from the given string
	 *
	 * @param string $iconifyIcon
	 * @return array
	 */
	public function getSetAndName($iconifyIcon) {
		if(substr($iconifyIcon, 0, 9) === 'iconify--') {
			$iconifyIcon = substr($iconifyIcon, 9);
		}
		$pieces = explode('--', $iconifyIcon);
		if(count($pieces) !== 2) {
			throw new WireException('Invalid icon string.');
		}
		return [
			'set' => $pieces[0],
			'name' => $pieces[1],
		];
	}

	/**
	 * Get the icon URL or path from the given string
	 *
	 * @param string $iconifyIcon
	 * @param bool $getPath
	 * @return string
	 */
	public function getIconUrl($iconifyIcon, $getPath = false) {
		$base = $getPath ? $this->localBasePath : $this->localBaseUrl;
		$setAndName = $this->getSetAndName($iconifyIcon);
		return $base . $setAndName['set'] . '/' . $setAndName['name'] . '.svg';
	}

	/**
	 * Download the icon from Iconify
	 *
	 * @param string $iconifyIcon
	 * @return bool
	 */
	public function downloadIcon($iconifyIcon) {
		$files = $this->wire()->files;
		$destinationFilename = $this->getIconUrl($iconifyIcon, true);
		// Download SVG from Iconify if it doesn't exist
		if(!is_file($destinationFilename)) {
			// Create the local iconify directory if it doesn't exist
			if(!is_dir($this->localBasePath)) {
				$files->mkdir($this->localBasePath);
			}
			$setAndName = $this->getSetAndName($iconifyIcon);
			$tempDir = $files->tempDir();
			$tempDirPath = $tempDir->get();
			$tempFilename = $tempDirPath . $setAndName['name'] . '.svg';
			// Attempt to download the icon
			$http = new WireHttp();
			try {
				// Download to a temp directory
				$http->download("https://api.iconify.design/{$setAndName['set']}/{$setAndName['name']}.svg", $tempFilename);
				// Create the destination directory if it doesn't exist
				if(!is_dir($this->localBasePath . $setAndName['set'])) {
					$files->mkdir($this->localBasePath . $setAndName['set'], true);
				}
				// Copy the file from the temp directory to the destination directory
				return $files->copy($tempFilename, $destinationFilename);
			} catch(\Exception $e) {
				// Log the exception message
				$this->wire()->log->save('iconify-icon', $e->getMessage());
				return false;
			}
		}
		return true;
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	public function ___render() {
		$this->addClass('iii-input');
		$title = '';
		$containerClass = '';
		$previewStyle = "width:{$this->previewSize}px; height:{$this->previewSize}px;";
		if($this->value) {
			$containerClass = ' has-selection';
			$this->downloadIcon($this->value);
			$setAndName = $this->getSetAndName($this->value);
			$title = "{$setAndName['set']}/{$setAndName['name']}";
			$iconUrl = $this->getIconUrl($this->value);
			$previewStyle .= "background-image:url($iconUrl);";
		}
		$labels = [
			'placeholder' => $this->_('Search icons...'),
			'choose' => $this->_('Click to choose an icon from the results below:'),
			'no-results' => $this->_('No matching icons found.'),
			'no-selection' => $this->_('No icon selected.'),
			'clear' => $this->_('Clear'),
		];
		$out = <<<EOT
<div class="iii-container$containerClass">
	<input type="text" name="_search_$this->name" class="uk-input InputfieldIgnoreChanges iii-search" placeholder="{$labels['placeholder']}" data-prefixes="$this->prefixes">
	<div class="iii-results" data-choose="{$labels['choose']}" data-no-results="{$labels['no-results']}"></div>
	<div class="iii-selection">
		<div class="iii-selected" style="$previewStyle" title="$title"></div>
		<button class="iii-clear" type="button">{$labels['clear']}</button>
	</div>
	<div class="iii-no-selection">{$labels['no-selection']}</div>
</div>
EOT;
		return $out . parent::___render();
	}

	public function getCustomConfigInputfields() {
		$modules = $this->wire()->modules;
		$customInputfields = [];

		/** @var InputfieldText $f */
		$f = $modules->get('InputfieldText');
		$name = 'prefixes';
		$f->name = $name;
		$f->label = $this->_('Iconify icon set prefixes');
		$f->description = $this->_('If you want to limit the Iconify search to particular icon sets then enter the prefixes of those sets separated by commas.');
		$f->notes = $this->_('You can find the prefix of an icon set from its URL by browsing at [https://icon-sets.iconify.design/](https://icon-sets.iconify.design/). For example, the prefix of the icon set browsable at https://icon-sets.iconify.design/mdi/ is "mdi".');
		$f->icon = 'puzzle-piece';
		$f->value = $this->$name;
		$f->collapsed = Inputfield::collapsedBlank;
		$customInputfields[$name] = $f;

		/** @var InputfieldInteger $f */
		$f = $modules->get('InputfieldInteger');
		$name = 'previewSize';
		$f->name = $name;
		$f->label = $this->_('Icon preview size');
		$f->description = $this->_('The width and height of the icon preview in pixels.');
		$f->icon = 'arrows-h';
		$f->inputType = 'number';
		$f->value = $this->$name ?: 100;
		$f->collapsed = Inputfield::collapsedYes;
		$customInputfields[$name] = $f;

		return $customInputfields;
	}

	/**
	 * Config inputfields
	 *
	 * @return InputfieldWrapper
	 */
	public function ___getConfigInputfields() {
		// Start with only basic config fields rather than those normally used for InputfieldText
		$inputfields = Inputfield::___getConfigInputfields();
		// Add custom config fields
		foreach($this->getCustomConfigInputfields() as $f) {
			$inputfields->add($f);
		}
		return $inputfields;
	}

}
