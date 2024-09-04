import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import React, {useCallback, useContext, useEffect} from 'react';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {useRouter} from 'next/router';
import {Box, Paper, Button, Container} from '@mui/material';
import Link from 'next/link';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';

const RepairerSuccessRegistration: NextPageWithLayout = () => {
  const {
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    stepTwoCompleted,
    formCompleted,
    success,
    setStepOneCompleted,
    setStepTwoFirstQuestionCompleted,
    setStepTwoCompleted,
    setFormCompleted,
  } = useContext(RepairerRegistrationContext);
  const router = useRouter();

  const redirectToFirstStep = useCallback(() => {
    setStepOneCompleted(false);
    setStepTwoFirstQuestionCompleted(false);
    setStepTwoCompleted(false);
    setFormCompleted(false);
    router.push('/reparateur/inscription');
  }, [
    setStepOneCompleted,
    setStepTwoFirstQuestionCompleted,
    setStepTwoCompleted,
    setFormCompleted,
    router,
  ]);

  useEffect(() => {
    if (
      !formCompleted ||
      !stepOneCompleted ||
      !stepTwoFirstQuestionCompleted ||
      !stepTwoCompleted
    ) {
      redirectToFirstStep();
    }
  }, [
    formCompleted,
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    stepTwoCompleted,
    redirectToFirstStep,
  ]);

  return (
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
          <Box display={'flex'} flexDirection={'column'}>
            Votre demande d&apos;inscription a bien été enregistrée. Elle est
            désormais en attente de validation et sera rapidement traitée.
            <Link href="/" legacyBehavior passHref>
              <Button
                variant="outlined"
                sx={{marginTop: '30px', width: '60%', alignSelf: 'center'}}>
                Retour à l&apos;accueil
              </Button>
            </Link>
          </Box>
        </Paper>
      )}
      {!success && (
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
              Il semble y avoir eu un problème lors de la création de cette
              antenne. Veuillez vérifier les champs et rééssayer. Si le problème
              persiste, merci de contacter l&apos;administrateur du site.
              <Link href="/inscription/mon-enseigne" legacyBehavior passHref>
                <Button variant="outlined" sx={{marginTop: '30px'}}>
                  Retour à la création d&apos;antenne
                </Button>
              </Link>
            </Box>
          </Paper>
        </Container>
      )}
    </>
  );
};

RepairerSuccessRegistration.getLayout = (page) => (
  <RepairerRegistrationLayout registrationCompleted={true}>
    {page}
  </RepairerRegistrationLayout>
);
export default RepairerSuccessRegistration;
