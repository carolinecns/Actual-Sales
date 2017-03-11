<?php
class BFormHelper extends FormHelper {
	function input($fieldName, $options = array()) {
		$this->setEntity($fieldName);

		$options = array_merge(
			array('before' => null, 'between' => null, 'after' => null, 'format' => null),
			$this->_inputDefaults,
			$options
		);

		$modelKey = $this->model();
		$fieldKey = $this->field();
		if (!isset($this->fieldset[$modelKey])) {
			$this->_introspectModel($modelKey);
		}

		if (!isset($options['type'])) {
			$magicType = true;
			$options['type'] = 'text';
			if (isset($options['options'])) {
				$options['type'] = 'select';
			} elseif (in_array($fieldKey, array('psword', 'passwd', 'password'))) {
				$options['type'] = 'password';
			} elseif (isset($this->fieldset[$modelKey]['fields'][$fieldKey])) {
				$fieldDef = $this->fieldset[$modelKey]['fields'][$fieldKey];
				$type = $fieldDef['type'];
				$primaryKey = $this->fieldset[$modelKey]['key'];
			}

			if (isset($type)) {
				$map = array(
					'string'  => 'text',     'datetime'  => 'datetime',
					'boolean' => 'checkbox', 'timestamp' => 'datetime',
					'text'    => 'textarea', 'time'      => 'time',
					'date'    => 'date',     'float'     => 'text'
				);

				if (isset($this->map[$type])) {
					$options['type'] = $this->map[$type];
				} elseif (isset($map[$type])) {
					$options['type'] = $map[$type];
				}
				if ($fieldKey == $primaryKey) {
					$options['type'] = 'hidden';
				}
			}
			if (preg_match('/_id$/', $fieldKey) && $options['type'] !== 'hidden') {
				$options['type'] = 'select';
			}

			if ($modelKey === $fieldKey) {
				$options['type'] = 'select';
				if (!isset($options['multiple'])) {
					$options['multiple'] = 'multiple';
				}
			}
		}
		$types = array('checkbox', 'radio', 'select');

		if (
			(!isset($options['options']) && in_array($options['type'], $types)) ||
			(isset($magicType) && $options['type'] == 'text')
		) {
			$view =& ClassRegistry::getObject('view');
			$varName = Inflector::variable(
				Inflector::pluralize(preg_replace('/_id$/', '', $fieldKey))
			);
			$varOptions = $view->getVar($varName);
			if (is_array($varOptions)) {
				if ($options['type'] !== 'radio') {
					$options['type'] = 'select';
				}
				$options['options'] = $varOptions;
			}
		}

		$autoLength = (!array_key_exists('maxlength', $options) && isset($fieldDef['length']));
		if ($autoLength && $options['type'] == 'text') {
			$options['maxlength'] = $fieldDef['length'];
		}
		if ($autoLength && $fieldDef['type'] == 'float') {
			$options['maxlength'] = array_sum(explode(',', $fieldDef['length']))+1;
		}

		$divOptions = array();
		$div = $this->_extractOption('div', $options, true);
		unset($options['div']);

		if (!empty($div)) {
			
			if($options['type'] == 'checkbox')
				$divOptions['class'] = 'control-group input clear';	
			else
				$divOptions['class'] = 'control-group input';
			
			$divOptions = $this->addClass($divOptions, $options['type']);
			if (is_string($div)) {
				$divOptions['class'] = $div;
			} elseif (is_array($div)) {
				$divOptions = array_merge($divOptions, $div);
			}
			if (
				isset($this->fieldset[$modelKey]) &&
				in_array($fieldKey, $this->fieldset[$modelKey]['validates'])
			) {
				$divOptions = $this->addClass($divOptions, 'required');
			}
			if (!isset($divOptions['tag'])) {
				$divOptions['tag'] = 'div';
			}
		}

		$label = null;
		if (isset($options['label']) && $options['type'] !== 'radio') {
			$label = $options['label'];
			unset($options['label']);
		}

		if ($options['type'] === 'radio') {
			$label = false;
			if (isset($options['options'])) {
				$radioOptions = (array)$options['options'];
				unset($options['options']);
			}
		}

		if (!is_array($label) && $label !== false) {
			$label = $this->_inputLabel($fieldName, $label, $options);
		}

		$error = $this->_extractOption('error', $options, null);
		unset($options['error']);

		$selected = $this->_extractOption('selected', $options, null);
		unset($options['selected']);

		if (isset($options['rows']) || isset($options['cols'])) {
			$options['type'] = 'textarea';
		}

		if ($options['type'] === 'datetime' || $options['type'] === 'date' || $options['type'] === 'time' || $options['type'] === 'select') {
			$options += array('empty' => false);
		}
		if ($options['type'] === 'datetime' || $options['type'] === 'date' || $options['type'] === 'time') {
			$dateFormat = $this->_extractOption('dateFormat', $options, 'MDY');
			$timeFormat = $this->_extractOption('timeFormat', $options, 12);
			unset($options['dateFormat'], $options['timeFormat']);
		}

		$type = $options['type'];
		$out = array_merge(
			array('before' => null, 'label' => null, 'between' => null, 'input' => null, 'after' => null, 'error' => null),
			array('before' => $options['before'], 'label' => $label, 'between' => $options['between'], 'after' => $options['after'])
		);
		$format = null;
		if (is_array($options['format']) && in_array('input', $options['format'])) {
			$format = $options['format'];
		}
		unset($options['type'], $options['before'], $options['between'], $options['after'], $options['format']);

		switch ($type) {
			case 'hidden':
				$input = $this->hidden($fieldName, $options);
				$format = array('input');
				unset($divOptions);
			break;
			case 'checkbox':
				$input = $this->checkbox($fieldName, $options);
				$format = $format ? $format : array('before', 'input', 'between', 'label', 'after', 'error');
			break;
			case 'radio':
				$input = $this->radio($fieldName, $radioOptions, $options);
			break;
			case 'text':
			case 'password':
			case 'file':
				$input = $this->{$type}($fieldName, $options);
			break;
			case 'select':
				$options += array('options' => array());
				$list = $options['options'];
				unset($options['options']);
				$input = $this->select($fieldName, $list, $selected, $options);
			break;
			case 'time':
				$input = $this->dateTime($fieldName, null, $timeFormat, $selected, $options);
			break;
			case 'date':
				$input = $this->dateTime($fieldName, $dateFormat, null, $selected, $options);
			break;
			case 'datetime':
				$input = $this->dateTime($fieldName, $dateFormat, $timeFormat, $selected, $options);
			break;
			case 'textarea':
			default:
				$input = $this->textarea($fieldName, $options + array('cols' => '30', 'rows' => '6'));
			break;
		}

		if ($type != 'hidden' && $error !== false) {
			$errMsg = $this->error($fieldName, $error);
			if ($errMsg) {
				$divOptions = $this->addClass($divOptions, 'error');
				$out['error'] = $errMsg;
			}
		}

		$out['input'] = $input;
		$format = $format ? $format : array('before', 'label', 'between', 'input', 'after', 'error');
		$output = '';
		foreach ($format as $element) {
			$output .= $out[$element];
			unset($out[$element]);
		}

		if (!empty($divOptions['tag'])) {
			$tag = $divOptions['tag'];
			unset($divOptions['tag']);
			$output = $this->Html->tag($tag, $output, $divOptions);
		}
		return $output;
	}

