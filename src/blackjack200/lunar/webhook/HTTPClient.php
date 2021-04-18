<?php


namespace blackjack200\lunar\webhook;


use pocketmine\Thread;
use Threaded;

class HTTPClient extends Thread {
	private Threaded $buffer;
	private bool $running;

	public function __construct() {
		$this->buffer = new Threaded();
	}

	public function start(int $options = PTHREADS_INHERIT_ALL) : void {
		$this->running = true;
		parent::start($options);
	}

	public function shutdown() : void {
		$this->synchronized(function () {
			$this->running = false;
		});
	}

	public function submit(Request $req) : void {
		$this->buffer[] = serialize([$req->URL, $req->getData()]);
		$this->notify();
	}

	public function run() : void {
		while ($this->running) {
			$this->processRequest();
			$this->synchronized(function () {
				if ($this->running) {
					$this->wait();
				}
			});
		}
	}

	public function processRequest() : void {
		$reqData = $this->buffer->shift();
		if ($reqData !== null) {
			[$url, $data] = unserialize($reqData);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
			curl_exec($ch);
			curl_close($ch);
		}
	}
}