import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import React, {useContext, useEffect} from 'react';
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

  useEffect(() => {
    if (!formCompleted) {
      !stepOneCompleted
        ? redirectToFirstStep()
        : !stepTwoFirstQuestionCompleted
          ? redirectToChoiceStep()
          : !stepTwoCompleted
            ? redirectToWorkshopStep()
            : redirectToValidationStep();
    }
  });

  const redirectToFirstStep = () => {
    setStepOneCompleted(false);
    setStepTwoFirstQuestionCompleted(false);
    setStepTwoCompleted(false);
    router.push('/reparateur/inscription');
  };

  const redirectToChoiceStep = () => {
    setStepTwoFirstQuestionCompleted(false);
    setStepTwoCompleted(false);
    router.push('/reparateur/inscription/choix-antenne');
  };

  const redirectToWorkshopStep = () => {
    setStepTwoCompleted(false);
    router.push('/reparateur/inscription/mon-enseigne');
  };

  const redirectToValidationStep = () => {
    setFormCompleted(false);
    router.push('/reparateur/inscription/validation');
  };
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
              Votre demande d&apos;inscription a bien été enregistrée. Elle est
              est désormais en attente de validation et sera rapidement traitée.
              <Link href="/" legacyBehavior passHref>
                <Button variant="outlined" sx={{marginTop: '30px'}}>
                  Retour à l&apos;accueil
                </Button>
              </Link>
            </Box>
          </Paper>
        </Container>
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
  <RepairerRegistrationLayout simple={true}>{page}</RepairerRegistrationLayout>
);
export default RepairerSuccessRegistration;
