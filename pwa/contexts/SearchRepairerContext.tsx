import React, {createContext, ReactNode, useState} from 'react';
import {Repairer} from '@interfaces/Repairer';
import {City} from '@interfaces/City';
import {BikeType} from '@interfaces/BikeType';

interface ProviderProps {
  children: ReactNode;
}

interface OrderByOption {
  key: string;
  value: string;
}

interface SearchRepairerContext {
  repairers: Repairer[];
  allRepairers: Repairer[];
  cityInput: string;
  showMap: boolean;
  selectedBike: BikeType | null;
  searchRadius: string;
  selectedRepairer: string;
  city: City | null;
  currentPage: number;
  repairerTypeSelected: string[];
  sortChosen: string;
  orderBy: OrderByOption | null;
  filterBy: OrderByOption | null;
  totalItems: number;
  setRepairers: (repairers: Repairer[]) => void;
  setAllRepairers: (repairers: Repairer[]) => void;
  setCityInput: (value: string) => void;
  setShowMap: (value: boolean) => void;
  setSelectedBike: (value: BikeType | null) => void;
  setSearchRadius: (value: string) => void;
  setSelectedRepairer: (value: string) => void;
  setCurrentPage: (value: number) => void;
  setCity: (value: City | null) => void;
  setRepairerTypeSelected: (value: string[]) => void;
  setOrderBy: (value: OrderByOption) => void;
  setFilterBy: (value: OrderByOption) => void;
  setSortChosen: (value: string) => void;
  setTotalItems: (value: number) => void;
}

const initialValue = {
  repairers: [],
  allRepairers: [],
  cityInput: '',
  selectedRepairer: '',
  city: null,
  selectedBike: null,
  searchRadius: '5000',
  showMap: false,
  currentPage: 1,
  repairerTypeSelected: [],
  orderBy: null,
  filterBy: null,
  sortChosen: 'availability',
  totalItems: 0,
  setRepairers: () => [],
  setAllRepairers: () => [],
  setCityInput: () => null,
  setShowMap: () => false,
  setSelectedBike: () => null,
  setSearchRadius: () => null,
  setSelectedRepairer: () => null,
  setCity: () => null,
  setCurrentPage: () => null,
  setRepairerTypeSelected: () => null,
  setOrderBy: () => null,
  setFilterBy: () => null,
  setSortChosen: () => null,
  setTotalItems: () => null,
};

export const SearchRepairerContext =
  createContext<SearchRepairerContext>(initialValue);

export const SearchRepairerProvider = ({
  children,
}: ProviderProps): JSX.Element => {
  const [cityInput, setCityInput] = useState<string>('');
  const [selectedRepairer, setSelectedRepairer] = useState<string>('');
  const [city, setCity] = useState<City | null>(null);
  const [selectedBike, setSelectedBike] = useState<BikeType | null>(null);
  const [searchRadius, setSearchRadius] = useState<string>('5000');
  const [showMap, setShowMap] = useState<boolean>(false);
  const [repairers, setRepairers] = useState<Repairer[]>([]);
  const [allRepairers, setAllRepairers] = useState<Repairer[]>([]);
  const [repairerTypeSelected, setRepairerTypeSelected] = useState<string[]>(
    []
  );
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [orderBy, setOrderBy] = useState<OrderByOption | null>(null);
  const [filterBy, setFilterBy] = useState<OrderByOption | null>(null);
  const [sortChosen, setSortChosen] = useState<string>('availability');
  const [totalItems, setTotalItems] = useState<number>(0);

  return (
    <SearchRepairerContext.Provider
      value={{
        cityInput,
        selectedRepairer,
        city,
        selectedBike,
        searchRadius,
        showMap,
        repairers,
        allRepairers,
        currentPage,
        repairerTypeSelected,
        orderBy,
        filterBy,
        sortChosen,
        totalItems,
        setRepairers,
        setAllRepairers,
        setCityInput,
        setShowMap,
        setSelectedBike,
        setSearchRadius,
        setSelectedRepairer,
        setCity,
        setCurrentPage,
        setRepairerTypeSelected,
        setOrderBy,
        setFilterBy,
        setSortChosen,
        setTotalItems,
      }}>
      {children}
    </SearchRepairerContext.Provider>
  );
};
