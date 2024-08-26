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
  const repairers =
    user.repairers && user.repairers.length > 0
      ? user.repairers
      : user.repairerEmployee
        ? [user.repairerEmployee.repairer]
        : [];

  return repairers.some((repairer) =>
    repairer.repairerTypes.some(
      (repairerType) => repairerType.name === 'Réparateur itinérant'
    )
  );
};

export const isRepairerItinerant = (repairer: Repairer): boolean => {
  if (
    repairer?.repairerTypes.some(
      (repairerType) => repairerType.name === 'Réparateur itinérant'
    )
  ) {
    return true;
  }
  return false;
};
