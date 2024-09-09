import {
  Alert,
  Autocomplete,
  Box,
  Button,
  Checkbox,
  FormControl,
  Grid,
  InputLabel,
  ListItemText,
  MenuItem,
  TextField,
  Typography,
} from '@mui/material';
import {formatCityInput} from '@helpers/formatCityInput';
import {
  City,
  createCitiesWithGouvAPI,
  createCitiesWithNominatimAPI,
} from '@interfaces/City';
import {Street} from '@interfaces/Street';
import Select, {SelectChangeEvent} from '@mui/material/Select';
import React, {
  ChangeEvent,
  SyntheticEvent,
  useCallback,
  useContext,
  useEffect,
  useState,
} from 'react';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {searchCity, searchStreet} from '@utils/apiCity';
import {City as NominatimCity} from '@interfaces/Nominatim';
import {City as GouvCity} from '@interfaces/Gouv';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import {RepairerCity} from '@interfaces/RepairerCity';
import StorefrontIcon from '@mui/icons-material/Storefront';
import Avatar from '@mui/material/Avatar';
import {useRouter} from 'next/router';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import {bikeTypeResource} from '@resources/bikeTypeResource';

const useNominatim = process.env.NEXT_PUBLIC_USE_NOMINATIM !== 'false';

type WorkshopProps = {
  bikeTypesFetched: BikeType[];
  repairerTypesFetched: RepairerType[];
};