	function error($field, $text = null, $options = array()) {
		$defaults = array('wrap' => true, 'class' => 'help-block error-message', 'escape' => true);
		$options = array_merge($defaults, $options);
		$this->setEntity($field);

		$error = $this->tagIsInvalid();
		if ($error !== null) {
			if (is_array($error)) {
				if(isset($fields)){
				  list(,,$field) = explode('.', $field);
				}
				if (isset($error[$field])) {
					$error = $error[$field];
				} else {
					return null;
				}
			}

			if (is_array($text) && is_numeric($error) && $error > 0) {
				$error--;
			}
			if (is_array($text)) {
				$options = array_merge($options, array_intersect_key($text, $defaults));
				if (isset($text['attributes']) && is_array($text['attributes'])) {
					$options = array_merge($options, $text['attributes']);
				}
				$text = isset($text[$error]) ? $text[$error] : null;
				unset($options[$error]);
			}

			if ($text !== null) {
				$error = $text;
			} elseif (is_numeric($error)) {
				$error = sprintf(__('Error in field %s', true), Inflector::humanize($this->field()));
			}
			if ($options['escape']) {
				$error = h($error);
				unset($options['escape']);
			}
			if ($options['wrap']) {
				$tag = is_string($options['wrap']) ? $options['wrap'] : 'div';
				unset($options['wrap']);
				return $this->Html->tag($tag, $error, $options);
			} else {
				return $error;
			}
		} else {
			return null;
		}
	}

	function error_menssage($text = null, $options = array()) {
		$defaults = array('wrap' => true, 'class' => 'help-block alert-error form-actions well', 'escape' => true);
		$options = array_merge($defaults, $options);
		
		if ($text) {
			$error = $text;
			
			if ($options['wrap']) {
				$tag = is_string($options['wrap']) ? $options['wrap'] : 'div';
				unset($options['wrap']);
				return $this->Html->tag($tag, $error, $options);
			} else {
				return $error;
			}
		} else {
			return null;
		}
	}

