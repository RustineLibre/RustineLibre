import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import React, {ReactElement, useContext, useEffect} from 'react';
import {
  RepairerRegistrationContext,
  RepairerRegistrationProvider,
} from '@contexts/RepairerRegistrationContext';
import {useRouter} from 'next/router';
import {Box, Paper, Button, Container} from '@mui/material';
import Link from 'next/link';
import WebsiteLayout from '@components/layout/WebsiteLayout';

const RepairerSuccessRegistration: NextPageWithLayout = () => {
  const {
    tunnelStep,
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    stepTwoCompleted,
    formCompleted,
    success,
    setTunnelStep,
  } = useContext(RepairerRegistrationContext);
  const router = useRouter();

  /*  useEffect(() => {
    if (tunnelStep !== 'success' || !formCompleted) {
      !stepOneCompleted
        ? redirectToFirstStep()
        : !stepTwoFirstQuestionCompleted
          ? redirectToChoiceStep()
          : !stepTwoCompleted
            ? redirectToWorkshopStep()
            : redirectToValidationStep();
    }
  }, []);*/

  console.log(
    tunnelStep,
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    stepTwoCompleted,
    formCompleted,
    success
  );
  const redirectToFirstStep = () => {
    setTunnelStep('user_info');
    router.push('/reparateur/inscription');
  };

  const redirectToChoiceStep = () => {
    setTunnelStep('choice');
    router.push('/reparateur/inscription/choix-antenne');
  };

  const redirectToWorkshopStep = () => {
    setTunnelStep('workshop');
    router.push('/reparateur/inscription/mon-enseigne');
  };

  const redirectToValidationStep = () => {
    setTunnelStep('validation');
    router.push('/reparateur/inscription/validation');
  };
  return (
    /* Put this in a page after form submission (/reparateur/inscription/demande-enregistree)
          The page must-check that all steps are completed
          (user must not see this message when browsing on this url) */
    <WebsiteLayout>
      <RepairerRegistrationProvider>
        {success && (
          <>
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
            </Container>
          </>
        )}
      </RepairerRegistrationProvider>
    </WebsiteLayout>
  );
};

export default RepairerSuccessRegistration;
