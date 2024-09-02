import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import RepairerRegistrationLayout from '@components/layout/RepairerRegistrationLayout';
import React, {ReactElement, useEffect, useState} from 'react';
import {RegistrationTunnelWorkshop} from '@components/repairers/registration/RegistrationTunnelWorkshop';
import {GetStaticProps} from 'next';
import {bikeTypeResource} from '@resources/bikeTypeResource';
import {repairerTypeResource} from '@resources/repairerTypeResource';
import {BikeType} from '@interfaces/BikeType';
import {RepairerType} from '@interfaces/RepairerType';

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
        bikeTypesFetched={bikeTypesFetched}
        repairerTypesFetched={repairerTypesFetched}
      />
    </>
  );
};

/*export const getStaticProps: GetStaticProps = async () => {
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
};*/

RepairerWorkshopRegistration.getLayout = (page: ReactElement) => {
  // here the best is to create a RepairerRegistrationLayout and put the context and the text "Devenir réparateur" in it
  // this way, the context and the text will be shared with all steps pages
  return <RepairerRegistrationLayout>{page}</RepairerRegistrationLayout>;
};

export default RepairerWorkshopRegistration;