	function success_menssage($text = null, $options = array()) {
		$defaults = array('wrap' => true, 'class' => 'help-block alert-success form-actions well', 'escape' => false);
		$options = array_merge($defaults, $options);
		
		if ($text) {
			$error = $text;
			
			if ($options['wrap']) {
				$tag = is_string($options['wrap']) ? $options['wrap'] : 'div';
				unset($options['wrap']);
				return $this->Html->tag($tag, $error, $options);
			} else {
				return $error;
			}
		} else {
			return null;
		}
	}
	
	function select($fieldName, $options = array(), $selected = null, $attributes = array()) {
		$select = array();
		$style = null;
		$tag = null;
		$attributes += array(
			'class' => null,
			'escape' => true,
			'secure' => null,
			'empty' => '',
			'showParents' => false,
			'hiddenField' => true
		);

		$escapeOptions = $this->_extractOption('escape', $attributes);
		$secure = $this->_extractOption('secure', $attributes);
		$showEmpty = $this->_extractOption('empty', $attributes);
		$showParents = $this->_extractOption('showParents', $attributes);
		$hiddenField = $this->_extractOption('hiddenField', $attributes);
		unset($attributes['escape'], $attributes['secure'], $attributes['empty'], $attributes['showParents'], $attributes['hiddenField']);

		$attributes = $this->_initInputField($fieldName, array_merge(
			(array)$attributes, array('secure' => false)
		));

		if (is_string($options) && isset($this->__options[$options])) {
			$options = $this->__generateOptions($options);
		} elseif (!is_array($options)) {
			$options = array();
		}
		if (isset($attributes['type'])) {
			unset($attributes['type']);
		}

		if (!isset($selected)) {
			$selected = $attributes['value'];
		}

		if (!empty($attributes['multiple'])) {
			$style = ($attributes['multiple'] === 'checkbox') ? 'checkbox' : null;
			$template = ($style) ? 'checkboxmultiplestart' : 'selectmultiplestart';
			$tag = $this->Html->tags[$template];
			if ($hiddenField) {
				$hiddenAttributes = array(
					'value' => '',
					'id' => $attributes['id'] . ($style ? '' : '_'),
					'secure' => false,
					'name' => $attributes['name']
				);
				$select[] = $this->hidden(null, $hiddenAttributes);
			}
		} else {
			$tag = $this->Html->tags['selectstart'];
		}

		if (!empty($tag) || isset($template)) {
			if (!isset($secure) || $secure == true) {
				$this->__secure();
			}
			$select[] = sprintf($tag, $attributes['name'], $this->_parseAttributes(
				$attributes, array('name', 'value'))
			);
		}
		$emptyMulti = (
			$showEmpty !== null && $showEmpty !== false && !(
				empty($showEmpty) && (isset($attributes) &&
				array_key_exists('multiple', $attributes))
			)
		);

		if ($emptyMulti) {
			$showEmpty = ($showEmpty === true) ? '' : $showEmpty;
			$options = array_reverse($options, true);
			$options[''] = $showEmpty;
			$options = array_reverse($options, true);
		}

		if (!isset($attributes['disabled'])) $attributes['disabled'] = null;
		$select = array_merge($select, $this->__selectOptions(
			array_reverse($options, true),
			$selected,
			array(),
			$showParents,
			array('escape' => $escapeOptions, 'style' => $style, 'name' => $attributes['name'], 'class' => $attributes['class'], 'disabled'=>$attributes['disabled'])
		));

		$template = ($style == 'checkbox') ? 'checkboxmultipleend' : 'selectend';
		$select[] = $this->Html->tags[$template];
		return implode("\n", $select);
	}


