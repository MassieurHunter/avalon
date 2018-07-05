<?php

class Voteteam_model extends MY_Model
{
	/**
	 * @var string
	 */
	public $table = 'votes_teams';

	/**
	 * @var string
	 */
	public $primary_key = 'voteUid';

	/**
	 * @var int
	 */
	protected $voteUid;
	/**
	 * @var int
	 */
	protected $gameUid;
	/**
	 * @var int
	 */
	protected $playerUid;
	/**
	 * @var int
	 */
	protected $teamUid;
	/**
	 * @var bool
	 */
	protected $success;

	/**
	 * @param int $gameUid
	 * @param int $playerUid
	 * @param int $teamUid
	 * @return Voteteam_model
	 */
	public function initWithGamePlayerAndTeam(int $gameUid, int $playerUid, int $teamUid): Voteteam_model {

		$infos = $this->db
			->where('gameUid', $gameUid)
			->where('playerUid', $playerUid)
			->where('teamUid', $teamUid)
			->get($this->table)
			->row();

		$this->init(false, $infos);

		return $this;

	}

	/**
	 * @return int
	 */
	public function getVoteUid(): int {
		return (int)$this->voteUid;
	}

	/**
	 * @param int $voteUid
	 * @return Voteteam_model
	 */
	public function setVoteUid($voteUid): Voteteam_model {
		$this->voteUid = $voteUid;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getGameUid(): int {
		return (int)$this->gameUid;
	}

	/**
	 * @param int $gameUid
	 * @return Voteteam_model
	 */
	public function setGameUid($gameUid): Voteteam_model {
		$this->gameUid = $gameUid;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPlayerUid(): int {
		return (int)$this->playerUid;
	}

	/**
	 * @param int $playerUid
	 * @return Voteteam_model
	 */
	public function setPlayerUid($playerUid): Voteteam_model {
		$this->playerUid = $playerUid;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getTeamUid(): int {
		return (int)$this->teamUid;
	}

	/**
	 * @param int $teamUid
	 * @return Voteteam_model
	 */
	public function setTeamUid($teamUid): Voteteam_model {
		$this->teamUid = $teamUid;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSuccess(): bool {
		return $this->success;
	}

	/**
	 * @param bool $success
	 * @return Voteteam_model
	 */
	public function setSuccess(bool $success): Voteteam_model {
		$this->success = $success;
		return $this;
	}


}