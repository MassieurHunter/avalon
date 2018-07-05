<?php

/**
 * Class Game_model
 *
 * @property Player_model $_playerModel
 * @property Role_model $_roleModel
 * @property Log_model $_logModel
 * @property History_model $_history
 */
class Game_model extends MY_Model
{
	/**
	 * @var string
	 */
	public $table = 'games';

	/**
	 * @var string
	 */
	public $primary_key = 'gameUid';

	/**
	 * @var string
	 */
	public $player_games_table = 'games_players';
	/**
	 * @var array
	 */
	public $basics = [
		'code'       => 'getCode',
		'maxPlayers' => 'getMaxPlayers',
		'nbPlayers'  => 'getNbPlayers',
		'started'    => 'isStarted',
		'finished'   => 'isFinished',
		'players'    => 'getRealPlayersWithBasicInfos',
	];
	/**
	 * @var array
	 */
	public $advanced = [
		'code'            => 'getCode',
		'maxPlayers'      => 'getMaxPlayers',
		'nbPlayers'       => 'getNbPlayers',
		'started'         => 'isStarted',
		'finished'        => 'isFinished',
		'rolesForCasting' => 'getRolesForCastingWithBasicInfos',
		'players'         => 'getRealPlayersWithBasicInfos',
	];
	/**
	 * @var int
	 */
	protected $gameUid;
	/**
	 * @var string
	 */
	protected $code;
	/**
	 * @var int
	 */
	protected $maxPlayers;
	/**
	 * @var int
	 */
	protected $nbPlayers;
	/**
	 * @var boolean
	 */
	protected $started;
	/**
	 * @var boolean
	 */
	protected $finished;
	/**
	 * @var Player_model[]
	 */
	protected $arrPlayers = [];
	/**
	 * @var Role_model[]
	 */
	protected $arrRoles = [];
	/**
	 * @var Log_model[]
	 */
	protected $arrLogs = [];
	/**
	 * @var array
	 */
	protected $arrVotesForTeams = [];
	/**
	 * @var array
	 */
	protected $arrVotesForQuests = [];
	/**
	 * @var History_model[]
	 */
	protected $arrHistories = [];


	/**
	 * @return string
	 */
	public function getCode(): string {
		return $this->code;
	}


	/**
	 * @param string $code
	 * @return Game_model
	 */
	public function setCode(string $code): Game_model {
		$this->code = $code;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function generateCode(): Game_model {
		$newCode = random_string();
		$this->setCode($newCode);
		return $this;
	}

	/**
	 * @param $code
	 */
	public function initByCode(string $code) {
		$infos = $this->db
			->where('code', $code)
			->get($this->table)
			->row();

		$this->init(false, $infos);
	}

	/**
	 * @return bool
	 */
	public function isStarted(): bool {
		return $this->started;
	}

	/**
	 * @param bool $started
	 * @return Game_model
	 */
	public function setStarted(bool $started): Game_model {
		$this->started = $started;
		return $this;
	}

	public function start() {

		$this
			->setStarted(true)
			->saveModifications();

		$this
			->addMiddleCards()
			->giveRoleToPlayers();

	}

	/**
	 *
	 */
	public function giveRoleToPlayers() {
		$arrRoles = $this->getRolesForCasting();
		$arrPlayer = $this->getPlayers();

		shuffle($arrRoles);
		shuffle($arrPlayer);

		foreach ($arrPlayer as $key => $playerModel) {

			$roleModel = $arrRoles[$key];
			$playerModel->addNewRole($this->getGameUid(), $roleModel);

		}


	}

	/**
	 * @return Role_model[]
	 */
	public function getRolesForCasting(): array {
		$arrRoles = $this->getRoles();
		$nbPlayers = $this->getMaxPlayers();

		return array_splice($arrRoles, 0, $nbPlayers);

	}

	/**
	 * @return Role_model[]
	 */
	public function getRoles(): array {

		if (empty($this->arrRoles)) {
			$this->initRoles();
		}

		return $this->arrRoles;

	}

	/**
	 *
	 */
	public function initRoles() {
		$this->load->model('Roles/role_model', '_roleModel');

		$arrRoles = $this->db
			->get($this->_roleModel->table)
			->result();

		foreach ($arrRoles as $role) {
			$roleModel = clone $this->_roleModel;
			$roleModel->init(false, $role);

			for ($i = 0; $i < $roleModel->getNb(); $i++) {

				$this->arrRoles[] = $roleModel;

			}
		}

	}

	/**
	 * @return int
	 */
	public function getMaxPlayers(): int {
		return (int)$this->maxPlayers;
	}

	/**
	 * @param int $maxPlayers
	 * @return Game_model
	 */
	public function setMaxPlayers(int $maxPlayers): Game_model {
		$this->maxPlayers = $maxPlayers;
		return $this;
	}

	/**
	 * @return Player_model[]
	 */
	public function getPlayers(): array {

		if (empty($this->arrPlayers)) {
			$this->initPlayers();
		}

		return $this->arrPlayers;

	}

	/**
	 *
	 */
	public function initPlayers() {
		$this->load->model('player_model', '_playerModel');

		$arrPlayers = $this->db
			->select($this->_playerModel->table . '.*')
			->where($this->primary_key, $this->getGameUid())
			->join($this->player_games_table, $this->_playerModel->primary_key)
			->order_by('name')
			->get($this->_playerModel->table)
			->result();

		foreach ($arrPlayers as $player) {
			$playerModel = clone $this->_playerModel;
			$playerModel->init(false, $player);

			$this->arrPlayers[$playerModel->getPlayerUid()] = $playerModel;
		}

	}

	/**
	 * @return int
	 */
	public function getGameUid(): int {
		return (int)$this->gameUid;
	}

	/**
	 * @param int $gameUid
	 * @return Game_model
	 */
	public function setGameUid(int $gameUid): Game_model {
		$this->gameUid = $gameUid;
		return $this;
	}

	/**
	 * @param Player_model $oPlayer
	 */
	public function addPlayer(Player_model $oPlayer) {
		if (empty($this->arrPlayers)) {
			$this->initPlayers();
		}

		$insertQuery = $this->db
			->set('gameUid', $this->getGameUid())
			->set('playerUid', $oPlayer->getPlayerUid())
			->get_compiled_insert($this->player_games_table);

		$insertIgnoreQuery = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insertQuery);

		$this->db->query($insertIgnoreQuery);

		$this->arrPlayers[$oPlayer->getPlayerUid()] = $oPlayer;

		$nbPlayers = count($this->arrPlayers);

		$this
			->setNbPlayers($nbPlayers)
			->saveModifications();
	}

