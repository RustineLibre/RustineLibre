import {Repairer} from '@interfaces/Repairer';
import {RepairerEmployee} from '@interfaces/RepairerEmployee';

export interface User {
  '@id': string;
  '@type': string;
  id: string;
  firstName: string;
  lastName: string;
  city?: string;
  street?: string;
  email: string;
  roles: string[];
  repairers: Repairer[];
  repairerEmployee?: RepairerEmployee;
  plainPassword: string;
  lastConnect?: string;
  emailConfirmed: boolean;
  lastRepairers: Repairer[];
  firebaseToken: string;
  telephone: string;
  createdAt?: string | null;
}
