import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import React, {useCallback, useContext, useEffect, useState} from 'react';
import {UserFormContext} from '@contexts/UserFormContext';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {
  City,
  createCitiesWithGouvAPI,
  createCitiesWithNominatimAPI,
} from '@interfaces/City';
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
  Card,
  Checkbox,
  CircularProgress,
  FormControlLabel,
  Grid,
  Typography,
} from '@mui/material';
import Link from 'next/link';
import {User} from '@interfaces/User';
import Avatar from '@mui/material/Avatar';
import StorefrontIcon from '@mui/icons-material/Storefront';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import SummarizeIcon from '@mui/icons-material/Summarize';
import {useRouter} from 'next/router';
import {RepairerCity} from '@interfaces/RepairerCity';

const useNominatim = process.env.NEXT_PUBLIC_USE_NOMINATIM !== 'false';

export const RegistrationTunnelValidation = () => {
  const {password} = useContext(UserFormContext);
  const router = useRouter();
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
    isRoving,
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    stepTwoCompleted,
    setFormCompleted,
    setSuccess,
    setSuccessMessage,
    setHasBoss,
    setRepairerCities,
    setName,
    setCity,
    setStreet,
    setStreetNumber,
    setRepairerTypeSelected,
    setComment,
    setSelectedBikeTypes,
    setTunnelStep,
  } = useContext(RepairerRegistrationContext);
  const [cityInput, setCityInput] = useState<string>('');
  const [citiesList, setCitiesList] = useState<City[]>([]);

  const [acceptChart, setAcceptChart] = useState<boolean>(false);
  const [bikeTypes, setBikeTypes] = useState<BikeType[]>([]);
  const [repairerTypes, setRepairerTypes] = useState<RepairerType[]>([]);
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
      !name ||
      !repairerTypeSelected ||
      !city ||
      !street ||
      !streetNumber ||
      selectedBikeTypes.length === 0
    ) {
      setErrorMessage(
        "Il semble manquer des informations nécessaires à l'inscription, veuillez vérifier les champs des formulaires"
      );
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
        /*
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
        setNewBoss(response.owner);*/
        console.log('première enseigne');
        setHasBoss(true);
      } else {
        /*        const response = await repairerResource.post({
          owner: newBoss?.['@id'],
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
        });*/
      }

      setPendingRegistration(false);

      !multipleWorkshop || finish ? handleSuccess() : handleCreateAndContinue();
    } catch (e: any) {
      setErrorMessage(e.message?.replace(errorRegex, '$2'));
      setTimeout(() => {
        setErrorMessage(null);
      }, 3000);
    }
    setPendingRegistration(false);
  };

  const handleSuccess = () => {
    setSuccess(true);
    setFormCompleted(true);
    setTunnelStep('success');
    router.push('/reparateur/inscription/demande-enregistree');
  };

  const handleCreateAndContinue = () => {
    setName('');
    setCity(null);
    setStreet(null);
    setStreetNumber('');
    setRepairerTypeSelected([]);
    setComment('');
    setSelectedBikeTypes([]);
    setRepairerCities([]);
    setTunnelStep('workshop');
    setSuccessMessage('Votre antenne a été créée avec succès');
    router.push('/reparateur/inscription/mon-enseigne');
  };
  const handleCreateAndFinish = () => {
    setFinish(true);
  };

  useEffect(() => {
    if (finish) {
      handleSubmit();
    }
  }, [finish]);

  const handleGoBack = () => {
    setTunnelStep('workshop');
    router.push('/reparateur/inscription/mon-enseigne');
  };

  useEffect(() => {
    if (tunnelStep !== 'validation') {
      !stepOneCompleted
        ? redirectToFirstStep()
        : !stepTwoFirstQuestionCompleted
          ? redirectToChoiceStep()
          : handleGoBack();
    }
  }, []);

  const redirectToFirstStep = () => {
    setTunnelStep('user_info');
    router.push('/reparateur/inscription');
  };
  const redirectToChoiceStep = () => {
    setTunnelStep('choice');
    router.push('/reparateur/inscription/choix-antenne');
  };

  return (
    <>
      <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
        <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
          <Avatar sx={{bgcolor: 'primary.main', mx: 'auto'}}>
            <SummarizeIcon sx={{color: 'white', fontSize: '1.7rem'}} />
          </Avatar>
          <Typography
            variant="h2"
            textAlign="center"
            color="primary.main"
            pb={2}>
            Récapitulatif{' '}
          </Typography>
        </Box>
        <Typography variant="h5" component="label" textAlign={'center'}>
          Veuillez vérifier vos informations et signer la charte avant de
          valider
        </Typography>
      </Box>
      <Card
        elevation={0}
        sx={{
          borderRadius: 6,
          transition: 'all ease 0.5s',
          width: '100%',
          maxWidth: '400px',
          mx: 'auto',
          mt: 2,
          textAlign: 'left',
          border: '1px solid',
          borderColor: 'grey.300',
          p: 0,
        }}>
        <Box display="flex" alignItems="center" p={2} gap={2}>
          <Box display="flex" flexDirection="column" flex={1}>
            <Box
              display="flex"
              flexDirection="row"
              gap={1}
              color="text.secondary"
              justifyContent={'center'}
              textAlign="center">
              <Box display="flex" flexDirection={'column'} alignItems="center">
                <AccountCircleIcon fontSize={'medium'} color="primary" />
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Nom : {lastName}
                </Typography>
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Prénom : {firstName}
                </Typography>
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Email : {email}
                </Typography>
              </Box>
            </Box>
          </Box>
        </Box>
      </Card>
      <Card
        elevation={0}
        sx={{
          borderRadius: 6,
          transition: 'all ease 0.5s',
          width: '100%',
          maxWidth: '400px',
          mx: 'auto',
          mt: 2,
          textAlign: 'left',
          border: '1px solid',
          borderColor: 'grey.300',
          p: 0,
        }}>
        <Box display="flex" alignItems="center" p={2} gap={2}>
          <Box display="flex" flexDirection="column" flex={1}>
            <Box
              display="flex"
              flexDirection="row"
              gap={1}
              color="text.secondary"
              justifyContent={'center'}
              textAlign="center">
              <Box display="flex" flexDirection={'column'} alignItems="center">
                <Avatar sx={{bgcolor: 'primary.main', width: 24, height: 24}}>
                  <StorefrontIcon sx={{color: 'white', fontSize: '1rem'}} />
                </Avatar>
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Nom : {name}
                </Typography>
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Adresse : {streetNumber} {street?.name}, {city?.name} (
                  {city?.postcode})
                </Typography>
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Type de réparateur :{' '}
                  {repairerTypeSelected.map((rt) => rt).join(' / ')}
                </Typography>
                <Typography
                  variant="body1"
                  component="div"
                  textTransform="capitalize"
                  py={1}>
                  Type de vélo : {selectedBikeTypes.map((bt) => bt).join(' / ')}
                </Typography>
                {isRoving && (
                  <Typography
                    variant="body1"
                    component="div"
                    textTransform="capitalize"
                    py={1}>
                    Villes d&apos;intervention itinérance :{' '}
                    {repairerCities
                      .map((rc: RepairerCity) => rc.name)
                      .join(' / ')}
                  </Typography>
                )}
              </Box>
            </Box>
          </Box>
        </Box>
      </Card>
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
      <Box display="flex" flexDirection="column" alignItems="center">
        <Button variant="outlined" onClick={handleGoBack}>
          Retour
        </Button>
        <>
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
                onClick={() => handleSubmit()}
                variant="contained"
                size="large"
                sx={{mt: 2, mx: 'auto'}}>
                {!pendingRegistration ? (
                  'Enregistrer et ajouter une nouvelle antenne'
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
                  'Enregistrer cette antenne et terminer'
                ) : (
                  <CircularProgress size={20} sx={{color: 'white'}} />
                )}
              </Button>
            </>
          )}
        </>
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
            onClick={handleCreateAndFinish}
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
      {errorMessage && (
        <Typography variant="body1" color="error">
          {errorMessage}
        </Typography>
      )}
    </>
  );
};
