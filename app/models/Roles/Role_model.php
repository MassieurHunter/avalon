<?php

/**
 * Class Role_model
 *
 * @property Log_model $_log
 * @property Player_model $_player_model
 * @property Role_model $subModel
 *
 */
class Role_model extends MY_Model
{


    const GOOD      = 1;
    const MERLIN    = 2;
    const PERCEVAL  = 3;
    const EVIL      = 4;
    const ASSASSIN  = 5;
    const MORDRED   = 6;
    const MORGANA   = 7;
    const OBERON    = 8;
    const TEAM_EVIL = 'evil';
    const TEAM_GOOD = 'good';

    /**
     * @var string
     */
    public $table = 'roles';
    /**
     * @var string
     */
    public $primary_key = 'roleUid';
    /**
     * @var array
     */
    public $basics = [
        'roleUid'          => 'getRoleUid',
        'name'             => 'getName',
        'description'      => 'getDescription',
        'model'            => 'getModel',
        'isGood'           => 'isGood',
        'isSeenByEvil'     => 'isSeenByEvil',
        'isSeenByPerceval' => 'isSeenByPerceval',
        'isSeenByMerlin'   => 'isSeenByMerlin',
    ];
    /**
     * @var int
     */
    protected $roleUid;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $model;
    /**
     * @var int
     */
    protected $nb;
    /**
     * @var bool
     */
    protected $good;

    /**
     * @return string
     */
    public function getDescription(): string {
        return (string)$this->description;
    }

    /**
     * @param string $description
     * @return Role_model
     */
    public function setDescription(string $description): Role_model {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getNb(): int {
        return (int)$this->nb;
    }

    /**
     * @param int $nb
     * @return Role_model
     */
    public function setNb(int $nb): Role_model {
        $this->nb = $nb;
        return $this;
    }

    /**
     * @return string
     */
    public function getBootstrapClass(): string {
        if ($this->isGood()) {

            $class = 'info';

        } else {

            $class = 'dark';

        }

        return $class;
    }

    /**
     * @return bool
     */
    public function isGood(): bool {
        return $this->good == true;
    }

    /**
     * @param bool $good
     * @return Role_model
     */
    public function setGood(bool $good): Role_model {
        $this->good = $good;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return (string)$this->name;
    }

    /**
     * @param string $name
     * @return Role_model
     */
    public function setName(string $name): Role_model {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSeenByMerlin(): boolean {
        return $this->getSubmodel()->isSeenByMerlin();
    }

    /**
     * @return Role_model
     */
    public function getSubmodel(): Role_model {

        if (empty($this->subModel)) {

            $this->load->model('Roles/' . ucfirst($this->getModel()) . '_model', 'subModel');
            $this->subModel->init($this->getRoleUid());

        }

        return $this->subModel;

    }

    /**
     * @return string
     */
    public function getModel(): string {
        return (string)$this->model;
    }

    /**
     * @param string $model
     * @return Role_model
     */
    public function setModel(string $model): Role_model {
        $this->model = $model;
        return $this;
    }

    /**
     * @return int
     */
    public function getRoleUid(): int {
        return (int)$this->roleUid;
    }

    /**
     * @param int $roleUid
     * @return Role_model
     */
    public function setRoleUid(int $roleUid): Role_model {
        $this->roleUid = $roleUid;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSeenByPerceval(): boolean {
        return $this->getSubmodel()->isSeenByPerceval();
    }

    /**
     * @return bool
     */
    public function isSeenByEvil(): boolean {
        return $this->getSubmodel()->isSeenByEvil();
    }

    /**
     * @return bool
     */
    public function canKillMerlin(): boolean {
        return $this->getSubmodel()->canKillMerlin();
    }

    /**
     * @return string
     */
    public function getTeam(): string {
        return $this->isGood() ? self::TEAM_GOOD : self::TEAM_EVIL;
    }


}