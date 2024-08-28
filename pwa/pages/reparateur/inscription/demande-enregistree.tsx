import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import React, {ReactElement, useContext, useEffect} from 'react';
import {RegistrationTunnelChoiceWorkshop} from '@components/repairers/registration/RegistrationTunnelChoiceWorkshop';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {useRouter} from 'next/router';
import {Box, Paper, Button} from '@mui/material';
import Link from 'next/link';

const RepairerSuccessRegistration: NextPageWithLayout = () => {
  const {
    tunnelStep,
    stepOneCompleted,
    stepTwoFirstQuestionCompleted,
    stepTwoCompleted,
    formCompleted,
    success,
  } = useContext(RepairerRegistrationContext);
  const router = useRouter();

  useEffect(() => {
    if (
      tunnelStep !== 'success' ||
      !stepOneCompleted ||
      !stepTwoFirstQuestionCompleted ||
      !stepTwoCompleted ||
      !formCompleted
    ) {
    }
  }, []);
  return (
    <>
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
            Votre demande d&apos;inscription a bien été enregistrée. Elle est
            désormais en attente de validation et sera rapidement traitée.
            <Link href="/" legacyBehavior passHref>
              <Button variant="outlined" sx={{marginTop: '30px'}}>
                Retour à l&apos;accueil
              </Button>
            </Link>
          </Box>
        </Paper>
      )}{' '}
    </>
  );
};

RepairerSuccessRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerSuccessRegistration;
