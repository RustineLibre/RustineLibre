import {User} from '@interfaces/User';
import {Repairer} from '@interfaces/Repairer';
import {AutoDiagnostic} from '@interfaces/AutoDiagnostic';
import {Bike} from '@interfaces/Bike';
import {BikeType} from '@interfaces/BikeType';
import {Discussion} from '@interfaces/Discussion';

export interface Appointment {
  '@id': string;
  '@type': string;
  id: string;
  customer: User | null;
  customerName: string | null;
  createdAt?: string;
  customerPhoneWithoutAccount?: string | null;
  repairer: Repairer;
  autoDiagnostic?: AutoDiagnostic;
  slotTime: string;
  status: string;
  bike?: Bike;
  bikeType?: BikeType;
  latitude?: string;
  longitude?: string;
  address?: string;
  comment?: string;
  discussion?: Discussion;
}
