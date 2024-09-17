import {ENTRYPOINT} from '@config/entrypoint';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import React, {ReactElement} from 'react';
import {RegistrationTunnelWorkshop} from '@components/repairers/registration/RegistrationTunnelWorkshop';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';
import {GetStaticProps} from 'next';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {repairerTypeResource} from '@resources/repairerTypeResource';

type RepairerRegistrationProps = {
  bikeTypesFetched: BikeType[];
  repairerTypesFetched: RepairerType[];
};
const RepairerWorkshopRegistration: NextPageWithLayout<
  RepairerRegistrationProps
> = ({bikeTypesFetched = [], repairerTypesFetched = []}) => (
  <RegistrationTunnelWorkshop
    bikeTypesFetched={bikeTypesFetched}
    repairerTypesFetched={repairerTypesFetched}
  />
);

export const getStaticProps: GetStaticProps = async () => {
  console.log('RepairerWorkshopRegistration => getStaticProps');
  console.log(ENTRYPOINT);
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

RepairerWorkshopRegistration.getLayout = (page: ReactElement) => (
  <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>
);

export default RepairerWorkshopRegistration;
