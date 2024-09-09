import React, {createContext, ReactNode, useState} from 'react';
import {City} from '@interfaces/City';
import {Street} from '@interfaces/Street';
import {RepairerCity} from '@interfaces/RepairerCity';
import {Repairer} from '@interfaces/Repairer';

interface ProviderProps {
  children: ReactNode;
}

interface RepairerRegistrationContext {
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
  isMultipleWorkshop: boolean;
  repairerCities: RepairerCity[] | any;
  hasBoss: boolean;
  stepOneCompleted: boolean;
  stepTwoFirstQuestionCompleted: boolean;
  stepTwoCompleted: boolean;
  formCompleted: boolean;
  isRoving: boolean;
  success: boolean;
  successMessage: string;
  counter: number;
  lastRepairerCreated: Repairer | null;
  fromGoBack: boolean;
  setFromGoBack: (value: boolean) => void;
  setLastRepairerCreated: (value: Repairer | null) => void;
  setCounter: (value: number) => void;
  setSuccessMessage: (value: string) => void;
  setSuccess: (value: boolean) => void;
  setIsRoving: (value: boolean) => void;
  setFormCompleted: (value: boolean) => void;
  setStepTwoCompleted: (value: boolean) => void;
  setStepTwoFirstQuestionCompleted: (value: boolean) => void;
  setStepOneCompleted: (value: boolean) => void;
  setHasBoss: (value: boolean) => void;
  setRepairerCities: (value: RepairerCity[] | any) => void;
  setIsMultipleWorkShop: (value: boolean) => void;
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
}

const initialValue = {
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
  isMultipleWorkshop: false,
  repairerCities: [],
  hasBoss: false,
  stepOneCompleted: false,
  stepTwoFirstQuestionCompleted: false,
  stepTwoCompleted: false,
  formCompleted: false,
  isRoving: false,
  success: false,
  successMessage: '',
  counter: 1,
  lastRepairerCreated: null,
  fromGoBack: false,
  setFromGoBack: () => null,
  setLastRepairerCreated: () => null,
  setCounter: () => null,
  setSuccessMessage: () => null,
  setSuccess: () => null,
  setIsRoving: () => null,
  setFormCompleted: () => null,
  setStepTwoCompleted: () => null,
  setStepTwoFirstQuestionCompleted: () => null,
  setStepOneCompleted: () => null,
  setHasBoss: () => null,
  setRepairerCities: () => null,
  setIsMultipleWorkShop: () => null,
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
};

export const RepairerRegistrationContext =
  createContext<RepairerRegistrationContext>(initialValue);

export const RepairerRegistrationProvider = ({
  children,
}: ProviderProps): JSX.Element => {
  const [firstName, setFirstName] = useState<string>('');
  const [lastName, setLastName] = useState<string>('');
  const [email, setEmail] = useState<string>('');
  const [name, setName] = useState<string>('');
  const [city, setCity] = useState<City | null>(null);
  const [street, setStreet] = useState<Street | null>(null);
  const [streetNumber, setStreetNumber] = useState<string>('');
  const [comment, setComment] = useState<string>('');
  const [isMultipleWorkshop, setIsMultipleWorkShop] = useState<boolean>(false);
  const [repairerTypeSelected, setRepairerTypeSelected] = useState<string[]>(
    []
  );
  const [selectedBikeTypes, setSelectedBikeTypes] = useState<string[]>([]);
  const [repairerCities, setRepairerCities] = useState<RepairerCity[] | any>(
    []
  );
  const [hasBoss, setHasBoss] = useState<boolean>(false);
  const [stepOneCompleted, setStepOneCompleted] = useState<boolean>(false);
  const [stepTwoFirstQuestionCompleted, setStepTwoFirstQuestionCompleted] =
    useState<boolean>(false);
  const [stepTwoCompleted, setStepTwoCompleted] = useState<boolean>(false);
  const [formCompleted, setFormCompleted] = useState<boolean>(false);
  const [isRoving, setIsRoving] = useState<boolean>(false);
  const [success, setSuccess] = useState<boolean>(false);
  const [successMessage, setSuccessMessage] = useState<string>('');
  const [counter, setCounter] = useState<number>(1);
  const [lastRepairerCreated, setLastRepairerCreated] =
    useState<Repairer | null>(null);
  const [fromGoBack, setFromGoBack] = useState<boolean>(false);

  return (
    <RepairerRegistrationContext.Provider
      value={{
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
        isMultipleWorkshop,
        repairerCities,
        hasBoss,
        stepOneCompleted,
        stepTwoFirstQuestionCompleted,
        stepTwoCompleted,
        formCompleted,
        isRoving,
        success,
        successMessage,
        counter,
        lastRepairerCreated,
        fromGoBack,
        setFromGoBack,
        setLastRepairerCreated,
        setCounter,
        setSuccessMessage,
        setSuccess,
        setIsRoving,
        setFormCompleted,
        setStepTwoCompleted,
        setStepTwoFirstQuestionCompleted,
        setStepOneCompleted,
        setHasBoss,
        setRepairerCities,
        setIsMultipleWorkShop,
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
      }}>
      {children}
    </RepairerRegistrationContext.Provider>
  );
};
