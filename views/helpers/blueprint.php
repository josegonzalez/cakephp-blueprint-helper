<?php
/**
 * Modifies the CakePHP FormHelper to support blueprintCSS
 *
 * @author Jose Diaz-Gonzalez
 * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://josediazgonzalez/code/blueprinthelper/
 * @package app
 * @subpackage app.views.helpers
 * @version .7
 */
class BlueprintHelper extends AppHelper {
	/**
	 * Array of helpers in use by the BlueprintHelper
	 *
	 * @var string
	 **/
	var $helpers = array('Form', 'Html');
	/**
	 * The value of the class attribute assigned to the wrapper div for every label
	 * in the format elementType => class 
	 *
	 * @var array
	 */
	var $_labelClass = array();

	/**
	 * The value of the class attribute assigned to the wrapper div for most elements
	 *
	 * @var string
	 */
	var $_inputClass = array();
	
	/**
	 * The value of the class assigned outside the input element if necessary
	 *
	 * @var string
	 **/
	var $_specialClass = array();

	/**
	 * Sets up Blueprint for inclusion in the header
	 *
	 * @param array $options Additional options to be set if the plugin settings need to be configured
	 * @return string
	 * @access public
	 * @author Jose Diaz-Gonzalez
	 **/
	function setup($options = array()) {
		$options = array_merge(array('screen' => 'blueprint/screen','print' => 'blueprint/print'), $options);
		$head = $this->Html->css($options['screen'], 'stylesheet', array('media' => 'screen, projection'));
		$head .= $this->Html->css($options['print'], 'stylesheet', array('media' => 'print'));
		return $head;
	}

	/**
	 * Sets up the IE stylesheet for the header
	 *
	 * @param array $options Additional options to be set if the plugin settings need to be configured
	 * @return string
	 * @access public
	 * @author Jose Diaz-Gonzalez
	 **/
	function ie($options = array()) {
		$options = array_merge(array('ie' => 'blueprint/ie'), $options);
		$head = "<!--[if IE]>";
		$head .= $this->Html->css($options['ie'], 'stylesheet', array('media' => 'screen'));
		$head .= "<![endif]-->";
		return $head;
	}
	/**
	 * Wrapper for Html Helper that includes a plugin(s) in the header
	 *
	 * @param string $path This is the path within the blueprint plugins folder to the plugin(s)
	 * 						containing the plugin. Do not include "screen.css" or the trailing slash
	 * @param array $options Additional options to be set if the plugin settings need to be configured
	 * @return string
	 * @access public
	 * @author Jose Diaz-Gonzalez
	 **/
	function plugins($path = NULL, $options = array()) {
		$styles = '';
		if (!empty($path)) :
			$options = array_merge(array('blueprint' => 'blueprint/plugins','file' => 'screen', 'media' => 'screen, projection'), $options);
			if (is_array($path)) :
				foreach ($path as $plugin) :
					$styles .= $this->Html->css($options['blueprint'] . "//$plugin//" . $options['file'], 'stylesheet', array('media' => $options['media']));
				endforeach;
			else :
				$styles = $this->Html->css($options['blueprint'] . "//$path//" . $options['file'], 'stylesheet', array('media' => $options['media']));
			endif;
		endif;
		return $styles;
	}

	/**
	 * Caches the blueprintCSS markup needed for labels and form elements
	 *
	 * @param string $label The value assigned to the class attribute of the div wrapper
	 * @param string $input
	 * @access public
	 * @author Dave Mahon
	 */
	function configure($elements, $label = '', $input = '', $special = '') {
		if (is_array($elements)) {
			foreach ($elements as $element){
				$this->_labelClass[$element] = $label;
				$this->_inputClass[$element] = $input;
				if (!empty($special)) $this->_specialClass[$element] = $special;
			}
		} else {
			$this->_labelClass[$elements] = $label;
			$this->_inputClass[$elements] = $input;
			if (!empty($special)) $this->_specialClass[$elements] = $special;
		}
	}

