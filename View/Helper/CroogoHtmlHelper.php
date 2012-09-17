<?php

App::uses('HtmlHelper', 'View/Helper');

class CroogoHtmlHelper extends HtmlHelper {

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		$this->_tags['beginbox'] =
			'<div class="row-fluid">
				<div class="span12">
					<div class="box">
						<div class="box-title">
							<i class="icon-list"></i>
							%s
						</div>
						<div class="box-content %s">';
		$this->_tags['endbox'] =
						'</div>
					</div>
				</div>
			</div>';
		$this->_tags['icon'] = '<i class="%s"%s></i> ';
	}

	public function beginBox($title, $isHidden = false, $isLabelHidden = false) {
		$isHidden = $isHidden ? 'hidden' : '';
		$isLabelHidden = $isLabelHidden ? 'label-hidden' : '';
		$class = $isHidden . ' ' . $isLabelHidden;
		return $this->useTag('beginbox', $title, $class);
	}

	public function endBox() {
		return $this->useTag('endbox');
	}

	public function icon($name, $options = array()) {
		$defaults = array('class' => '');
		$options = array_merge($defaults, $options);
		$class = '';
		foreach ((array)$name as $iconName) {
			$class .= ' icon-' . $iconName;
		}
		$class .= ' ' . $options['class'];
		$class = trim($class);
		unset($options['class']);
		$attributes = '';
		foreach ($options as $attr => $value) {
			$attributes .= $attr . '="' . $value . '" ';
		}
		if ($attributes) {
			$attributes = ' ' . $attributes;
		}
		return sprintf($this->_tags['icon'], $class, $attributes);
	}

	public function status($value) {
		$icon = $value == 1 ? 'ok' : 'remove';
		$class = $value == 1 ? 'green' : 'red';

		return $this->icon($icon, array('class' => $class));
	}

/**
 * Add possibilities to parent::link() method
 *
 * ### Options
 *
 * - `escape` Set to true to enable escaping of title and attributes.
 * - `button` 'primary', 'info', 'success', 'warning', 'danger', 'inverse', 'link'. http://twitter.github.com/bootstrap/base-css.html#buttons
 * - `icon` 'ok', 'remove' ... http://fortawesome.github.com/Font-Awesome/
 *
 * @param string $title The content to be wrapped by <a> tags.
 * @param string|array $url Cake-relative URL or array of URL parameters, or external URL (starts with http://)
 * @param array $options Array of HTML attributes.
 * @param string $confirmMessage JavaScript confirmation message.
 * @return string An `<a />` element.
 */
	public function link($title, $url = null, $options = array(), $confirmMessage = false) {
		$defaults = array('escape' => false);
		$options = is_null($options) ? array() : $options;
		$options = array_merge($defaults, $options);

		if (isset($options['button'])) {
			$buttons = array('btn');
			foreach ((array)$options['button'] as $button) {
				if ($button == 'default') {
					continue;
				}
				$buttons[] = 'btn-' . $button;
			}
			$options['class'] = trim(join(' ', $buttons));
			unset($options['button']);
		}

		if (isset($options['icon'])) {
			if (empty($options['iconInline'])) {
				$title = $this->icon($options['icon']) . $title;
			} else {
				$icon = 'icon-large icon-' . $options['icon'];
				if (isset($options['class'])) {
					$options['class'] .= ' ' . $icon;
				} else {
					$options['class'] = ' ' . $icon;
				}
			}
			unset($options['icon']);
		}

		if (isset($options['tooltip'])) {
			$options['rel'] = 'tooltip';
			$options['data-placement'] = 'top';
			$options['data-original-title'] = $options['tooltip'];
			unset($options['tooltip']);
		}

		return parent::link($title, $url, $options, $confirmMessage);
	}

	public function addPath($path, $separator) {
		$path = explode($separator, $path);
		$currentPath = '';
		foreach ($path as $p) {
			if (!is_null($p)) {
				$currentPath .= $p . $separator;
				$this->addCrumb($p, $currentPath);
			}
		}
		return $this;
	}

	public function addCrumb($name, $link = null, $options = null) {
		parent::addCrumb($name, $link, $options);
		return $this;
	}

	public function hasCrumbs() {
		return !empty($this->_crumbs);
	}

}
