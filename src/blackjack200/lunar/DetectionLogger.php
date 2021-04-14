<?php


namespace blackjack200\lunar;


use pocketmine\Thread;
use pocketmine\utils\TextFormat;
use Threaded;

class DetectionLogger extends Thread {
	private string $logFile;
	private Threaded $buffer;
	private bool $running;

	public function __construct(string $file) {
		$this->logFile = $file;
		$this->buffer = new Threaded();
		touch($this->logFile);
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

	public function write(string $buffer) : void {
		$this->buffer[] = TextFormat::clean($buffer);
		$this->notify();
	}

	public function run() : void {
		$logResource = fopen($this->logFile, 'ab');
		if (!is_resource($logResource)) {
			throw new \RuntimeException('Cannot open log file');
		}
		while ($this->running) {
			$this->writeStream($logResource);
			$this->synchronized(function () {
				if ($this->running) {
					$this->wait();
				}
			});
		}
		$this->writeStream($logResource);
		fclose($logResource);
	}

	/**
	 * @param resource $stream
	 */
	protected function writeStream($stream) : void {
		while ($this->buffer->count() > 0) {
			/** @var string $line */
			$line = $this->buffer->pop();
			fwrite($stream, $line . "\n");
		}
	}
}