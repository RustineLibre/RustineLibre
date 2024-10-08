import {User} from '@interfaces/User';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import {MediaObject} from '@interfaces/MediaObject';
import {RepairerCity} from '@interfaces/RepairerCity';

export interface Repairer {
  '@id': string;
  '@type': string;
  id: string;
  owner: User;
  name: string;
  slug: string;
  description?: string;
  city?: string;
  distance?: number;
  postcode?: string;
  country?: string;
  mobilePhone?: string;
  thumbnail?: MediaObject;
  descriptionPicture?: MediaObject;
  street?: string;
  streetNumber?: string;
  rrule: string;
  latitude?: string;
  longitude?: string;
  firstSlotAvailable?: string;
  comment?: string;
  bikeTypesSupported: BikeType[];
  repairerTypes: RepairerType[];
  repairerCities: RepairerCity[];
  enabled: boolean;
  openingHours?: string;
  optionalPage?: string;
  durationSlot?: number;
  numberOfSlots?: number;
  isConnectedToGoogle: boolean;
}
