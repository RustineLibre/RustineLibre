import React, {useContext} from 'react';
import Head from 'next/head';
import {Box} from '@mui/material';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {NextPageWithLayout} from '@pages/_app';
import AgendaCalendar from '@components/dashboard/agenda/AgendaCalendar';
import Error404 from '../../../../404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const Agenda: NextPageWithLayout = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Agenda | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Box component="main" maxWidth="1200">
          {repairer && <AgendaCalendar repairer={repairer} />}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Agenda;
