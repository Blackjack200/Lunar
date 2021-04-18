<?php


namespace blackjack200\lunar\webhook\discord;


use blackjack200\lunar\webhook\Request;

class DiscordRequest extends Request {
	public function __construct(string $webhookURL, DiscordMessage $message) {
		$this->URL = $webhookURL;
		$this->setData(json_encode($message->getData(), JSON_THROW_ON_ERROR));
	}
}