	/**
	 * @param Player_model[] $arrPlayers
	 * @return Game_model
	 */
	public function setArrPlayers(array $arrPlayers): Game_model {
		$this->arrPlayers = $arrPlayers;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRolesNameForCasting(): string {

		$rolesName = [];

		foreach ($this->getRolesForCasting() as $role) {
			$rolesName[] = $role->getName();
		}

		return implode(', ', $rolesName);

	}

	/**
	 * @return array
	 */
	public function getRolesForCastingWithBasicInfos(): array {

		$arrRolesforCasting = [];

		foreach ($this->getRolesForCasting() as $role) {

			$arrRolesforCasting[] = $role->getBasicInfos();

		}

		return $arrRolesforCasting;
	}

	/**
	 * @return array
	 */
	public function getPlayersWithBasicInfos(): array {
		$arrPlayers = [];

		foreach ($this->getPlayers() as $playerUid => $player) {
			$arrPlayers[$playerUid] = $player->getBasicInfos();
		}

		return $arrPlayers;
	}

	/**
	 * @return string
	 */
	public function getPlayersName(): string {
		$arrPlayersName = [];

		foreach ($this->getPlayers() as $playerUid => $player) {
			$arrPlayersName[] = $player->getName();
		}

		sort($arrPlayersName);

		return $this->lang->line('players_list') . implode(', ', $arrPlayersName);
	}

	/**
	 * @return int
	 */
	public function getNbPlayers(): int {
		return (int)$this->nbPlayers;
	}

	/**
	 * @param int $nbPlayers
	 * @return Game_model
	 */
	public function setNbPlayers(int $nbPlayers): Game_model {
		$this->nbPlayers = $nbPlayers;
		return $this;
	}

	/**
	 * @param int $playerUid
	 * @return array
	 */
	public function finish(int $playerUid): array {

		if (!$this->isFinished()) {

			$this
				->setFinished(true)
				->saveModifications();

		}

		/**
		 * @todo everything
		 */

		$this->load->model('history_model', '_history');
		$this->_history
			->setPlayerUid($playerUid)
			->setGameUid($this->getGameUid())
			->setWinner($arrMessages['playerWon'])
			->setTeam($currentPlayerTeam)
			->setAllies(implode(',', $playerAllies))
			->create();


		return $arrMessages;

	}

	/**
	 * @return bool
	 */
	public function isFinished(): bool {
		return $this->finished;
	}

	/**
	 * @param bool $finished
	 * @return Game_model
	 */
	public function setFinished(bool $finished): Game_model {
		$this->finished = $finished;
		return $this;
	}

	/**
	 * @return History_model[]
	 */
	public function getArrHistories(): array {

		if (empty($this->arrHistories)) {

			$this->initHistories();

		}

		return $this->arrHistories;
	}

	/**
	 *
	 */
	public function initHistories() {

		$this->arrHistories = [];
		$this->load->model('history_model', '_history');

		$arrHistories = $this->db
			->select('*')
			->where('gameUid', $this->getGameUid())
			->get($this->_history->table)
			->result();

		foreach ($arrHistories as $history) {
			$oHistory = clone $this->_history;
			$this->arrHistories[$oHistory->getPlayerUid()] = $oHistory->init(false, $history);
		}

	}

	/**
	 * @param array $arrHistories
	 * @return Game_model
	 */
	public function setArrHistories(array $arrHistories): Game_model {
		$this->arrHistories = $arrHistories;
		return $this;
	}


}