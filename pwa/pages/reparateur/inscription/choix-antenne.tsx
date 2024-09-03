import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import React, {ReactElement} from 'react';
import {RegistrationTunnelChoiceWorkshop} from '@components/repairers/registration/RegistrationTunnelChoiceWorkshop';

const RepairerChoiceRegistration: NextPageWithLayout = () => (
  <RegistrationTunnelChoiceWorkshop />
);

RepairerChoiceRegistration.getLayout = (page: ReactElement) => (
  <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>
);

export default RepairerChoiceRegistration;
