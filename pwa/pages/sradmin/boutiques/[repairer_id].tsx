import React, {useContext} from 'react';
import Head from 'next/head';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {useAccount} from '@contexts/AuthContext';
import DashboardHomeContent from '@components/dashboard/home/DashboardHomeContent';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const Dashboard = () => {
  const {user} = useAccount({});
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Tableau de bord | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Box component="main">
          {user && repairer && (
            <DashboardHomeContent repairer={repairer} currentUser={user} />
          )}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Dashboard;
