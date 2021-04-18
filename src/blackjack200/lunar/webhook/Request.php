<?php


namespace blackjack200\lunar\webhook;


use Volatile;

class Request extends Volatile {
	public string $URL;
	private string $data;

	/** @return mixed|null */
	public function getData() { return unserialize($this->data); }

	/**
	 * @param mixed|null $data
	 */
	public function setData($data) : void {
		$this->data = serialize($data);
	}
}