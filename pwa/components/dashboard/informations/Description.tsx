import React, {SyntheticEvent, useCallback, useEffect, useState} from 'react';
import dynamic from 'next/dynamic';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {repairerTypeResource} from '@resources/repairerTypeResource';
const Editor = dynamic(() => import('@components/form/Editor'), {
  ssr: false,
});
import {
  MenuItem,
  FormControl,
  OutlinedInput,
  Checkbox,
  ListItemText,
  InputLabel,
  Box,
  CircularProgress,
  Typography,
  Button,
  Alert,
  Select,
  SelectChangeEvent,
} from '@mui/material';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import {Repairer} from '@interfaces/Repairer';
import {RequestBody} from '@interfaces/Resource';
import {errorRegex} from '@utils/errorRegex';
import Autocomplete from '@mui/material/Autocomplete';
import TextField from '@mui/material/TextField';
import Stack from '@mui/material/Stack';
import {City, createCities} from '@interfaces/City';
import {searchCity} from '@utils/apiCity';
import {formatCityInput} from '@helpers/formatCityInput';
import {RepairerCity} from '@interfaces/RepairerCity';

interface DescriptionProps {
  repairer: Repairer | null;
  // eslint-disable-next-line no-unused-vars
  updateRepairer: (iri: string, bodyRequest: RequestBody) => void;
}

