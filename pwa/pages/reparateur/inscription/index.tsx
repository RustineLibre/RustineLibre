import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import React, {ReactElement} from 'react';

import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import RegistrationTunnelUserInfo from '@components/repairers/registration/RegistrationTunnelUserInfo';

const RepairerRegistration: NextPageWithLayout = () => (
  <RegistrationTunnelUserInfo />
);

RepairerRegistration.getLayout = (page: ReactElement) => (
  <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>
);

export default RepairerRegistration;
