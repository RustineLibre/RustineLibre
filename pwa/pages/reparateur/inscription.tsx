import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import {ENTRYPOINT} from '@config/entrypoint';
import React, {ReactElement, useContext, useState} from 'react';
import {GetStaticProps} from 'next';
import Head from 'next/head';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import WebsiteLayout from '@components/layout/WebsiteLayout';
import {Box, Button, Container, Paper, Typography} from '@mui/material';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import {
  RepairerRegistrationContext,
  RepairerRegistrationProvider,
} from '@contexts/RepairerRegistrationContext';
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
  // you can already use your context here
  /* Think that on each page you have to check if previous steps are completed (not on the firststep),
    to do this, you have to add steps properties for each step in your context.
    Example:
      - stepOneCompleted -> when the identity form is completed (and valid) you have to put it true when user clic on next button
      - stepTwoFirstQuestionCompleted (antenna) -> if stepOneCompleted is true so you have to put it true when user clic on next button, if stepOneCompleted is false you have to redirect on this stepOne (/repairer/inscription)
      - stepTwoCompleted -> if previous steps are true, you have to put it true when the user have completed this form (and the form is valid) and he clics on next button, if one of the previous steps is false you have to redirect on stepOne (/repairer/inscription)
      - formCompleted => if all previous steps are completed and user checks the case, put this formCompleted to true when submit the form, else if one of the previous steps completed is false, you have to redirect on stepOne (/repairer/inscription)
    usefully for example if a user navigate via browser url /reparateur/inscription/mon-enseigne or if he quit the multiform to navigate on another website page, he must not complete this form part if one of a previous form step is not complete so we have to redirect on stepOne.
    A good idea is to check when the user quit the form (navigating on another page,
    close the browser tab) when he had started
    to complete some fields to avert him (by a modal) that he can lose the data fill-in form */
  const [success, setSuccess] = useState<boolean>(false);

  const handleSetSuccess = () => {
    setSuccess(true);
  };
  return (
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
          {/* Cette partie doit aller dans le RepairerRegistrationLayout, dans cette page, il ne doit y avoir que le formulaire du stepOne */}
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
                Tu es un.e professionnel.le du vélo, dans une association ou un
                atelier indépendant ?
                <br />
                Tu as envie de rejoindre un collectif de pairs sur ton
                territoire ?<br />
                Tu cherches un outil numérique qui te référence et qui te permet
                de gérer tes rendez-vous avec tes usagers ?
                <br />
                Tu peux remplir ce formulaire et ton collectif local reviendra
                vers toi rapidement!
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
            {/* Here create a component for your stepOne form (IdentityForm) and remove this component
            (make the same for other forms components) */}
            <RegistrationTunnel
              repairerTypesFetched={repairerTypesFetched}
              bikeTypesFetched={bikeTypesFetched}
              handleSetSuccess={handleSetSuccess}
            />
          </Box>
          {/* Put this in a page after form submission (/reparateur/inscription/demande-enregistree)
          The page must-check that all steps are completed
          (user must not see this message when browsing on this url) */}
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

RepairerRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationProvider>{page}</RepairerRegistrationProvider>;
};

export default RepairerRegistration;
