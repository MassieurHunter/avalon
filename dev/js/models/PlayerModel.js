import BaseModel from './BaseModel';
import RoleModel from './RoleModel';
import GameModel from './GameModel';
import ABuilder from '../tools/ABuilder';
import Ajax from '../tools/Ajax';
import Forms from "../components/Forms";
import LangModel from "./LangModel";

export default class PlayerModel extends BaseModel {


  setLang(lang) {
    this.set('lang', lang);
  }

  getLangModel() {
    return new LangModel(this.get('lang', {}));
  }

  getPlayerUid() {
    return this.getInt('playerUid');
  }

  getName() {
    return this.get('name');
  }

  getRoleModel() {
    return new RoleModel(this.get('role', {}));
  }

  setRole(role) {
    this.set('role', role);
  }

  getGameModel() {
    return new GameModel(this.get('game', {}));
  }

  setGame(game) {
    this.set('game', game);
  }

  displayRoleName() {
    this.getRoleModel().displayName();
  }

  finishTurn() {

    let alert = new ABuilder('div', {
      'class': 'alert alert-light',
      'role': 'alert'
    }, this.getLangModel().getLine('finished'));

    $('.turn-finished').append(alert);

    $('.action-form-container').html('');

  }

  displayVoteForTeam(Team) {
    let Game = this.getGameModel();
    let Lang = this.getLangModel();

    let playersList = '';

    let acceptButton = new ABuilder(
      'button',
      {
        'class': 'btn btn-primary btn-block mt-2',
        'type': 'submit',
      },
      Lang.getLine('vote_for')
    );

    let refuseButton = new ABuilder(
      'button',
      {
        'class': 'btn btn-danger btn-block mt-2',
        'type': 'submit',
      },
      Lang.getLine('vote_against')
    );

    let title = new ABuilder('h4', {
      'class': 'action-title',
    }, Lang.getLine('vote_for_team'));


    let voteForm = new ABuilder(
      'form',
      {
        'class': 'ajax-form',
        'data-target': 'player/vote/team/'
      },
      [
        playersList,
        acceptButton,
        refuseButton,
      ]
    );

    $('.vote-message').html('');
    $('.vote-form-container').html('');
    $('.vote-form-container')
      .removeClass('d-none')
      .append(title)
      .append(voteForm);

    let forms = new Forms();

  }

  displayVoteForQuest(Quest) {
    let Game = this.getGameModel();
    let Lang = this.getLangModel();

    let successButton = new ABuilder(
      'button',
      {
        'class': 'btn btn-primary btn-block mt-2',
        'type': 'submit',
      },
      Lang.getLine('vote_success')
    );

    let failureButton = new ABuilder(
      'button',
      {
        'class': 'btn btn-danger btn-block mt-2',
        'type': 'submit',
      },
      Lang.getLine('vote_failure')
    );

    let title = new ABuilder('h4', {
      'class': 'action-title',
    }, Lang.getLine('vote_for_quest'));


    let voteForm = new ABuilder(
      'form',
      {
        'class': 'ajax-form',
        'data-target': 'player/vote/quest/'
      },
      [
        successButton,
        failureButton,
      ]
    );

    $('.vote-message').html('');
    $('.vote-form-container').html('');
    $('.vote-form-container')
      .removeClass('d-none')
      .append(title)
      .append(voteForm);

    let forms = new Forms();

  }

}