import React, {ChangeEvent, useCallback, useEffect, useState} from 'react';
import {
  Alert,
  Autocomplete,
  Box,
  Button,
  Checkbox,
  CircularProgress,
  Container,
  FormControl,
  Grid,
  InputLabel,
  ListItemText,
  MenuItem,
  TextField,
  Typography,
} from '@mui/material';
import {Repairer} from '@interfaces/Repairer';
import {formatCityInput} from '@helpers/formatCityInput';
import {
  City,
  createCitiesWithGouvAPI,
  createCitiesWithNominatimAPI,
} from '@interfaces/City';
import {Street} from '@interfaces/Street';
import Select from '@mui/material/Select';
import {RepairerType} from '@interfaces/RepairerType';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import {searchCity, searchStreet} from '@utils/apiCity';
import {BikeType} from '@interfaces/BikeType';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {City as NominatimCity} from '@interfaces/Nominatim';
import {City as GouvCity} from '@interfaces/Gouv';
import {repairerResource} from '@resources/repairerResource';
import {errorRegex} from '@utils/errorRegex';
import {useRouter} from 'next/router';
import {useAuth} from '@contexts/AuthContext';

interface RepairerFormProps {
  repairer: Repairer | null;
}

export const RepairerForm = ({repairer}: RepairerFormProps): JSX.Element => {
  const router = useRouter();
  const {fetchUser} = useAuth();
  const useNominatim = process.env.NEXT_PUBLIC_USE_NOMINATIM !== 'false';
  const [pendingRegistration, setPendingRegistration] =
    useState<boolean>(false);
  const [success, setSuccess] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [name, setName] = useState<string>('');
  const [cityInput, setCityInput] = useState<string>('');
  const [citiesList, setCitiesList] = useState<City[]>([]);
  const [city, setCity] = useState<City | null>(null);
  const [street, setStreet] = useState<Street | null>(null);
  const [streetNumber, setStreetNumber] = useState<string>('');
  const [repairerTypesSelected, setRepairerTypesSelected] = useState<string[]>(
    []
  );
  const [selectedBikeTypes, setSelectedBikeTypes] = useState<string[]>([]);
  const [repairerTypes, setRepairerTypes] = useState<RepairerType[]>([]);
  const [streetList, setStreetList] = useState<Street[]>([]);
  const [bikeTypes, setBikeTypes] = useState<BikeType[]>([]);

  useEffect(() => {
    if (repairer) {
      setName(repairer.name!);
      setStreetNumber(repairer.streetNumber!);
      setRepairerTypesSelected(
        repairer.repairerTypes.map((repairerType) => repairerType.name)
      );
      setSelectedBikeTypes(
        repairer.bikeTypesSupported.map((bikeType) => bikeType.name)
      );

      if (!street && repairer.street && repairer.city && repairer.postcode) {
        setStreet({
          name: repairer.street,
          city: repairer.city,
          postcode: repairer.postcode,
          lat: 0,
          lon: 0,
        } as Street);
      }
      if (!city && repairer.city && repairer.postcode) {
        setCity({
          formatted_name: `${repairer.city} (${repairer.postcode})`,
          id: 0,
          lat: 0,
          lon: 0,
          name: repairer.city,
          postcode: repairer.postcode.slice(0, 2),
        } as City);
        setCityInput(`${repairer.city} (${repairer.postcode})`);
      }
    }
  }, [repairer, city, street]);
  const handleSubmit = async (event: any) => {
    event.preventDefault();

    if (
      !name ||
      !street ||
      !streetNumber ||
      repairerTypesSelected.length === 0 ||
      !city ||
      selectedBikeTypes.length === 0
    ) {
      return;
    }

    try {
      const selectedBikeTypeIRIs: string[] = bikeTypes
        .filter((bikeType) => selectedBikeTypes.includes(bikeType.name))
        .map((bikeType) => bikeType['@id']);

      const selectedRepairerTypesIRIs: string[] = repairerTypes
        .filter((repairerType) =>
          repairerTypesSelected.includes(repairerType.name)
        )
        .map((repairerType) => repairerType['@id']);

      const body = {
        name: name,
        street: street?.name,
        streetNumber: streetNumber,
        city: city.name,
        postcode: city?.postcode,
        bikeTypesSupported: selectedBikeTypeIRIs,
        repairerTypes: selectedRepairerTypesIRIs,
        latitude: `${street?.lat ?? city.lat}`,
        longitude: `${street?.lon ?? city.lon}`,
      };

      setPendingRegistration(true);

      const response = repairer
        ? await repairerResource.put(repairer['@id'], body, {}, true)
        : await repairerResource.post(body);
      fetchUser();
      setPendingRegistration(false);
      setSuccess(true);
      if (!repairer) {
        setTimeout(() => {
          router.push(`/sradmin/boutiques/${response.id}`);
        }, 3000);
      }
    } catch (e: any) {
      setErrorMessage(e.message?.replace(errorRegex, '$2'));
      setTimeout(() => {
        setErrorMessage(null);
      }, 3000);
    }
    setPendingRegistration(false);
  };

  const fetchRepairerTypes = async () => {
    const responseRepairerTypes = await repairerTypeResource.getAll(false);
    setRepairerTypes(responseRepairerTypes['hydra:member']);
  };

  const fetchBikeTypes = async () => {
    const responseBikeTypes = await bikeTypeResource.getAll(false);
    setBikeTypes(responseBikeTypes['hydra:member']);
  };

  const handleChangeStreet = async (
    event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ): Promise<void> => {
    const adresseSearch = event.target.value;
    if (adresseSearch.length >= 3) {
      const streetApiResponse = await searchStreet(adresseSearch, city);
      setStreetList(streetApiResponse);
    }
  };

  const fetchCitiesResult = useCallback(
    async (cityStr: string) => {
      const citiesResponse = await searchCity(cityStr, useNominatim);
      const cities: City[] = useNominatim
        ? createCitiesWithNominatimAPI(citiesResponse as NominatimCity[])
        : createCitiesWithGouvAPI(citiesResponse as GouvCity[]);
      setCitiesList(cities);
    },
    [setCitiesList, useNominatim]
  );

  useEffect(() => {
    if (cityInput === '' || cityInput.length < 3) {
      setCitiesList([]);
    } else fetchCitiesResult(cityInput);
  }, [setCitiesList, fetchCitiesResult, cityInput]);

  useEffect(() => {
    if (repairerTypes.length === 0) {
      fetchRepairerTypes();
    }

    if (bikeTypes.length === 0) {
      fetchBikeTypes();
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <Container component="main" maxWidth="md">
      <Typography component="h1" variant="h5" textAlign={'center'}>
        {repairer ? 'Modifier cette boutique' : "J'ajoute une boutique"}
      </Typography>
      <Box
        component="form"
        onSubmit={handleSubmit}
        noValidate
        sx={{mt: 1, display: 'flex', flexDirection: 'column', gap: 2}}>
        <Grid item xs={12}>
          <TextField
            fullWidth
            required
            id="name"
            label="Nom de votre enseigne"
            name="name"
            autoComplete="name"
            value={name}
            inputProps={{maxLength: 80}}
            onChange={(e) => setName(e.target.value)}
          />
        </Grid>
        <Grid item xs={12}>
          <Autocomplete
            filterOptions={(options) => options}
            freeSolo
            value={cityInput}
            options={citiesList}
            getOptionLabel={(city) =>
              typeof city === 'string'
                ? city
                : formatCityInput(city.name, city.postcode)
            }
            onChange={(event, value) => setCity(value as City)}
            renderInput={(params) => (
              <TextField
                label="Ville ou code postal"
                required
                {...params}
                value={cityInput}
                onChange={(e) => setCityInput(e.target.value)}
              />
            )}
          />
        </Grid>
        {city && (
          <Grid item xs={12}>
            <Autocomplete
              freeSolo
              value={street}
              options={streetList}
              getOptionLabel={(streetObject) =>
                typeof streetObject === 'string'
                  ? streetObject
                  : `${streetObject.name} (${streetObject.city})`
              }
              onChange={(event, value) => setStreet(value as Street)}
              renderInput={(params) => (
                <TextField
                  label="Rue"
                  {...params}
                  value={street}
                  onChange={(e) => handleChangeStreet(e)}
                />
              )}
            />
          </Grid>
        )}
        {city && (
          <Grid item xs={12}>
            <TextField
              fullWidth
              id="streetNumber"
              label="Numéro de la rue"
              name="streetNumber"
              autoComplete="streetNumber"
              value={streetNumber}
              inputProps={{maxLength: 30}}
              onChange={(e) => setStreetNumber(e.target.value)}
            />
          </Grid>
        )}
        <Grid item xs={12}>
          <FormControl fullWidth required>
            <InputLabel id="repairer-types-label">
              Type de réparateur
            </InputLabel>
            <Select
              required
              labelId="repairer-types-label"
              label="Type de réparateur"
              id="repairer-types"
              multiple
              value={repairerTypesSelected}
              onChange={(e) =>
                setRepairerTypesSelected(
                  typeof e.target.value === 'string'
                    ? e.target.value.split(',')
                    : e.target.value
                )
              }
              style={{width: '100%'}}
              renderValue={(selected) => selected.join(', ')}>
              {repairerTypes.map((repairerType) => (
                <MenuItem key={repairerType.id} value={repairerType.name}>
                  <Checkbox
                    checked={
                      repairerTypesSelected.indexOf(repairerType.name) > -1
                    }
                  />
                  <ListItemText primary={repairerType.name} />
                  {repairerType.name}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={12}>
          <FormControl fullWidth required>
            <InputLabel id="bike-type-label">Vélos réparés</InputLabel>
            <Select
              required
              labelId="bike-type-label"
              label="Vélos réparés"
              id="bike-type"
              multiple
              fullWidth
              value={selectedBikeTypes}
              onChange={(e) =>
                setSelectedBikeTypes(
                  typeof e.target.value === 'string'
                    ? e.target.value.split(',')
                    : e.target.value
                )
              }
              renderValue={(selected) => selected.join(', ')}>
              {bikeTypes.map((bikeType) => (
                <MenuItem key={bikeType.name} value={bikeType.name}>
                  <Checkbox
                    checked={selectedBikeTypes.indexOf(bikeType.name) > -1}
                  />
                  <ListItemText primary={bikeType.name} />
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid>
        <Button type="submit" fullWidth variant="outlined" sx={{mt: 3, mb: 2}}>
          {!pendingRegistration ? (
            repairer ? (
              'Editer cette boutique'
            ) : (
              'Ajouter cette boutique'
            )
          ) : (
            <CircularProgress size={20} />
          )}
        </Button>
        {errorMessage && (
          <Typography variant="body1" color="error">
            {errorMessage}
          </Typography>
        )}
        {success && (
          <Alert severity="success">
            Boutique {repairer ? 'mise à jour' : 'ajoutée'}
          </Alert>
        )}
      </Box>
    </Container>
  );
};

export default RepairerForm;
