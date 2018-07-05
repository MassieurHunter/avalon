<?php


/**
 *
 * @property \Game_model $_game
 * @property \Player_model $_oTestPlayer
 * @property \Role_model $_roleModel
 * @property \Team_model $newTeam
 * @property \Votequest_model $newVoteQuest
 * @property \Votequest_model $_voteQuest
 * @property \Voteteam_model $newVoteTeam
 * @property \Voteteam_model $_voteTeam
 * @property \History_model $_history
 *
 */
class Player_model extends MY_Model
{
    /**
     * @var string
     */
    public $table = 'players';

    /**
     * @var string
     */
    public $primary_key = 'playerUid';

    /**
     * @var string
     */
    public $player_games_table = 'games_players';

    /**
     * @var string
     */
    public $player_roles_table = 'players_game_roles';
    /**
     * @var array
     */
    public $basics = [
        'playerUid' => 'getPlayerUid',
        'name'      => 'getName',
    ];
    /**
     * @var int
     */
    protected $playerUid;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var string
     */
    protected $theme;
    /**
     * @var Role_model[]
     */
    protected $arrRoles = [];
    /**
     * @var Game_model[]
     */
    protected $arrGames = [];
    /**
     * @var array
     */
    protected $arrGamesHistory = [];

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Player_model
     */
    public function setName(string $name): Player_model {
        $this->name = $name;
        return $this;
    }

