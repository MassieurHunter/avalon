import BaseModel from './BaseModel';
import ABuilder from '../tools/ABuilder'

export default class TeamModel extends BaseModel {

    getPlayers() {
        return this.get('players');
    }

    getPlayersList() {
        //todo
    }

}