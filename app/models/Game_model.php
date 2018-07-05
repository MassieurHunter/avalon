<?php

/**
 * Class Game_model
 *
 * @property Player_model $_playerModel
 * @property Role_model $_roleModel
 * @property History_model $_history
 * @property Votequest_model $_voteQuest
 * @property Voteteam_model $_voteTeam
 */
class Game_model extends MY_Model
{
    const MAX_REFUSED_TEAMS = 5;
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
        'players'    => 'getPlayersWithBasicInfos',
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
        'nbRefusedTeams'  => 'getNbRefusedTeams',
        'players'         => 'getPlayersWithBasicInfos',
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
     * @var array
     */
    protected $arrVotesForTeams = [];
    /**
     * @var array
     */
    protected $arrVotesForQuests = [];
    /**
     * @var int
     */
    protected $nbRefusedTeams = 0;
    /**
     * @var History_model[]
     */
    protected $arrHistories = [];
    /**
     * @var bool
     */
    protected $withOberon = false;
    /**
     * @var bool
     */
    protected $withPerceval = false;
    /**
     * @var bool
     */
    protected $withMorgana = false;
    /**
     * @var bool
     */
    protected $withMordred = false;

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

    /**
     * @return int
     */
    public function getNbRefusedTeams(): int {
        return $this->nbRefusedTeams;
    }

    /**
     * @param int $nbRefusedTeams
     * @return Game_model
     */
    public function setNbRefusedTeams(int $nbRefusedTeams): Game_model {
        $this->nbRefusedTeams = $nbRefusedTeams;
        return $this;
    }


