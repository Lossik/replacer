<?php


namespace Lossik\Replacer;


trait TReplacer
{


	/**
	 * @var Replacer
	 */
	protected $replacer;

	/**
	 * @var Functions
	 */
	protected $functions;


	protected function getValue($item, $need = true)
	{
		if ($this->replacer->isReplaceable($item)) {
			return $this->replacer->replace($item, $need);
		}
		if ($this->functions->isFunction($item)) {
			return $this->functions->callFunction($item);
		}

		return $item;
	}


	protected function initReplacer()
	{
		$this->replacer  = new Replacer();
		$this->functions = new Functions($this->replacer);
	}

}