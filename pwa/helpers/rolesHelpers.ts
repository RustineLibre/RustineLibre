import {User} from '@interfaces/User';
import {Repairer} from '@interfaces/Repairer';

export const isCyclist = (user: User): boolean => {
  return !isBoss(user) && !isAdmin(user) && !isEmployee(user);
};

export const isBoss = (user: User): boolean => {
  return user.roles.includes('ROLE_BOSS');
};

export const isEmployee = (user: User): boolean => {
  return user.roles.includes('ROLE_EMPLOYEE');
};

export const isAdmin = (user: User): boolean => {
  return user.roles.includes('ROLE_ADMIN');
};

export const isItinerant = (user: User): boolean => {
  const repairer = user.repairer
    ? user.repairer
    : user.repairerEmployee
    ? user.repairerEmployee.repairer
    : null;

  if (!repairer) {
    return false;
  }
  if (
    repairer?.repairerTypes.map(
      (repairerType) => repairerType.name === 'Réparateur itinérant'
    )
  ) {
    return true;
  }
  return false;
};

export const isRepairerItinerant = (repairer: Repairer): boolean => {
  if (
    repairer?.repairerTypes.map(
      (repairerType) => repairerType.name === 'Réparateur itinérant'
    )
  ) {
    return true;
  }
  return false;
};
