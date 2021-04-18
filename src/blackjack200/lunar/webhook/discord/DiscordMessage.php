<?php


namespace blackjack200\lunar\webhook\discord;


use Exception;

class DiscordMessage {
	private array $data;

	public function __construct() {
		$this->data = [];
	}

	public function content(string $content) : void {
		if (strlen($content) > 2000) {
			throw new Exception('content is too long');
		}
		$this->data['content'] = $content;
	}

	public function username(string $name) : void {
		$this->data['username'] = $name;
	}

	public function avatar(string $URL) : void {
		$this->data['avatar_url'] = $URL;
	}

	public function getData() : array {
		return $this->data;
	}
}