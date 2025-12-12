<?php namespace ProcessWire;

class AdminIconifyIcon extends WireData implements Module, ConfigurableModule {

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->set('prefixes', '');
		$this->set('previewSize', 100);
	}

	/**
	 * Ready
	 */
	public function ready() {
		// Add CSS
		$config = $this->wire()->config;
		$cssPath = $config->paths->assets . 'iconify/IconifyIcons.css';
		if(!is_file($cssPath)) $this->generateCSS();
		$config->styles->add($config->versionUrl($config->urls->assets . 'iconify/IconifyIcons.css', true));
		$config->styles->add($config->versionUrl($config->urls->$this . "$this.css", true));

		// Hooks
		$this->addHookAfter('ProcessField::buildEditForm, ProcessTemplate::buildEditForm', $this, 'afterBuildEditForm');
		$this->addHookBefore('Fields::save', $this, 'fieldSaved');
		$this->addHookBefore('Templates::save', $this, 'templateSaved');
		$this->addHookAfter('Pages::saved', $this, 'afterPagesSaved');
	}

	/**
	 * After ProcessField::buildEditForm and ProcessTemplate::buildEditForm
	 *
	 * @param HookEvent $event
	 */
	protected function afterBuildEditForm(HookEvent $event) {
		/* @var InputfieldWrapper $wrapper */
		$wrapper = $event->return;
		$input = $this->wire()->input;
		$modules = $this->wire()->modules;
		$name = 'iconifyIcon';

		// Get Field or Template item depending on the event object
		$itemId = (int) $input->post->id ?: $input->get->id;
		if(!$itemId) return;
		if($event->object == 'ProcessField') {
			$iconFieldName = 'icon';
			$item = $this->wire()->fields->get($itemId);
		} else {
			$iconFieldName = 'pageLabelIcon';
			$item = $this->wire()->templates->get($itemId);
		}

		// Get core "Icon" field
		$iconField = $wrapper->getChildByName($iconFieldName);
		if(!$iconField) return;
		$iconField->showIf = "$name=''";

		// Add "Iconify icon" field
		/** @var InputfieldIconifyIcon $f */
		$f = $modules->get('InputfieldIconifyIcon');
		$f->name = $name;
		$f->label = $this->_('Iconify icon');
		$f->icon = $this->sanitizeFieldIconName($item->$name) ?: 'puzzle-piece';
		$f->value = $item->$name;
		$f->prefixes = $this->prefixes;
		$f->previewSize = $this->previewSize;
		$f->collapsed = Inputfield::collapsedBlank;
		$wrapper->insertAfter($f, $iconField);
	}

	/**
	 * Before Fields::save
	 *
	 * @param HookEvent $event
	 */
	protected function fieldSaved(HookEvent $event) {
		/** @var Field $field */
		$field = $event->arguments(0);
		// If iconifyIcon has changed...
		if($field->isChanged('iconifyIcon')) {
			// Set iconifyIcon property now so that it's available to generateCSS()
			$field->set('iconifyIcon', $field->iconifyIcon);
			// Generate CSS
			$this->generateCSS();
		}
		// Set field icon to sanitized iconifyIcon name
		if($field->iconifyIcon) $field->set('icon', $this->sanitizeFieldIconName($field->iconifyIcon));
	}

	/**
	 * Before Templates::save
	 *
	 * @param HookEvent $event
	 */
	protected function templateSaved(HookEvent $event) {
		/** @var Template $template */
		$template = $event->arguments(0);
		// Get iconifyIcon from POST because it doesn't get automatically saved to $template
		// https://github.com/processwire/processwire-requests/issues/569
		$iconifyIcon = $this->wire()->input->post->text('iconifyIcon');
		// If iconifyIcon has changed...
		if((string) $template->iconifyIcon !== $iconifyIcon) {
			// Set iconifyIcon property now so that it's available to generateCSS()
			$template->set('iconifyIcon', $iconifyIcon);
			// Generate CSS
			$this->generateCSS();
			// Set template icon via dedicated method
			if($iconifyIcon) $template->setIcon($iconifyIcon);
		}
	}

	/**
	 * After Pages::saved
	 *
	 * @param HookEvent $event
	 */
	protected function afterPagesSaved(HookEvent $event) {
		/** @var Page $page */
		$page = $event->arguments(0);
		$changes = $event->arguments(1);
		if($page->template != 'admin') return;
		if(empty($changes['page_icon'])) return;
		if(substr($page->page_icon, 0, 9) !== 'iconify--') return;
		$this->generateCSS();
	}

	/**
	 * InputfieldsWrapper strips out all occurrences "icon-" and "fa-" so modify icon name to avoid this
	 * https://github.com/processwire/processwire/blob/2d6254d7da5ddf9947ad435aacf428369825479b/wire/core/InputfieldWrapper.php#L909
	 *
	 * @param string
	 * @return string
	 */
	public function sanitizeFieldIconName($name) {
		return str_replace(['icon-', 'fa-'], 'iireplaced-', (string) $name);
	}

	/**
	 * Generate CSS file for ProcessWire admin icons
	 */
	protected function generateCSS() {
		$config = $this->wire()->config;
		$files = $this->wire()->files;
		$fields = $this->wire()->fields;
		$templates = $this->wire()->templates;
		/** @var InputfieldIconifyIcon $iii */
		$iii = $this->wire()->modules->get('InputfieldIconifyIcon');
		$icons = [];
		foreach($templates->find("iconifyIcon!=''") as $template) {
			$icons[$template->iconifyIcon] = $template->iconifyIcon;
		}
		foreach($fields->find("iconifyIcon!=''") as $field) {
			$icons[$field->iconifyIcon] = $field->iconifyIcon;
		}
		if($fields->get('page_icon')) {
			$iconedPages = $this->wire()->pages->find("template=admin, page_icon!='', include=unpublished, check_access=0");
			foreach($iconedPages as $p) {
				// Skip any icons that are not Iconify icons
				if(substr($p->page_icon, 0, 9) !== 'iconify--') continue;
				$icons[$p->page_icon] = $p->page_icon;
			}
		}
		$css = '';
		foreach($icons as $iconifyIcon) {
			$iii->downloadIcon($iconifyIcon);
			$url = $iii->getIconUrl($iconifyIcon);
			$selector = ".fa-$iconifyIcon";
			$sanitizedName = $this->sanitizeFieldIconName($iconifyIcon);
			if($iconifyIcon !== $sanitizedName) $selector .= ", .fa-$sanitizedName";
			$css .= "$selector { mask-image:url($url); -webkit-mask-image:url($url); }\n";
		}
		$iconifyPath = $config->paths->assets . 'iconify/';
		if(!is_dir($iconifyPath)) $files->mkdir($iconifyPath);
		$files->filePutContents($iconifyPath . 'IconifyIcons.css', $css);
	}

	/**
	 * Config inputfields
	 *
	 * @param InputfieldWrapper $inputfields
	 */
	public function getModuleConfigInputfields($inputfields) {
		/** @var InputfieldIconifyIcon $iii */
		$iii = $this->wire()->modules->get('InputfieldIconifyIcon');
		$customInputfields = $iii->getCustomConfigInputfields();
		foreach($customInputfields as $name => $f) {
			$inputfields->add($f);
			if($name === 'previewSize') {
				$f->value = $this->$name ?: 100;
			} else {
				$f->value = $this->$name;
			}
		}
	}

}
