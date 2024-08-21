import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import React, {
  useCallback,
  useContext,
  useEffect,
  useState,
} from 'react';
import {UserFormContext} from '@contexts/UserFormContext';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {
  City,
  createCitiesWithGouvAPI,
  createCitiesWithNominatimAPI,
} from '@interfaces/City';
import {Street} from '@interfaces/Street';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {searchCity} from '@utils/apiCity';
import {City as NominatimCity} from '@interfaces/Nominatim';
import {City as GouvCity} from '@interfaces/Gouv';
import {repairerResource} from '@resources/repairerResource';
import {errorRegex} from '@utils/errorRegex';
import {
  Box,
  Button,
  Checkbox,
  CircularProgress,
  FormControlLabel,
  Grid,
  Typography,
} from '@mui/material';
import LetterR from '@components/common/LetterR';
import RegistrationTunnelUserInfo from '@components/repairers/registration/RegistrationTunnelUserInfo';
import RegistrationTunnelWorkshop from '@components/repairers/registration/RegistrationTunnelWorkshop';
import Link from 'next/link';
import {User} from '@interfaces/User';
import {RegistrationTunnelChoiceWorkshop} from '@components/repairers/registration/RegistrationTunnelChoiceWorkshop';

const useNominatim = process.env.NEXT_PUBLIC_USE_NOMINATIM !== 'false';