export const RegistrationTunnelWorkshop = ({
  bikeTypesFetched,
  repairerTypesFetched,
}: WorkshopProps): JSX.Element => {
  const router = useRouter();
  const {
    name,
    city,
    street,
    streetNumber,
    comment,
    repairerTypeSelected,
    selectedBikeTypes,
    repairerCities,
    isRoving,
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    successMessage,
    counter,
    lastRepairerCreated,
    fromGoBack,
    setFromGoBack,
    setSuccessMessage,
    setIsRoving,
    setStepTwoCompleted,
    setRepairerCities,
    setRepairerTypeSelected,
    setSelectedBikeTypes,
    setComment,
    setName,
    setCity,
    setStreet,
    setStreetNumber,
  } = useContext(RepairerRegistrationContext);
  const [cityInput, setCityInput] = useState<string>('');
  const [citiesList, setCitiesList] = useState<City[]>([]);
  const [streetList, setStreetList] = useState<Street[]>([]);
  const [bikeTypes, setBikeTypes] = useState<BikeType[]>(bikeTypesFetched);
  const [repairerTypes, setRepairerTypes] =
    useState<RepairerType[]>(repairerTypesFetched);
  const fetchRepairerTypes = async () => {
    const responseRepairerTypes = await repairerTypeResource.getAll(false);
    setRepairerTypes(responseRepairerTypes['hydra:member']);
  };

  useEffect(() => {
    if (repairerTypes.length === 0) {
      fetchRepairerTypes();
    }
  }, [repairerTypes]); // eslint-disable-line react-hooks/exhaustive-deps

  const fetchBikeTypes = async () => {
    const responseBikeTypes = await bikeTypeResource.getAll(false);
    setBikeTypes(responseBikeTypes['hydra:member']);
  };

  useEffect(() => {
    if (bikeTypes.length === 0) {
      fetchBikeTypes();
    }
  }, [bikeTypes]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleChangeName = (event: ChangeEvent<HTMLInputElement>): void => {
    setName(event.target.value);
  };

  const fetchCitiesResult = useCallback(
    async (cityStr: string) => {
      const citiesResponse = await searchCity(cityStr, useNominatim);
      const cities: City[] = useNominatim
        ? createCitiesWithNominatimAPI(citiesResponse as NominatimCity[])
        : createCitiesWithGouvAPI(citiesResponse as GouvCity[]);
      setCitiesList(cities);
    },
    [setCitiesList]
  );

  useEffect(() => {
    if (cityInput === '' || cityInput.length < 3) {
      setCitiesList([]);
      return;
    }

    fetchCitiesResult(cityInput);
  }, [setCitiesList, fetchCitiesResult, cityInput]);

  const handleCityChange = (event: ChangeEvent<HTMLInputElement>): void => {
    setCityInput(event.target.value);
  };

  const handleItinerantCityChange = (value: string): void => {
    if (value === '' || value.length < 3) {
      setCitiesList([]);
      return;
    }

    fetchCitiesResult(value);
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

        return;
      }

      newRepairerCities.push(city as RepairerCity);
    });

    setRepairerCities(newRepairerCities);
  };

  const handleChangeStreetNumber = (
    event: ChangeEvent<HTMLInputElement>
  ): void => {
    setStreetNumber(event.target.value);
  };

  const handleChangeStreet = async (
    event: ChangeEvent<HTMLInputElement>
  ): Promise<void> => {
    const adresseSearch = event.target.value;

    if (adresseSearch.length >= 3) {
      const streetApiResponse = await searchStreet(adresseSearch, city);
      setStreetList(streetApiResponse);
    }
  };

  const handleChangeComments = (event: ChangeEvent<HTMLInputElement>): void => {
    setComment(event.target.value);
  };

  const handleChangeRepairerType = (
    event: SelectChangeEvent<typeof repairerTypeSelected>
  ): void => {
    const {
      target: {value},
    } = event;
    setRepairerTypeSelected(
      typeof value === 'string' ? value.split(',') : value
    );
  };

  useEffect(() => {
    if (repairerTypeSelected.length > 0) {
      setIsRoving(
        repairerTypeSelected.some(
          (element) => element === 'Réparateur itinérant'
        )
      );
    }
  }, [repairerTypeSelected]);

  useEffect(() => {
    if (lastRepairerCreated) {
    }
  }, [lastRepairerCreated]);

  const fetchOldRepairerTypes = async () => {
    const oldRepairerTypes: RepairerType[] = [];
    if (lastRepairerCreated?.repairerTypes) {
      // On itère sur les IRI dans lastRepairerCreated.repairerTypes
      lastRepairerCreated.repairerTypes.forEach((iri) => {
        // On trouve le repairerType correspondant dans repairerTypes en comparant les IRI
        const matchingRepairerType = repairerTypes.find(
          (repairerType) => repairerType['@id'] === (iri as unknown as string)
        );

        // Si un repairerType correspondant est trouvé, on l'ajoute à oldRepairerTypes
        if (matchingRepairerType) {
          oldRepairerTypes.push(matchingRepairerType);
        }
      });

      setRepairerTypeSelected(oldRepairerTypes.map((rt) => rt.name));
    }
  };

  console.log(repairerTypeSelected, selectedBikeTypes);
  const fetchOldBikeTypes = async () => {
    const oldBikeTypes: BikeType[] = [];
    if (lastRepairerCreated?.bikeTypesSupported) {
      lastRepairerCreated.bikeTypesSupported.forEach((iri) => {
        const matchingBikeType = bikeTypes.find(
          (bikeType) => bikeType['@id'] === (iri as unknown as string)
        );

        if (matchingBikeType) {
          oldBikeTypes.push(matchingBikeType);
        }
      });

      setSelectedBikeTypes(oldBikeTypes.map((bt) => bt.name));
    }
  };

  const handleChangeBikeRepaired = (
    event: SelectChangeEvent<typeof selectedBikeTypes>
  ) => {
    const {
      target: {value},
    } = event;
    setSelectedBikeTypes(typeof value === 'string' ? value.split(',') : value);
  };

  const handleGoBack = () => {
    if (lastRepairerCreated) {
      setFromGoBack(true);
      router.push('/reparateur/inscription/validation');
      return;
    }
    router.push('/reparateur/inscription/choix-antenne');
  };

  const handleNextStep = () => {
    setStepTwoCompleted(true);
    router.push('/reparateur/inscription/validation');
  };

  useEffect(() => {
    if (!stepOneCompleted || !stepTwoFirstQuestionCompleted) {
      router.push('/reparateur/inscription');
    }
  }, [stepTwoFirstQuestionCompleted, stepOneCompleted, router]);

  useEffect(() => {
    successMessage &&
      setTimeout(() => {
        setSuccessMessage('');
      }, 5000);
  });

  useEffect(() => {
    if (lastRepairerCreated && fromGoBack) {
      setName(lastRepairerCreated.name);
      setCity(
        lastRepairerCreated.city
          ? (lastRepairerCreated.city as unknown as City)
          : null
      );
      setStreet(
        lastRepairerCreated.street
          ? (lastRepairerCreated.street as unknown as Street)
          : null
      );
      setComment(lastRepairerCreated.comment!);
      setStreetNumber(lastRepairerCreated.streetNumber!);
    }
    fetchOldRepairerTypes();
    fetchOldBikeTypes();
    setRepairerCities(lastRepairerCreated?.repairerCities);
  }, [
    fromGoBack,
    lastRepairerCreated,
    setCity,
    setComment,
    setName,
    setRepairerCities,
    setStreet,
    setStreetNumber,
  ]);

  return (
    <>
      {successMessage && (
        <Alert color="success" sx={{my: 2}}>
          {successMessage}
        </Alert>
      )}
      <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
        <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
          <Avatar sx={{bgcolor: 'primary.main', mx: 'auto'}}>
            <StorefrontIcon sx={{color: 'white', fontSize: '1.7rem'}} />
          </Avatar>
          <Typography
            variant="h2"
            textAlign="center"
            color="primary.main"
            pb={2}>
            {counter <= 1 ? 'Mon enseigne' : `Enseigne ${counter}`}
          </Typography>
        </Box>
        <Typography variant="h5" component="label">
          Informations de l&apos;enseigne
        </Typography>
      </Box>
      <>
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
            onChange={handleChangeName}
          />
        </Grid>
        <Grid item xs={12}>
          <Autocomplete
            filterOptions={(options) => options}
            freeSolo
            value={city}
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
                onChange={handleCityChange}
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
                  onChange={handleChangeStreet}
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
              onChange={handleChangeStreetNumber}
            />
          </Grid>
        )}
        <Grid item xs={12}>
          <FormControl fullWidth required>
            <InputLabel id="repairer-type-label">Type de réparateur</InputLabel>
            <Select
              required
              multiple
              id="repairer-type"
              labelId="repairer-type-label"
              label="Type de réparateur"
              onChange={handleChangeRepairerType}
              value={repairerTypeSelected}
              style={{width: '100%'}}
              renderValue={(selected) => selected.join(', ')}>
              {repairerTypes.map((repairer) => (
                <MenuItem key={repairer.id} value={repairer.name}>
                  <Checkbox
                    checked={repairerTypeSelected.indexOf(repairer.name) > -1}
                  />
                  <ListItemText primary={repairer.name} />{' '}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid>
        {isRoving && (
          <Grid item xs={12}>
            <FormControl fullWidth required>
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
                  handleItinerantCityChange(value);
                }}
                renderInput={(params) => (
                  <TextField
                    label="Villes d'intervention itinérance"
                    {...params}
                    size="medium"
                  />
                )}
              />
            </FormControl>
          </Grid>
        )}
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
              onChange={handleChangeBikeRepaired}
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
        <Grid item xs={12}>
          <TextField
            fullWidth
            multiline
            rows={3}
            id="comment"
            label="Commentaires"
            name="comment"
            autoComplete="comment"
            value={comment}
            inputProps={{maxLength: 2000}}
            onChange={handleChangeComments}
          />
        </Grid>
      </>
      <Box
        width={{xs: '100%', md: '80%'}}
        mt={3}
        display="flex"
        mx="auto"
        justifyContent="space-between">
          <Button variant="outlined" onClick={handleGoBack}>
            Retour
          </Button>
        <Button
          onClick={handleNextStep}
          variant="contained"
          disabled={
            !name ||
            !city ||
            !street ||
            !streetNumber ||
            !repairerTypeSelected.length ||
            !selectedBikeTypes.length
          }>
          Suivant
        </Button>
      </Box>
    </>
  );
};

export default RegistrationTunnelWorkshop;