    /**
     * Init the user with email and test the password
     *
     * @param string $name
     * @param string $password
     *
     *
     * array['result']  boolean login successfull
     * array['message'] string translation key
     * @return array
     */
    public function login(string $name, string $password): array {
        $success = false;

        /*
         * check if the inputs aren't empty
         */
        if ($name && $password) {

            $this->initFromName($name);
            /*
             * Check if the player exists
             */
            if ($this->getPlayerUid()) {
                /*
                 * Check if the password is correct
                 */
                if ($this->verifyPassword($password)) {

                    $success = true;
                    $message = 'login_success';
                    $this->createCookieAndSession();

                } else {

                    $message = 'error_wrong_name_password';

                }// end password
            } else {

                $message = 'error_wrong_name_password';

            }// end player exists

        } else {

            $message = 'error_no_data';

        }// end inputs

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    /**
     * @param $name
     * @return Player_model
     */
    public function initFromName(string $name): Player_model {
        $player = $this->db
            ->select('*')
            ->from($this->table)
            ->where('name', $name)
            ->get()
            ->row();
        if (!empty($player)) {
            $this->init(false, $player);
        }
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
     * @return Player_model
     */
    public function setPlayerUid(int $playerUid): Player_model {
        $this->playerUid = $playerUid;
        return $this;
    }

    /**
     * Test the password for the user
     *
     * @param string $password
     * @param boolean $hashed
     * @return boolean
     */
    public function verifyPassword(string $password, bool $hashed = false): bool {
        return $hashed
            ? $password == $this->getPassword()
            : password_verify($password, $this->getPassword());
    }

    /**
     * @return string
     */
    public function getPassword(): string {
        return (string)$this->password;
    }

    /**
     * @param string $password
     * @return Player_model
     */
    public function setPassword(string $password): Player_model {
        $this->password = $password;
        return $this;
    }

    /**
     *
     */
    public function createCookieAndSession() {

        $autoLogCookie = [
            'name'   => 'autoLog',
            'value'  => $this->getPlayerUid() . ':' . $this->getPassword(),
            'expire' => strtotime('+1 year'),
            'path'   => '/',
        ];

        $this->session->set_userdata('autoLog', $this->getPlayerUid() . ':' . $this->getPassword());
        $this->input->set_cookie($autoLogCookie);

    }

    /**
     * @return string
     */
    public function getTheme(): string {
        return (string)$this->theme;
    }

    /**
     * @param string $theme
     * @return Player_model
     */
    public function setTheme(string $theme): Player_model {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @param string $password
     * @return Player_model
     */
    public function hashAndSetPassword(string $password): Player_model {
        $this->setPassword($this->hashPassword($password));
        return $this;
    }

    /**
     * Return the hashed version of the inputed password
     *
     * @param string $password
     * @return string
     */
    public function hashPassword(string $password): string {

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

        return $hashedPassword;
    }

    /**
     * Login with ws_auth cookie or session
     *
     * @return boolean
     */
    public function autoLogin(): bool {
        $this->load->model('player/player_model', '_oTestPlayer');
        $autoLogString = $this->session->userdata('autoLog');
        $splitedAutoLog = explode(':', $autoLogString);
        $ok = false;

        /*
         * We test if the auto-login string is valid
         */
        if (count($splitedAutoLog) == 2) {

            $playerUid = $splitedAutoLog[0];
            $hashedPassword = $splitedAutoLog[1];
            /*
             * we test if the two inputs aren't empty
             */
            if ($playerUid && $hashedPassword) {
                /*
                 * We init the player's infos from his playerUid
                 */
                $this->_oTestPlayer->init($playerUid);

                if ($this->_oTestPlayer->getPlayerUid()) {
                    /*
                     * We test the hashed password
                     */
                    if ($this->_oTestPlayer->verifyPassword($hashedPassword, true)) {
                        $ok = true;
                        $this->init($playerUid);
                        $this->createCookieAndSession();
                    }
                }
            }
        }

        return $ok;
    }

    /**
     * @param Game_model $game
     * @return array
     */
    public function joinGame(Game_model $game): array {

        $success = false;

        if ($this->getPlayerUid()) {

            if ($game->getGameUid()) {

                if (!$game->isFinished()) {

                    if ($game->getNbPlayers() < $game->getMaxPlayers() || $this->isInGame($game)) {

                        $game->addPlayer($this);
                        $success = true;
                        $message = 'joining_game';

                    } else {

                        $message = 'error_game_full';
                    }

                } else {

                    $message = 'error_game_finished';

                }

            } else {
                $message = 'error_game_not_exists';
            }

        } else {
            $message = 'error_not_logged_in';
        }

        return [
            'success' => $success,
            'message' => $message,
        ];

    }

    /**
     * @param Game_model $game
     * @return bool
     */
    public function isInGame(Game_model $game): bool {
        $arrPlayers = $game->getPlayers();


        return isset($arrPlayers[$this->getPlayerUid()]);
    }

    /**
     * @param int $gameUid
     * @param Role_model $role
     */
    public function setRole(int $gameUid, Role_model $role) {

        $insertQuery = $this->db
            ->set('playerUid', $this->getPlayerUid())
            ->set('gameUid', $gameUid)
            ->set('roleUid', $role->getRoleUid())
            ->get_compiled_insert($this->player_roles_table);

        $insertIgnoreQuery = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insertQuery);

        $this->db->query($insertIgnoreQuery);

        $this->arrRoles[$gameUid] = $role;

    }

    /**
     * @param int $gameUid
     * @return array
     */
    public function getRoleWithBasicInfos(int $gameUid): array {
        return $this->getRole($gameUid)->getBasicInfos();
    }

    /**
     * @param int $gameUid
     * @return Role_model
     */
    public function getRole(int $gameUid): Role_model {

        if (!isset($this->arrRoles[$gameUid])) {
            $this->initRole($gameUid);
        }

        return $this->arrRoles[$gameUid];
    }

    public function getTeam(int $gameUid): string {

        return $this->getRole($gameUid)->getTeam();
    }


    /**
     * @param int $gameUid
     */
    public function initRole(int $gameUid) {

        $this->load->model('Roles/role_model', '_roleModel');
        $this->arrRoles[$gameUid] = clone $this->_roleModel;

        $role = $this->db
            ->select($this->_roleModel->table . '.*')
            ->where('playerUid', $this->getPlayerUid())
            ->where('gameUid', $gameUid)
            ->join($this->player_roles_table, $this->_roleModel->primary_key)
            ->get($this->_roleModel->table)
            ->row(0);

        $this->arrRoles[$gameUid]->init(false, $role);

    }

    /**
     * @param int $gameUid
     * @param int $quest
     * @param Player_model[] ...$players
     * @return Team_model
     */
    public function createTeamForQuest($gameUid, $quest, ...$players): Team_model {

        $this->load->model('team_model', 'newTeam');

        $this->newTeam
            ->setGameUid($gameUid)
            ->setPlayerUid($this->getPlayerUid())
            ->setQuest($quest);

        foreach ($players as $key => $player) {

            $playerNumber = $key + 1;
            $setter = 'setPlayer' . $playerNumber;
            $this->newTeam->$setter($player);

        }

        $this->newTeam->create();


        return $this->newTeam;
    }


    /**
     * @param int $gameUid
     * @param int $teamUid
     * @return bool
     */
    public function getVoteForTeam(int $gameUid, int $teamUid): bool {

        $this->load->model('votes/voteteam_model', '_voteTeam');
        $this->_voteTeam->initWithGamePlayerAndTeam($gameUid, $this->getPlayerUid(), $teamUid);

        return $this->_voteTeam->isSuccess();

    }

    /**
     * @param int $gameUid
     * @param int $quest
     * @return bool
     */
    public function getVoteForQuest(int $gameUid, int $quest): bool {

        $this->load->model('votes/votequest_model', '_voteQuest');
        $this->_voteQuest->initWithGamePlayerAndQuest($gameUid, $this->getPlayerUid(), $quest);

        return $this->_voteQuest->isSuccess();

    }


    /**
     * @param int $gameUid
     * @param int $teamUid
     */
    public function voteForTeam(int $gameUid, int $teamUid) {

        $this->load->model('votes/voteteam_model', 'newVoteTeam');
        $this->newVoteTeam
            ->setGameUid($gameUid)
            ->setPlayerUid($this->getPlayerUid())
            ->setTeamUid($teamUid)
            ->create();
    }

    /**
     * @param int $gameUid
     * @param int $quest
     */
    public function voteForQuest(int $gameUid, int $quest) {

        $this->load->model('votes/votequest_model', 'newVoteQuest');
        $this->newVoteQuest
            ->setGameUid($gameUid)
            ->setPlayerUid($this->getPlayerUid())
            ->setQuest($quest)
            ->create();
    }

    /**
     * @param int $gameUid
     * @param int $teamUid
     * @return bool
     */
    public function hasVotedForTeam(int $gameUid, int $teamUid): bool {

        $this->load->model('votes/voteteam_model', '_voteTeam');
        $this->_voteTeam->initWithGamePlayerAndTeam($gameUid, $this->getPlayerUid(), $teamUid);

        return $this->_voteTeam->getVoteUid() > 0;

    }

    /**
     * @param int $gameUid
     * @param int $quest
     * @return bool
     */
    public function hasVotedForQuest(int $gameUid, $quest): bool {

        $this->load->model('votes/votequest_model', '_voteQuest');
        $this->_voteQuest->initWithGamePlayerAndQuest($gameUid, $this->getPlayerUid(), $quest);

        return $this->_voteQuest->getVoteUid() > 0;

    }


    /**
     * @param int $gameUid
     * @param int $teamUid
     */
    public function cancelVoteForTeam(int $gameUid, int $teamUid) {

        $this->load->model('votes/voteteam_model', '_voteTeam');
        $this->_voteTeam
            ->initWithGamePlayerAndTeam($gameUid, $this->getPlayerUid(), $teamUid)
            ->delete();

    }

    /**
     * @param int $gameUid
     * @param int $quest
     */
    public function cancelVoteForQuest(int $gameUid, int $quest) {

        $this->load->model('votes/votequest_model', '_voteQuest');
        $this->_voteQuest
            ->initWithGamePlayerAndQuest($gameUid, $this->getPlayerUid(), $quest)
            ->delete();

    }


    /**
     * @param int $gameUid
     * @return bool
     */
    public function hasPlayed(int $gameUid): bool {
        $queryResult = $this->db
            ->select('played')
            ->where($this->primary_key, $this->getPlayerUid())
            ->where('gameUid', $gameUid)
            ->get($this->player_games_table)
            ->row();

        return $queryResult->played === '1';
    }

    /**
     * @param int $gameUid
     * @return History_model
     */
    public function getGameHistory(int $gameUid): History_model {
        $this->load->model('history_model', '_history');

        if (empty($this->arrGamesHistory)) {

            $this->initGamesHistory();

        }

        return isset($this->arrGamesHistory[$gameUid]) ? $this->arrGamesHistory[$gameUid] : $this->_history;
    }

    /**
     *
     */
    public function initGamesHistory() {

        $this->arrGamesHistory = [];
        $this->load->model('history_model', '_history');

        $arrGamesHistory = $this->db
            ->select('*')
            ->where('playerUid', $this->getPlayerUid())
            ->order_by('gameUid')
            ->get($this->_history->table)
            ->result();

        foreach ($arrGamesHistory as $history) {
            $oHistory = clone $this->_history;
            $this->arrGamesHistory[$oHistory->getGameUid()] = $oHistory->init(false, $history);
        }

    }

    /**
     * @param array $arrGamesHistory
     * @return Player_model
     */
    public function setArrGamesHistory(array $arrGamesHistory): Player_model {
        $this->arrGamesHistory = $arrGamesHistory;
        return $this;
    }

    /**
     * @return Game_model[]
     */
    public function getGames(): array {

        if (empty($this->arrGames)) {

            $this->initGames();

        }

        return $this->arrGames;

    }

    /**
     *
     */
    public function initGames() {

        $this->arrGames = [];
        $this->load->model('game_model', '_game');

        $arrGames = $this->db
            ->select($this->_game->table . '*')
            ->join($this->player_games_table, $this->_game->primary_key)
            ->where('playerUid', $this->getPlayerUid())
            ->order_by('gameUid')
            ->get($this->_game->table)
            ->result();

        foreach ($arrGames as $game) {
            $oGame = clone $this->_game;
            $this->arrGames[$oGame->getGameUid()] = $oGame->init(false, $game);
        }

    }

    /**
     * @param Game_model[] $arrGames
     * @return Player_model
     */
    public function setArrGames(array $arrGames): Player_model {

        $this->arrGames = $arrGames;
        return $this;

    }


}