	function __selectOptions($elements = array(), $selected = null, $parents = array(), $showParents = null, $attributes = array()) {
		$select = array();
		$attributes = array_merge(array('escape' => true, 'style' => null, 'class' => null), $attributes);
		$selectedIsEmpty = ($selected === '' || $selected === null);
		$selectedIsArray = is_array($selected);

		foreach ($elements as $name => $title) {
			$htmlOptions = array();
			if (is_array($title) && (!isset($title['name']) || !isset($title['value']))) {
				if (!empty($name)) {
					if ($attributes['style'] === 'checkbox') {
						$select[] = $this->Html->tags['fieldsetend'];
					} else {
						$select[] = $this->Html->tags['optiongroupend'];
					}
					$parents[] = $name;
				}
				$select = array_merge($select, $this->__selectOptions(
					$title, $selected, $parents, $showParents, $attributes
				));
				if (!empty($name)) {
					$name = $attributes['escape'] ? h($name) : $name;
					if ($attributes['style'] === 'checkbox') {
						$select[] = sprintf($this->Html->tags['fieldsetstart'], $name);
					} else {
						$select[] = sprintf($this->Html->tags['optiongroup'], $name, '');
					}
				}
				$name = null;
			} elseif (is_array($title)) {
				$htmlOptions = $title;
				$name = $title['value'];
				$title = $title['name'];
				unset($htmlOptions['name'], $htmlOptions['value']);
			}

			if ($name !== null) {
				if (
					(!$selectedIsArray && !$selectedIsEmpty && (string)$selected == (string)$name) ||
					($selectedIsArray && in_array($name, $selected))
				) {
					if ($attributes['style'] === 'checkbox') {
						$htmlOptions['checked'] = true;
					} else {
						$htmlOptions['selected'] = 'selected';
					}
				}

				if ($showParents || (!in_array($title, $parents))) {
					$title = ($attributes['escape']) ? h($title) : $title;

					if ($attributes['style'] === 'checkbox') {
						$htmlOptions['value'] = $name;

						$tagName = Inflector::camelize(
							$this->model() . '_' . $this->field().'_'.Inflector::slug($name)
						);
						$htmlOptions['id'] = $tagName;
						$label = array('for' => $tagName);

						if (isset($htmlOptions['checked']) && $htmlOptions['checked'] === true) {
							$label['class'] = 'selected';
						}

						$name = $attributes['name'];

						if (empty($attributes['class'])) {
							$attributes['class'] = 'checkbox';
						} elseif ($attributes['class'] === 'form-error') {
							$attributes['class'] = 'checkbox ' . $attributes['class'];
						}
						if (!empty($attributes['disabled'])) $htmlOptions['disabled'] = $attributes['disabled'];

                        $item = sprintf(
							$this->Html->tags['checkboxmultiple'], $name,
							$this->_parseAttributes($htmlOptions)
						);
						$label = $this->label(null, $item.$title, array('class' => $attributes['class']));
						$select[] = $label;//$this->Html->div($attributes['class'], $item . $label);
					} else {
						$select[] = sprintf(
							$this->Html->tags['selectoption'],
							$name, $this->_parseAttributes($htmlOptions), $title
						);
					}

				}
			}
		}
		return array_reverse($select, true);
	}

	
	function radio($fieldName, $options = array(), $attributes = array()) {
		$attributes = $this->_initInputField($fieldName, $attributes);
		$legend = false;

		if (isset($attributes['legend'])) {
			$legend = $attributes['legend'];
			unset($attributes['legend']);
		} elseif (count($options) > 1) {
			$legend = __(Inflector::humanize($this->field()), true);
		}
		$label = true;

		if (isset($attributes['label'])) {
			$label = $attributes['label'];
			unset($attributes['label']);
		}
		$inbetween = null;

		if (isset($attributes['separator'])) {
			$inbetween = $attributes['separator'];
			unset($attributes['separator']);
		}

		if (isset($attributes['value'])) {
			$value = $attributes['value'];
		} else {
			$value =  $this->value($fieldName);
		}
		$out = array();

		$hiddenField = isset($attributes['hiddenField']) ? $attributes['hiddenField'] : true;
		unset($attributes['hiddenField']);
		foreach ($options as $optValue => $optTitle) {
			$optionsHere = array('value' => $optValue);

			if (isset($value) && $value !== '' && $optValue == $value) {
				$optionsHere['checked'] = 'checked';
			}
			$parsedOptions = $this->_parseAttributes(
				array_merge($attributes, $optionsHere),
				array('name', 'type', 'id'), '', ' '
			);
			$tagName = Inflector::camelize(
				$attributes['id'] . '_' . Inflector::slug($optValue)
			);
            if (is_array($label)) {
    			$item = sprintf(
    				$this->Html->tags['radio'], $attributes['name'],
    				$tagName, $parsedOptions, null
    			);
    			$item = $this->label(null, $item.$optTitle, $label);
    			$out[] = $item;
            } else {
    			if ($label) {
    				$optTitle =  sprintf($this->Html->tags['label'], $tagName, null, $optTitle);
    			}
    			$out[] =  sprintf(
    				$this->Html->tags['radio'], $attributes['name'],
    				$tagName, $parsedOptions, $optTitle
    			);    			
    		}
		}
		$hidden = null;

		if ($hiddenField) {
			if (!isset($value) || $value === '') {
				$hidden = $this->hidden($fieldName, array(
					'id' => $attributes['id'] . '_', 'value' => '', 'name' => $attributes['name']
				));
			}
		}
		$out = $hidden . implode($inbetween, $out);

		if ($legend) {
			$out = sprintf(
				$this->Html->tags['fieldset'], '',
				sprintf($this->Html->tags['legend'], $legend) . $out
			);
		}
		return $out;
	}
}
?>