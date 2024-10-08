import React, {useContext} from 'react';
import Head from 'next/head';
import {Box, Tabs, Tab} from '@mui/material';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import OpeningHours from '@components/dashboard/agenda/OpeningHours';
import ExceptionalClosure from '@components/dashboard/agenda/ExceptionalClosure';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import GoogleAgendaLogin from '@components/dashboard/agenda/GoogleAgendaLogin';

const AgendaParameters: NextPageWithLayout = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);
  const [tabValue, setTabValue] = React.useState<number>(0);

  if (repairerNotFound) {
    return <Error404 />;
  }

  const handleChangeTab = (event: React.SyntheticEvent, newValue: number) => {
    setTabValue(newValue);
  };

  return (
    <>
      <Head>
        <title>Paramètres agenda | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Box component="main" maxWidth="1200">
          <Tabs value={tabValue} onChange={handleChangeTab}>
            <Tab label="Jours et plages horaires" />
            <Tab label="Fermetures exceptionnelles" />
            <Tab label="Connexion Google Agenda" />
          </Tabs>

          <Box sx={{marginTop: 3}}>
            {repairer && tabValue === 0 && <OpeningHours repairer={repairer} />}
            {repairer && tabValue === 1 && (
              <ExceptionalClosure repairer={repairer} />
            )}
            {repairer && tabValue === 2 && (
              <GoogleAgendaLogin repairer={repairer} />
            )}
          </Box>
        </Box>
      </DashboardLayout>
    </>
  );
};

export default AgendaParameters;
