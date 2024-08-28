import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import Head from 'next/head';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import {RegistrationTunnelValidation} from '@components/repairers/registration/RegistrationTunnelValidation';
import React, {ReactElement, useContext, useEffect} from 'react';
import WebsiteLayout from '@components/layout/WebsiteLayout';
import {RegistrationTunnelChoiceWorkshop} from '@components/repairers/registration/RegistrationTunnelChoiceWorkshop';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {useRouter} from 'next/router';

const RepairerValidationRegistration: NextPageWithLayout = () => {
  const {tunnelStep, stepTwoCompleted} = useContext(
    RepairerRegistrationContext
  );
  const router = useRouter();

  useEffect(() => {
    if (tunnelStep !== 'validation' || !stepTwoCompleted) {
      /*
                  router.push('/reparateur/inscription');
            */
      console.log(tunnelStep, stepTwoCompleted);
    }
  }, []);
  return (
      <RegistrationTunnelValidation />
  );
};

RepairerValidationRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerValidationRegistration;
