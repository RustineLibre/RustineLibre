import React, {createContext, ReactNode, useState} from 'react';
import {City} from '@interfaces/City';
import {Street} from '@interfaces/Street';
import {RepairerCity} from '@interfaces/RepairerCity';

interface ProviderProps {
  children: ReactNode;
}

interface RepairerRegistrationContext {
  tunnelStep: string;
  firstName: string;
  lastName: string;
  email: string;
  name: string;
  city: City | null;
  street: Street | null;
  streetNumber: string;
  comment: string;
  repairerTypeSelected: string[];
  selectedBikeTypes: string[];
  multipleWorkshop: boolean;
  chosen: boolean;
  repairerCities: RepairerCity[] | any;
  hasBoss: boolean;
  choiceValue: string;
  setChoiceValue: (value: string) => void;
  setHasBoss: (value: boolean) => void;
  setRepairerCities: (value: RepairerCity[] | any) => void;
  setChosen: (value: boolean) => void;
  setMultipleWorkShop: (value: boolean) => void;
  setSelectedBikeTypes: (value: string[]) => void;
  setRepairerTypeSelected: (value: string[]) => void;
  setComment: (value: string) => void;
  setName: (value: string) => void;
  setCity: (value: City | null) => void;
  setStreet: (value: Street | null) => void;
  setStreetNumber: (value: string) => void;
  setEmail: (value: string) => void;
  setLastName: (value: string) => void;
  setFirstName: (value: string) => void;
  setTunnelStep: (value: string) => void;
}

const initialValue = {
  tunnelStep: 'workshop',
  firstName: '',
  lastName: '',
  email: '',
  name: '',
  city: null,
  street: null,
  streetNumber: '',
  comment: '',
  repairerTypeSelected: [],
  selectedBikeTypes: [],
  multipleWorkshop: false,
  chosen: false,
  repairerCities: [],
  hasBoss: false,
  choiceValue: '',
  setChoiceValue: () => null,
  setHasBoss: () => null,
  setRepairerCities: () => null,
  setChosen: () => null,
  setMultipleWorkShop: () => null,
  setSelectedBikeTypes: () => null,
  setRepairerTypeSelected: () => null,
  setComment: () => null,
  setName: () => null,
  setCity: () => null,
  setStreet: () => null,
  setStreetNumber: () => null,
  setEmail: () => null,
  setLastName: () => null,
  setFirstName: () => null,
  setTunnelStep: () => null,
};

export const RepairerRegistrationContext =
  createContext<RepairerRegistrationContext>(initialValue);

export const RepairerRegistrationProvider = ({
  children,
}: ProviderProps): JSX.Element => {
  const [tunnelStep, setTunnelStep] = useState<string>('workshop');
  const [firstName, setFirstName] = useState<string>('');
  const [lastName, setLastName] = useState<string>('');
  const [email, setEmail] = useState<string>('');
  const [name, setName] = useState<string>('');
  const [city, setCity] = useState<City | null>(null);
  const [street, setStreet] = useState<Street | null>(null);
  const [streetNumber, setStreetNumber] = useState<string>('');
  const [comment, setComment] = useState<string>('');
  const [chosen, setChosen] = useState<boolean>(false);
  const [multipleWorkshop, setMultipleWorkShop] = useState<boolean>(false);
  const [repairerTypeSelected, setRepairerTypeSelected] = useState<string[]>(
    []
  );
  const [selectedBikeTypes, setSelectedBikeTypes] = useState<string[]>([]);
  const [repairerCities, setRepairerCities] = useState<RepairerCity[] | any>(
    []
  );
  const [hasBoss, setHasBoss] = useState<boolean>(false);
  const [choiceValue, setChoiceValue] = useState('');

  return (
    <RepairerRegistrationContext.Provider
      value={{
        tunnelStep,
        firstName,
        lastName,
        email,
        name,
        city,
        street,
        streetNumber,
        comment,
        repairerTypeSelected,
        selectedBikeTypes,
        multipleWorkshop,
        chosen,
        repairerCities,
        hasBoss,
        choiceValue,
        setChoiceValue,
        setHasBoss,
        setRepairerCities,
        setChosen,
        setMultipleWorkShop,
        setRepairerTypeSelected,
        setSelectedBikeTypes,
        setComment,
        setName,
        setCity,
        setStreet,
        setStreetNumber,
        setEmail,
        setLastName,
        setFirstName,
        setTunnelStep,
      }}>
      {children}
    </RepairerRegistrationContext.Provider>
  );
};
