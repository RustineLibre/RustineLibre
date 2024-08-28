import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import {RegistrationTunnelValidation} from '@components/repairers/registration/RegistrationTunnelValidation';
import React, {ReactElement, useContext} from 'react';
import WebsiteLayout from '@components/layout/WebsiteLayout';
import {RegistrationTunnelWorkshop} from '@components/repairers/registration/RegistrationTunnelWorkshop';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {GetStaticProps} from 'next';
import {ENTRYPOINT} from '@config/entrypoint';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import RepairerChoiceRegistration from './choix-antenne';

type RepairerRegistrationProps = {
  bikeTypesFetched: BikeType[];
  repairerTypesFetched: RepairerType[];
};
const RepairerWorkshopRegistration: NextPageWithLayout<
  RepairerRegistrationProps
> = ({bikeTypesFetched = [], repairerTypesFetched = []}) => {
  return (
    <>
      <RegistrationTunnelWorkshop
        bikeTypes={bikeTypesFetched}
        repairerTypes={repairerTypesFetched}
      />
    </>
  );
};

export const getStaticProps: GetStaticProps = async () => {
  const bikeTypesCollection = await bikeTypeResource.getAll(false);
  const bikeTypesFetched = bikeTypesCollection['hydra:member'];

  const repairerTypesCollection = await repairerTypeResource.getAll(false);
  const repairerTypesFetched = repairerTypesCollection['hydra:member'];

  return {
    props: {
      bikeTypesFetched,
      repairerTypesFetched,
    },
    revalidate: 10,
  };
};

RepairerWorkshopRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerWorkshopRegistration;
