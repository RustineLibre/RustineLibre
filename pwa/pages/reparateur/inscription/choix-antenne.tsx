import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import React, {ReactElement, useContext, useEffect} from 'react';
import {RegistrationTunnelChoiceWorkshop} from '@components/repairers/registration/RegistrationTunnelChoiceWorkshop';

const RepairerChoiceRegistration: NextPageWithLayout = () => {
  return (
    <>
      <RegistrationTunnelChoiceWorkshop />
    </>
  );
};

RepairerChoiceRegistration.getLayout = (page: ReactElement) => {
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerChoiceRegistration;
