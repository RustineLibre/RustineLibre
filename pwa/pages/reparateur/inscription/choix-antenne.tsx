import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import React, {ReactElement, useContext, useEffect} from 'react';
import {RegistrationTunnelChoiceWorkshop} from '@components/repairers/registration/RegistrationTunnelChoiceWorkshop';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {useRouter} from 'next/router';

const RepairerChoiceRegistration: NextPageWithLayout = () => {
  const {tunnelStep, stepOneCompleted} = useContext(
    RepairerRegistrationContext
  );
  const router = useRouter();

  useEffect(() => {
    if (tunnelStep !== 'choice' || !stepOneCompleted) {
/*
      router.push('/reparateur/inscription');
*/
    }
  }, []);
  return (
    <>
      <RegistrationTunnelChoiceWorkshop />
    </>
  );
};

RepairerChoiceRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerChoiceRegistration;