type RegistrationProps = {
  bikeTypesFetched: BikeType[];
  repairerTypesFetched: RepairerType[];
  handleSetSuccess: () => void;
};
export const RegistrationTunnel = ({
  bikeTypesFetched,
  repairerTypesFetched,
  handleSetSuccess,
}: RegistrationProps) => {
  const {password} = useContext(UserFormContext);
  const {
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
    repairerCities,
    hasBoss,
    setHasBoss,
    setRepairerCities,
    setName,
    setCity,
    setStreet,
    setRepairerTypeSelected,
    setComment,
    setSelectedBikeTypes,
    setTunnelStep,
  } = useContext(RepairerRegistrationContext);
  const [cityInput, setCityInput] = useState<string>('');
  const [citiesList, setCitiesList] = useState<City[]>([]);

  const [acceptChart, setAcceptChart] = useState<boolean>(false);
  const [streetList, setStreetList] = useState<Street[]>([]);
  const [bikeTypes, setBikeTypes] = useState<BikeType[]>(bikeTypesFetched);
  const [repairerTypes, setRepairerTypes] =
    useState<RepairerType[]>(repairerTypesFetched);
  const [pendingRegistration, setPendingRegistration] =
    useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [finish, setFinish] = useState<boolean>(false);
  const [newBoss, setNewBoss] = useState<User | null>(null);

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
    } else fetchCitiesResult(cityInput);
  }, [setCitiesList, fetchCitiesResult, cityInput]);

  const handleSubmit = async (
    event?: React.FormEvent<HTMLFormElement>
  ): Promise<void> => {
    event?.preventDefault();

    if (
      !firstName ||
      !lastName ||
      !email ||
      !password ||
      !repairerTypeSelected ||
      !city ||
      selectedBikeTypes.length === 0
    ) {
      return;
    }

    try {
      const repairerTypeSelectedIRIs: string[] = repairerTypes
        .filter((repairerType) =>
          repairerTypeSelected.includes(repairerType.name)
        )
        .map((repairerType) => repairerType['@id']);
      const selectedBikeTypeIRIs: string[] = bikeTypes
        .filter((bikeType) => selectedBikeTypes.includes(bikeType.name))
        .map((bikeType) => bikeType['@id']);
      setPendingRegistration(true);
      if (!hasBoss) {
        const response = await repairerResource.postRepairerAndUser({
          firstName: firstName,
          lastName: lastName,
          email: email,
          plainPassword: password,
          name: name,
          street: street?.name,
          streetNumber: streetNumber,
          city: city.name,
          postcode: city?.postcode,
          bikeTypesSupported: selectedBikeTypeIRIs,
          repairerTypes: repairerTypeSelectedIRIs,
          repairerCities: repairerCities,
          comment: comment,
          latitude: street?.lat ?? city.lat,
          longitude: street?.lon ?? city.lon,
        });
        setNewBoss(response.owner);
        setHasBoss(true);
      } else {
        console.log(newBoss);
      }

      setPendingRegistration(false);
      !multipleWorkshop || finish
        ? handleSetSuccess()
        : setTunnelStep('workshop');
    } catch (e: any) {
      setErrorMessage(e.message?.replace(errorRegex, '$2'));
      setTimeout(() => {
        setErrorMessage(null);
      }, 3000);
    }
    setPendingRegistration(false);
  };

  const handleCreateAndContinue = () => {
    handleSubmit();
    setName('');
    setCity(null);
    setStreet(null);
    setRepairerTypeSelected([]);
    setComment('');
    setSelectedBikeTypes([]);
    setRepairerCities([]);
  };
  const handleCreateAndFinish = () => {
    setFinish(true);
    handleSubmit();
  };
  return (
    <Box
      component="form"
      onSubmit={handleSubmit}
      noValidate
      sx={{
        mt: 1,
        bgcolor: 'white',
        px: {xs: 3, md: 5},
        py: {xs: 4, md: 5},
        boxShadow: 2,
        width: {xs: '90%', md: '55%'},
        borderRadius: 6,
        mx: 'auto',
        maxWidth: '700px',
        position: 'relative',
      }}>
      <Box
        position="absolute"
        top={{xs: '0', md: '50px'}}
        left={{xs: '100%', md: '0%'}}
        width={{xs: '80px', md: '110px'}}
        sx={{
          transform: {
            xs: 'translateY(-80%) translateX(-110%)',
            md: 'translateX(-85%)',
          },
        }}>
        <LetterR color="secondary" />
      </Box>
      <Grid container spacing={2} direction="column">
        {tunnelStep === 'user_info' && <RegistrationTunnelUserInfo />}
        {tunnelStep === 'choice' && <RegistrationTunnelChoiceWorkshop />}
        {tunnelStep === 'workshop' && (
          <RegistrationTunnelWorkshop
            repairerTypes={repairerTypes}
            bikeTypes={bikeTypes}
          />
        )}
        {tunnelStep === 'validation' && (
          <>
            <Box
              display="flex"
              flexDirection="column"
              gap={2}
              sx={{mx: 'auto'}}>
              <Typography
                variant="h3"
                textAlign="center"
                color="primary.main"
                pb={2}>
                Charte et validation (3/3)
              </Typography>
            </Box>
            <Grid item xs={12}>
              <FormControlLabel
                sx={{
                  py: 1,
                  fontSize: 14,
                }}
                control={
                  <Checkbox
                    checked={acceptChart}
                    onClick={() => setAcceptChart(!acceptChart)}
                  />
                }
                label={
                  <Typography fontSize={14}>
                    Je me reconnais dans les valeurs décrites dans{' '}
                    <Link
                      style={{textDecoration: 'none', fontSize: 14}}
                      href="/notre-charte"
                      rel="canonical"
                      target="_blank">
                      la charte
                    </Link>
                    , et je m&apos;engage à les respecter.{' '}
                  </Typography>
                }
              />
            </Grid>
          </>
        )}
      </Grid>
      {tunnelStep === 'validation' && (
        <Box display="flex" flexDirection="column" alignItems="center">
          <Button variant="outlined" onClick={() => setTunnelStep('workshop')}>
            Retour
          </Button>
          {multipleWorkshop && (
            <>
              <Button
                disabled={
                  !firstName ||
                  !lastName ||
                  !city ||
                  !repairerTypeSelected ||
                  !name ||
                  !email ||
                  !password ||
                  !selectedBikeTypes.length ||
                  !acceptChart
                }
                type="submit"
                variant="contained"
                size="large"
                sx={{mt: 2, mx: 'auto'}}>
                {!pendingRegistration ? (
                  'Enregistrer cette antenne et continuer'
                ) : (
                  <CircularProgress size={20} sx={{color: 'white'}} />
                )}
              </Button>
              <Button
                disabled={
                  !firstName ||
                  !lastName ||
                  !city ||
                  !repairerTypeSelected ||
                  !name ||
                  !email ||
                  !password ||
                  !selectedBikeTypes.length ||
                  !acceptChart
                }
                onClick={() => handleCreateAndFinish()}
                variant="contained"
                size="large"
                sx={{mt: 2, mx: 'auto'}}>
                {!pendingRegistration ? (
                  'Enregistrer cet atelier et terminer'
                ) : (
                  <CircularProgress size={20} sx={{color: 'white'}} />
                )}
              </Button>
            </>
          )}
          {!multipleWorkshop && (
            <Button
              disabled={
                !firstName ||
                !lastName ||
                !city ||
                !repairerTypeSelected ||
                !name ||
                !email ||
                !password ||
                !selectedBikeTypes.length ||
                !acceptChart
              }
              type="submit"
              variant="contained"
              size="large"
              sx={{mt: 2, mx: 'auto'}}>
              {!pendingRegistration ? (
                'Créer mon compte'
              ) : (
                <CircularProgress size={20} sx={{color: 'white'}} />
              )}
            </Button>
          )}
        </Box>
      )}
      {errorMessage && (
        <Typography variant="body1" color="error">
          {errorMessage}
        </Typography>
      )}
    </Box>
  );
};