    /**
     *
     */
    public function start() {

        $this
            ->setStarted(true)
            ->saveModifications();

        $this->giveRoleToPlayers();

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
            $playerModel->setRole($this->getGameUid(), $roleModel);

        }


    }

    /**
     * @return Role_model[]
     */
    public function getRolesForCasting(): array {
        $arrRoles = $this->getRoles();
        $nbEvilGood = $this->getNbGoodAndEvil();
        $maxGoods = $nbEvilGood['nbGoods'];
        $maxEvils = $nbEvilGood['nbEvils'];
        $nbGoods = 0;
        $nbEvils = 0;

        $arrRolesForCasting = [];

        /*
         * Adding Merlin and the Assassin by default
         */
        $arrRolesForCasting[] = clone $arrRoles[Role_model::MERLIN];
        $arrRolesForCasting[] = clone $arrRoles[Role_model::ASSASSIN];
        $nbGoods++;
        $nbEvils++;

        /*
         * Adding Mordred
         */
        if ($this->isWithMordred()) {
            $arrRolesForCasting[] = clone $arrRoles[Role_model::MORDRED];
            $nbEvils++;
        }

        /*
         * Adding Oberon
         */
        if ($this->isWithOberon()) {
            $arrRolesForCasting[] = clone $arrRoles[Role_model::OBERON];
            $nbEvils++;
        }

        /*
         * Adding Morgana & Perceval
         */
        if ($this->isWithMorgana()) {
            $arrRolesForCasting[] = clone $arrRoles[Role_model::PERCEVAL];
            $arrRolesForCasting[] = clone $arrRoles[Role_model::MORGANA];
            $nbGoods++;
            $nbEvils++;
        } /*
         * Adding only Perceval
         */
        elseif ($this->isWithPerceval()) {
            $arrRolesForCasting[] = clone $arrRoles[Role_model::PERCEVAL];
            $nbGoods++;
        }

        for ($i = $nbGoods; $i < $maxGoods; $i++) {
            $arrRolesForCasting[] = clone $arrRoles[Role_model::GOOD];
        }

        for ($i = $nbEvils; $i < $maxEvils; $i++) {
            $arrRolesForCasting[] = clone $arrRoles[Role_model::EVIL];
        }


        return $arrRolesForCasting;

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

            $this->arrRoles[$roleModel->getRoleUid()] = $roleModel;
        }

    }

    /**
     * @return array
     */
    public function getNbGoodAndEvil(): array {

        switch ($this->getNbPlayers()) {

            case 5 :
                $nbGoods = 3;
                $nbEvils = 2;
                break;

            case 6 :
                $nbGoods = 4;
                $nbEvils = 2;
                break;

            case 7 :
                $nbGoods = 4;
                $nbEvils = 3;
                break;

            case 8 :
                $nbGoods = 5;
                $nbEvils = 3;
                break;

            case 9 :
                $nbGoods = 6;
                $nbEvils = 3;
                break;

            case 10 :
                $nbGoods = 6;
                $nbEvils = 4;
                break;

            default:
                $nbGoods = 0;
                $nbEvils = 0;
                break;

        }

        return [
            'nbGoods' => $nbGoods,
            'nbEvils' => $nbEvils,
        ];
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
     * @return bool
     */
    public function isWithMordred(): bool {
        return $this->withMordred;
    }

    /**
     * @param bool $withMordred
     * @return Game_model
     */
    public function setWithMordred(bool $withMordred): Game_model {
        $this->withMordred = $withMordred;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithOberon(): bool {
        return $this->withOberon;
    }

    /**
     * @param bool $withOberon
     * @return Game_model
     */
    public function setWithOberon(bool $withOberon): Game_model {
        $this->withOberon = $withOberon;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithMorgana(): bool {
        return $this->withMorgana;
    }

    /**
     * @param bool $withMorgana
     * @return Game_model
     */
    public function setWithMorgana(bool $withMorgana): Game_model {
        $this->withMorgana = $withMorgana;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithPerceval(): bool {
        return $this->withPerceval;
    }

    /**
     * @param bool $withPerceval
     * @return Game_model
     */
    public function setWithPerceval(bool $withPerceval): Game_model {
        $this->withPerceval = $withPerceval;
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
     * @param int $quest
     * @return int
     */
    public function getTeamSizeForQuest(int $quest): int {

        $arrTeamsSizes = $this->getTeamsSizes();

        return $arrTeamsSizes[$quest];

    }

    /**
     * @return array
     */
    public function getTeamsSizes(): array {

        switch ($this->getNbPlayers()) {

            case 5 :
                $arrTeamsSize = [
                    1 => 2,
                    2 => 3,
                    3 => 2,
                    4 => 3,
                    5 => 3,
                ];
                break;

            case 6 :
                $arrTeamsSize = [
                    1 => 2,
                    2 => 3,
                    3 => 4,
                    4 => 3,
                    5 => 4,
                ];
                break;

            case 7 :
                $arrTeamsSize = [
                    1 => 2,
                    2 => 3,
                    3 => 3,
                    4 => 4,
                    5 => 4,
                ];
                break;

            case 8 :
                $arrTeamsSize = [
                    1 => 3,
                    2 => 4,
                    3 => 4,
                    4 => 5,
                    5 => 5,
                ];
                break;

            case 9 :
                $arrTeamsSize = [
                    1 => 3,
                    2 => 4,
                    3 => 4,
                    4 => 5,
                    5 => 5,
                ];
                break;

            case 10 :
                $arrTeamsSize = [
                    1 => 3,
                    2 => 4,
                    3 => 4,
                    4 => 5,
                    5 => 5,
                ];
                break;

            default:
                $arrTeamsSize = [
                    1 => 2,
                    2 => 3,
                    3 => 2,
                    4 => 3,
                    5 => 3,
                ];
                break;

        }

        return $arrTeamsSize;

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
     * @param int $playerUid
     * @return array
     */
    public function finish(int $playerUid): array {

        if (!$this->isFinished()) {

            $this
                ->setFinished(true)
                ->saveModifications();

        }

        $arrPlayers = $this->getPlayers();

        $oPlayer = $arrPlayers[$playerUid];

        /**
         * @todo everything
         */

        $this->load->model('history_model', '_history');

        $this->_history
            ->setPlayerUid($playerUid)
            ->setGameUid($this->getGameUid())
            ->setWinner($arrMessages['playerWon'])
            ->setTeam($oPlayer->getTeam($this->getGameUid()))
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
     * @param array $arrHistories
     * @return Game_model
     */
    public function setArrHistories(array $arrHistories): Game_model {
        $this->arrHistories = $arrHistories;
        return $this;
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
     * @return array
     */
    public function getVotesForQuests(): array {

        if (empty($this->arrVotesForQuests)) {

            $this->initVotesForQuests();

        }

        return $this->arrVotesForQuests;
    }

    /**
     *
     */
    public function initVotesForQuests() {

        $this->arrVotesForQuests = [];
        $this->load->model('votes/votequest_model', '_voteQuest');

        $arrVotes = $this->db
            ->select('*')
            ->where('gameUid', $this->getGameUid())
            ->get($this->_voteQuest->table)
            ->result();

        foreach ($arrVotes as $vote) {
            $oVote = clone $this->_voteQuest;
            $this->arrVotesForQuests[$oVote->getQuest()] = $oVote->init(false, $vote);
        }

    }

    /**
     * @return array
     */
    public function getVotesForTeams(): array {

        if (empty($this->arrVotesForTeams)) {

            $this->initVotesForTeams();

        }

        return $this->arrVotesForTeams;
    }

    /**
     *
     */
    public function initVotesForTeams() {

        $this->arrVotesForTeams = [];
        $this->load->model('votes/voteteam_model', '_voteTeam');

        $arrVotes = $this->db
            ->select('*')
            ->where('gameUid', $this->getGameUid())
            ->get($this->_history->table)
            ->result();

        foreach ($arrVotes as $vote) {
            $oVote = clone $this->_voteTeam;
            $this->arrVotesForTeams[$oVote->getQuest()][$oVote->getTeam()] = $oVote->init(false, $vote);
        }

    }

}