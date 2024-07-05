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
  const [repairerTypeSelected, setRepairerTypeSelected] =
    useState<RepairerType>(repairer?.repairerType!);
  const [selectedBikeTypes, setSelectedBikeTypes] = useState<string[]>([]);
  const [citiesList, setCitiesList] = useState<City[]>([]);
  const [repairerCities, setRepairerCities] = useState<City[]>([]);
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
    cities: City[]
  ) => {
    let newRepairerCities = [];
    cities.map((city: City) => {
      newRepairerCities.push({
        formatted_name: `${city.name} (${city.postcode})`,
        latitude: city.lat.toString(),
        longitude: city.lon.toString(),
        name: city.name,
        postcode: city.postcode,
      } as City);
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
      setRepairerTypeSelected(repairer.repairerType ?? null);
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

      let cities: City[] = [];
      repairer.repairerCities.map((city) => {
        cities.push({
          formatted_name: `${city.name} (${city.postcode})`,
          lat: city.latitude,
          lon: city.longitude,
          name: city.name,
          postcode: city.postcode,
        } as City);
      });

      setRepairerCities(cities);
    }
  }, [
    bikeTypes,
    repairer,
    setSelectedBikeTypes,
    setDescription,
    setRepairerTypeSelected,
  ]);

  const handleChangeRepairerType = (event: SelectChangeEvent): void => {
    const selectedRepairerType = repairerTypes.find(
      (rt) => rt.name === event.target.value
    );
    setRepairerTypeSelected(selectedRepairerType!);
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

    try {
      setPendingRegistration(true);
      await updateRepairer(repairer['@id'], {
        repairerType: repairerTypeSelected['@id'],
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
              id="repairer-type"
              labelId="repairer-type-label"
              required
              label="Type de réparateur"
              onChange={handleChangeRepairerType}
              value={repairerTypeSelected?.name ?? ''}
              style={{width: '100%'}}>
              {repairerTypes.map((rt) => (
                <MenuItem key={rt.id} value={rt.name}>
                  {rt.name}
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
              getOptionLabel={(city) =>
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
                  label="Ville ou code postal"
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