	/**
	 * Wrapper for FormHelper->input that adds blueprintCSS markup
	 *
	 * @param string $fieldName This should be "Modelname.fieldname", "Modelname/fieldname" is deprecated
	 * @param array $options
	 * @param boolean $tagging if true
	 * @return string
	 * @access public
	 * @author Dave Mahon
	 */
	function input($fieldName, $options = array(), $tagging = false) {
		if (!isset($options['type'])) $options['type'] = 'text';
		
		if (!empty($this->_specialClass[$options['type']])){
			$options = array_merge(array(
				'before' => '<div class="' . $this->_labelClass[$options['type']] . '">',
				'between' => '</div><div class="' . $this->_specialClass[$options['type']] . '"><div class="' . $this->_inputClass[$options['type']] . '">',
				'after' => '</div></div>'
				), $options);
		} else {
			$options = array_merge(array(
				'before' => '<div class="' . $this->_labelClass[$options['type']] . '">',
				'between' => '</div><div class="' . $this->_inputClass[$options['type']] . '">',
				'after' => '</div>'
				), $options);
		}
		if ($tagging) :
			App::import('Helper', 'Tagging.Tagging');
			return $this->Tagging->input($fieldName, $options);
		endif;
		return $this->Form->input($fieldName, $options);
	}

	/**
	 * Wrapper for FormHelper->end that adds blueprintCSS markup
	 *
	 * If $options is set a form submit button will be created.
	 *
	 * @param mixed $options as a string will use $options as the value of button,
	 * 						array usage:
	 * 							array('label' => 'save'); value="save"
	 * 							array('label' => 'save', 'name' => 'Whatever'); value="save" name="Whatever"
	 * 							array('name' => 'Whatever'); value="Submit" name="Whatever"
	 * 							array('label' => 'save', 'name' => 'Whatever', 'div' => 'good') <div class="good"> value="save" name="Whatever"
	 * 							array('label' => 'save', 'name' => 'Whatever', 'div' => array('class' => 'good')); <div class="good"> value="save" name="Whatever"
	 *
	 * @return string a closing FORM tag optional submit button.
	 * @access public
	 * @author Dave Mahon
	 */
	function end($options = null) {
		if ($options !== null) :
			if (is_string($options)) $options = array('label'=>$options);
			if (isset($options['div'])) :
				$options['div'] = $this->addClass($options['div'], 'clear');
			else :
				$options['div'] = array('class'=>'clear');
			endif;
		endif;
		return $this->Form->end($options);
	}

	/**
	 * Wrapper for HtmlHelper->div that adds blueprintCSS markup for class clear
	 *
	 * @param string $fieldName This should be "Modelname.fieldname", "Modelname/fieldname" is deprecated
	 * @param string $class CSS class name of the div element.
	 * @param string $text String content that will appear inside the div element.
	 * 						If null, only a start tag will be printed
	 * @param array $attributes Additional HTML attributes of the DIV tag
	 * @param boolean $escape If true, $text will be HTML-escaped
	 * @return string The formatted DIV element
	 * @access public
	 * @author Dave Mahon
	 */
	function clear($class = null, $text = null, $attributes = array(), $escape = false) {
		if (strlen($class) > 0) :
			$class .= ' clear';
		else :
			$class = 'clear';
		endif;
		return $this->Html->div($class, $text, $attributes, $escape);
	}
	
	/**
	 * Automatically wraps text(s) with classes/ids
	 *
	 * @param boolean $wrapAll whether or not to wrap each text with the same class. true by default
	 * @param string/array $texts string or array of text that should be wrapped
	 * @param string/array $classes string or array of classes to wrap the texts
	 * @param string/array $ids string or array of ids to wrap the texts
	 * @return void
	 * @author Jose Diaz-Gonzalez
	 **/
	function span($wrapAll = true, $texts = NULL, $classes = NULL, $ids = null) {
		$finalText = '';
		if (is_array($texts)) :
			if ($wrapAll) :
				foreach ($texts as $text) :
					$finalText .= "<div";
					if (isset($ids)) :
						$finalText .= " id=\"$ids\"";
					endif;
					if (isset($classes)) :
						$finalText .= " class=\"$classes\"";
					endif;
					$finalText .= ">$text</div>";
				endforeach;
			else :
				$i = 0;
				foreach($texts as $text) :
					$finalText .= "<div";
					if (isset($ids)) :
						$finalText .= " id=\"" . $ids[$i] . "\"";
					endif;
					if (isset($classes)) :
						$finalText .= " class=\"" . $classes[$i] . "\"";
					endif;
					$finalText .= ">$text</div>";
				endforeach;
			endif;
		else :
			$finalText .= "<div";
			if (isset($ids)) :
				$finalText .= " id=\"" . $ids[$i] . "\"";
			endif;
			if (isset($classes)) :
				$finalText .= " class=\"" . $classes[$i] . "\"";
			endif;
			$finalText .= ">$text</div>";
		endif;
		return $finalText;
	}
}
?>