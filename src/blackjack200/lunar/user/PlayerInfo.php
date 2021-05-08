<?php


namespace blackjack200\lunar\user;


use blackjack200\lunar\user\info\ActionInfo;
use blackjack200\lunar\user\info\LocationInfo;
use blackjack200\lunar\utils\ExpiredCollection;
use blackjack200\lunar\utils\Timestamp;

class PlayerInfo {
	public ExpiredCollection $expired;
	public Timestamp $motion;
	public Timestamp $jump;
	public Timestamp $teleport;
	public LocationInfo $location;
	public ActionInfo $action;

	public function __construct() {
		$this->expired = new ExpiredCollection(32);
		$this->motion = new Timestamp();
		$this->jump = new Timestamp();
		$this->teleport = new Timestamp();
		$this->location = new LocationInfo();
		$this->action = new ActionInfo();
	}
}