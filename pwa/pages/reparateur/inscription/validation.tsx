import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import {RegistrationTunnelValidation} from '@components/repairers/registration/RegistrationTunnelValidation';
import React, {ReactElement} from 'react';

const RepairerValidationRegistration: NextPageWithLayout = () => (
  <RegistrationTunnelValidation />
);

RepairerValidationRegistration.getLayout = (page: ReactElement) => (
  <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>
);

export default RepairerValidationRegistration;
