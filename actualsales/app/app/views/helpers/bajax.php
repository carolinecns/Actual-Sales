<?php
class BajaxHelper extends AppHelper {

	var $helpers = array('BForm');

	/**
	 * Cria um form ajax que será postado via ajax, mantendo a modal aberta quando der erro
	 */
	function form($model = null, $options = array(), $ehAjax = true){
		$divupdate = isset($options['divupdate']) ? $options['divupdate'] : '#modal_dialog';
		unset($options['divupdate']);
		$callback = isset($options['callback']) ? $options['callback'] : 'function(){}';
		unset($options['callback']);
		if($ehAjax)
			$options = array_merge($options, array('onsubmit'=>"return ajaxFormRequest(this, '$divupdate', true, $callback)"));
		return $this->BForm->create($model, $options);
	}
	
}
?>