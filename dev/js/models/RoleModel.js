import BaseModel from './BaseModel';
import ABuilder from '../tools/ABuilder'

export default class RoleModel extends BaseModel {

  getName() {
    return this.get('name');
  }

  getDescription() {
    return this.get('description');
  }

  isSeenByPerceval() {
    return this.get('isSeenByPerceval');
  }

  isSeenByEvil() {
    return this.get('isSeenByEvil');
  }

  isSeenByMerlin() {
    return this.get('isSeenByMerlin');
  }

  canKillMerlin() {
    return this.get('canKillMerlin');
  }

  getBootstrapClass() {
    return this.get('bootstrapClass');
  }


  displayName() {

    let roleName = new ABuilder(
      'h5',
      {},
      this.getName()
    );

    let roleDesc = new ABuilder(
      'p',
      {},
      this.getDescription()
    );

    let alert = new ABuilder('div', {
        'class': 'alert alert-' + this.getBootstrapClass(),
        'role': 'alert'
      },
      [
        roleName,
        roleDesc
      ]);

    $('.role-infos').append(alert);
  }


}