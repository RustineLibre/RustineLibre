import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import {ENTRYPOINT} from '@config/entrypoint';
import React, {useState} from 'react';
import {GetStaticProps} from 'next';
import Head from 'next/head';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import WebsiteLayout from '@components/layout/WebsiteLayout';
import {Box, Button, Container, Paper, Typography} from '@mui/material';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import {RepairerRegistrationProvider} from '@contexts/RepairerRegistrationContext';
import {RegistrationTunnel} from '@components/repairers/registration/RegistrationTunnel';
import Link from 'next/link';

type RepairerRegistrationProps = {
  bikeTypesFetched: BikeType[];
  repairerTypesFetched: RepairerType[];
};

const RepairerRegistration: NextPageWithLayout<RepairerRegistrationProps> = ({
  bikeTypesFetched = [],
  repairerTypesFetched = [],
}) => {
  const [success, setSuccess] = useState<boolean>(false);
  const [pendingRegistration, setPendingRegistration] =
    useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleSetSuccess = () => {
    setSuccess(true);
  };
  return (
    <RepairerRegistrationProvider>
      <>
        <Head>
          <title>Devenir réparateur | Rustine Libre</title>
        </Head>
        <WebsiteLayout>
          <Box
            bgcolor="lightprimary.light"
            height="100%"
            width="100%"
            position="absolute"
            top="0"
            left="0"
            zIndex="-1"
          />
          <Container>
            {!success && (
              <Box
                py={4}
                display="flex"
                flexDirection={{xs: 'column', md: 'row'}}
                gap={4}
                position="relative"
                alignItems="flex-start">
                <Box
                  minHeight={{xs: '0', md: 'calc(100vh - 200px)'}}
                  justifyContent="center"
                  display="flex"
                  flexDirection="column"
                  alignItems={{xs: 'center', md: 'flex-start'}}
                  textAlign={{xs: 'center', md: 'left'}}
                  width={{xs: '100%', md: '45%'}}
                  mx="auto"
                  maxWidth={{xs: '600px', md: '100%'}}>
                  <Typography variant="h1" sx={{mb: 4}} color="primary">
                    Devenir réparateur
                  </Typography>
                  <Typography variant="body1">
                    Tu es un.e professionnel.le du vélo, dans une association ou
                    un atelier indépendant ?
                    <br />
                    Tu as envie de rejoindre un collectif de pairs sur ton
                    territoire ?<br />
                    Tu cherches un outil numérique qui te référence et qui te
                    permet de gérer tes rendez-vous avec tes usagers ?
                    <br />
                    Tu peux remplir ce formulaire et ton collectif local
                    reviendra vers toi rapidement!
                    <br />
                  </Typography>
                  <Typography my={2} variant="h4" color="secondary">
                    Inscris-toi !
                  </Typography>
                  <Box
                    sx={{
                      transform: {
                        xs: 'translateX(-30%)',
                        md: 'translateX(-50%) translateY(20%)',
                        lg: 'translateX(-125%) translateY(20%)',
                      },
                      position: {
                        xs: 'absolute',
                        md: 'static',
                      },
                      left: '0',
                      bottom: '10%',
                    }}>
                    <img alt="" src="/img/flower.svg" width="110px" />
                  </Box>
                </Box>
                <RegistrationTunnel
                  repairerTypesFetched={repairerTypesFetched}
                  bikeTypesFetched={bikeTypesFetched}
                  handleSetSuccess={handleSetSuccess}
                />
              </Box>
            )}
            {success && (
              <Paper
                elevation={4}
                sx={{
                  maxWidth: 400,
                  p: 4,
                  mt: 4,
                  mb: {xs: 10, md: 12},
                  mx: 'auto',
                }}>
                <Box>
                  Votre demande d&apos;inscription a bien été enregistrée. Elle
                  est désormais en attente de validation et sera rapidement
                  traitée.
                  <Link href="/" legacyBehavior passHref>
                    <Button variant="outlined" sx={{marginTop: '30px'}}>
                      Retour à l&apos;accueil
                    </Button>
                  </Link>
                </Box>
              </Paper>
            )}
          </Container>
        </WebsiteLayout>
      </>
    </RepairerRegistrationProvider>
  );
};

export const getStaticProps: GetStaticProps = async () => {
  if (!ENTRYPOINT) {
    return {
      props: {},
    };
  }

  const bikeTypesCollection = await bikeTypeResource.getAll(false);
  const bikeTypesFetched = bikeTypesCollection['hydra:member'];

  const repairerTypesCollection = await repairerTypeResource.getAll(false);
  const repairerTypesFetched = repairerTypesCollection['hydra:member'];

  return {
    props: {
      bikeTypesFetched,
      repairerTypesFetched,
    },
    revalidate: 10,
  };
};

export default RepairerRegistration;