export const Description = ({
  repairer,
  updateRepairer,
}: DescriptionProps): JSX.Element => {
  const [repairerTypes, setRepairerTypes] = useState<RepairerType[]>([]);
  const [bikeTypes, setBikeTypes] = useState<BikeType[]>([]);
  const [description, setDescription] = useState<string>('');
  const [repairerTypeSelected, setRepairerTypeSelected] = useState<string[]>(
    []
  );
  const [selectedBikeTypes, setSelectedBikeTypes] = useState<string[]>([]);
  const [citiesList, setCitiesList] = useState<City[]>([]);
  const [repairerCities, setRepairerCities] = useState<RepairerCity[] | any>(
    []
  );
  const [pendingRegistration, setPendingRegistration] =
    useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [success, setSuccess] = useState<boolean>(false);

  const fetchCitiesResult = useCallback(
    async (cityStr: string) => {
      const citiesResponse = await searchCity(cityStr, false);
      const cities: City[] = createCities(citiesResponse, false);
      setCitiesList(cities);
    },
    [setCitiesList]
  );

  const handleCityChange = (value: string): void => {
    if (value === '' || value.length < 3) {
      setCitiesList([]);
    } else fetchCitiesResult(value);
  };

  const handleCitySelect = (
    event: SyntheticEvent<Element, Event>,
    cities: any
  ) => {
    let newRepairerCities: RepairerCity[] = [];

    cities.map((city: RepairerCity | City) => {
      if ('lat' in city && 'lon' in city) {
        newRepairerCities.push({
          formatted_name: `${city.name} (${city.postcode})`,
          latitude: city.lat.toString(),
          longitude: city.lon.toString(),
          name: city.name,
          postcode: city.postcode,
        } as RepairerCity);
      } else {
        newRepairerCities.push(city as RepairerCity);
      }
    });

    setRepairerCities(newRepairerCities);
  };

  const fetchRepairerTypes = async () => {
    const responseRepairerTypes = await repairerTypeResource.getAll(false);
    setRepairerTypes(responseRepairerTypes['hydra:member']);
  };

  useEffect(() => {
    if (repairerTypes.length === 0) {
      fetchRepairerTypes();
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const fetchBikeTypes = async () => {
    const responseBikeTypes = await bikeTypeResource.getAll(false);
    setBikeTypes(responseBikeTypes['hydra:member']);
  };

  useEffect(() => {
    if (bikeTypes.length === 0) {
      fetchBikeTypes();
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    if (repairer) {
      setDescription(repairer.description ?? '');
      const repairerTypesSetted = repairer.repairerTypes.map((rt) => rt.name);
      setRepairerTypeSelected(
        repairer.repairerTypes
          .map((repairerType) => {
            return repairerType.name;
          })
          .filter((repairerTypeName) =>
            repairerTypesSetted.includes(repairerTypeName)
          )
      );

      const bikeTypesSupported = repairer.bikeTypesSupported.map(
        (bt) => bt.name
      );
      setSelectedBikeTypes(
        bikeTypes
          .map((bikeType) => {
            return bikeType.name;
          })
          .filter((bikeTypeId) => bikeTypesSupported.includes(bikeTypeId))
      );

      setRepairerCities(repairer.repairerCities);
    }
  }, [
    bikeTypes,
    repairer,
    setSelectedBikeTypes,
    setDescription,
    setRepairerTypeSelected,
  ]);

  const handleChangeRepairerType = (
    event: SelectChangeEvent<typeof repairerTypeSelected>
  ): void => {
    const value = event.target.value;

    setRepairerTypeSelected(
      typeof value === 'string' ? value.split(',') : value
    );
  };
  const handleChangeBikeRepaired = (
    event: SelectChangeEvent<typeof selectedBikeTypes>
  ) => {
    const value = event.target.value;
    setSelectedBikeTypes(typeof value === 'string' ? value.split(',') : value);
  };

  const handleSubmit = async (
    event: React.FormEvent<HTMLFormElement>
  ): Promise<void> => {
    event.preventDefault();
    if (!repairer) return;

    const selectedBikeTypeIRIs: string[] = bikeTypes
      .filter((bikeType) => selectedBikeTypes.includes(bikeType.name))
      .map((bikeType) => bikeType['@id']);

    const repairerTypeSelectedIRIs: string[] = repairerTypes
      .filter((repairerType) =>
        repairerTypeSelected.includes(repairerType.name)
      )
      .map((repairerType) => repairerType['@id']);

    try {
      setPendingRegistration(true);
      await updateRepairer(repairer['@id'], {
        repairerTypes: repairerTypeSelectedIRIs,
        bikeTypesSupported: selectedBikeTypeIRIs,
        description: description,
        repairerCities: repairerCities,
      });
      setSuccess(true);
      setTimeout(() => {
        setSuccess(false);
      }, 3000);
    } catch (e: any) {
      setErrorMessage(
        `Mise à jour impossible : ${e.message?.replace(errorRegex, '$2')}`
      );
      setTimeout(() => {
        setErrorMessage(null);
      }, 3000);
    }
    setPendingRegistration(false);
  };

  return (
    <Box sx={{marginTop: 3}} component="form" onSubmit={handleSubmit}>
      {!repairer && (
        <Typography>Vous ne gérez pas de solution de réparation</Typography>
      )}
      {repairer && (
        <>
          <FormControl fullWidth required sx={{mt: 2, mb: 1}}>
            <InputLabel id="repairer-type-label">Type de réparateur</InputLabel>
            <Select
              labelId="filter-results-label"
              id="filter-results"
              multiple
              fullWidth
              required
              value={repairerTypeSelected}
              renderValue={(selected) => selected.join(', ')}
              onChange={handleChangeRepairerType}
              input={<OutlinedInput label="Type de réparateur" />}
              style={{width: '100%'}}>
              {repairerTypes.map((repairerType) => (
                <MenuItem key={repairerType.id} value={repairerType.name}>
                  <Checkbox
                    checked={repairerTypeSelected.includes(repairerType.name)}
                  />
                  <ListItemText primary={repairerType.name} />
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <FormControl fullWidth required sx={{mt: 2, mb: 1}}>
            <InputLabel id="bike-type-label">Type de vélos</InputLabel>
            <Select
              id="bike-type"
              labelId="bike-type-label"
              required
              multiple
              fullWidth
              value={selectedBikeTypes}
              onChange={handleChangeBikeRepaired}
              input={<OutlinedInput label="Type de vélos" />}
              renderValue={(selected) => selected.join(', ')}>
              {bikeTypes.map((bikeType) => (
                <MenuItem key={bikeType['@id']} value={bikeType.name}>
                  <Checkbox
                    checked={selectedBikeTypes.includes(bikeType.name)}
                  />
                  <ListItemText primary={bikeType.name} />
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <InputLabel shrink sx={{mt: 1, mb: -2, ml: 1}}>
            Description
          </InputLabel>
          <Editor content={description} setContent={setDescription} />
          <Stack spacing={3} sx={{width: 1000}}>
            <Autocomplete
              fullWidth
              multiple
              sx={{mt: 2, mb: 1, p: 0}}
              filterOptions={(options) => options}
              freeSolo
              value={repairerCities}
              options={citiesList}
              getOptionLabel={(city: City | RepairerCity | string) =>
                typeof city === 'string'
                  ? city
                  : formatCityInput(city.name, city.postcode)
              }
              onChange={(event, value) => handleCitySelect(event, value)}
              onInputChange={(event, value) => {
                handleCityChange(value);
              }}
              renderInput={(params) => (
                <TextField
                  label="Vos villes d'intervention"
                  {...params}
                  size="medium"
                />
              )}
            />
          </Stack>

          <Button type="submit" variant="contained" sx={{my: 2}}>
            {!pendingRegistration ? (
              'Enregistrer les informations'
            ) : (
              <CircularProgress size={20} sx={{color: 'white'}} />
            )}
          </Button>
          {errorMessage && (
            <Typography variant="body1" color="error">
              {errorMessage}
            </Typography>
          )}
          {success && (
            <Alert severity="success">Informations mises à jour</Alert>
          )}
        </>
      )}
    </Box>
  );
};

export default Description;
