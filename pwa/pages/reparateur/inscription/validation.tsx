import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import Head from 'next/head';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import {RegistrationTunnelValidation} from '@components/repairers/registration/RegistrationTunnelValidation';
import React, {ReactElement, useContext} from 'react';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {useRouter} from 'next/router';

const RepairerValidationRegistration: NextPageWithLayout = () => {
  return <RegistrationTunnelValidation />;
};

RepairerValidationRegistration.getLayout = (page: ReactElement) => {
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerValidationRegistration;
