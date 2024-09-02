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
  const [bikeTypes, setBikeTypes] = useState<BikeType[]>(bikeTypesFetched);
  const [repairerTypes, setRepairerTypes] =
    useState<RepairerType[]>(repairerTypesFetched);
  const fetchRepairerTypes = async () => {
    const responseRepairerTypes = await repairerTypeResource.getAll(false);
    setRepairerTypes(responseRepairerTypes['hydra:member']);
  };

  useEffect(() => {
    if (repairerTypes.length === 0) {
      fetchRepairerTypes();
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const fetchBikeTypes = async () => {
    const responseBikeTypes = await bikeTypeResource.getAll(false);
    setBikeTypes(responseBikeTypes['hydra:member']);
  };

  useEffect(() => {
    if (bikeTypes.length === 0) {
      fetchBikeTypes();
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps
  return (
    <>
      <RegistrationTunnelWorkshop
        bikeTypes={bikeTypesFetched}
        repairerTypes={repairerTypesFetched}
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
