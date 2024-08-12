import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import InformationsContainer from '@components/dashboard/informations/InformationsContainer';
import React, {useContext} from 'react';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';

const RepairerInformations: NextPageWithLayout = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Informations | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        {repairer && <InformationsContainer editRepairer={repairer} />}
      </DashboardLayout>
    </>
  );
};

export default RepairerInformations;
