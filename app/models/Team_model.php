<?php

/**
 * Class Team_model
 *
 * @property Player_model $_player
 */
class Team_model extends MY_Model
{

    /**
     * @var int
     */
    protected $teamUid;
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
    protected $quest;
    /**
     * @var int
     */
    protected $player1;
    /**
     * @var int
     */
    protected $player2;
    /**
     * @var int
     */
    protected $player3;
    /**
     * @var int
     */
    protected $player4;
    /**
     * @var int
     */
    protected $player5;

    /**
     * @var Player_model[]
     */
    protected $arrPlayers = [];

    /**
     * @return int
     */
    public function getTeamUid(): int {
        return $this->teamUid;
    }

    /**
     * @param int $teamUid
     * @return Team_model
     */
    public function setTeamUid(int $teamUid): Team_model {
        $this->teamUid = $teamUid;
        return $this;
    }

    /**
     * @return int
     */
    public function getGameUid(): int {
        return $this->gameUid;
    }

    /**
     * @param int $gameUid
     * @return Team_model
     */
    public function setGameUid(int $gameUid): Team_model {
        $this->gameUid = $gameUid;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayerUid(): int {
        return $this->playerUid;
    }

    /**
     * @param int $playerUid
     * @return Team_model
     */
    public function setPlayerUid(int $playerUid): Team_model {
        $this->playerUid = $playerUid;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuest(): int {
        return $this->quest;
    }

    /**
     * @param int $quest
     * @return Team_model
     */
    public function setQuest(int $quest): Team_model {
        $this->quest = $quest;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayer1(): int {
        return $this->player1;
    }

    /**
     * @param int $player1
     * @return Team_model
     */
    public function setPlayer1(int $player1): Team_model {
        $this->player1 = $player1;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayer2(): int {
        return $this->player2;
    }

    /**
     * @param int $player2
     * @return Team_model
     */
    public function setPlayer2(int $player2): Team_model {
        $this->player2 = $player2;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayer3(): int {
        return $this->player3;
    }

    /**
     * @param int $player3
     * @return Team_model
     */
    public function setPlayer3(int $player3): Team_model {
        $this->player3 = $player3;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayer4(): int {
        return $this->player4;
    }

    /**
     * @param int $player4
     * @return Team_model
     */
    public function setPlayer4(int $player4): Team_model {
        $this->player4 = $player4;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayer5(): int {
        return $this->player5;
    }

    /**
     * @param int $player5
     * @return Team_model
     */
    public function setPlayer5(int $player5): Team_model {
        $this->player5 = $player5;
        return $this;
    }

    /**
     * @return array
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
        $this->load->model('player_model', '_player');
        $this->arrPlayers = [];

        $arrPlayersUid = [];

        for ($i = 1; $i < 6; $i++) {

            $getter = 'getPlayer' . $i;

            if ($this->$getter()) {

                $arrPlayersUid[] = $this->$getter();

            }

        }

        $arrPlayers = $this->db
            ->select()
            ->from($this->_player->table)
            ->where_in($this->_player->primary_key, $arrPlayersUid)
            ->get()
            ->result();

        foreach ($arrPlayers as $player) {
            $oPlayer = clone $this->_player;
            $oPlayer->init(false, $player);
            $this->arrPlayers[$oPlayer->getPlayerUid()] = $oPlayer;
        }

    }

}