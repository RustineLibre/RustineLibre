import Head from 'next/head';
import React, {useContext} from 'react';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import CustomersList from '@components/dashboard/customers/CustomersList';
import {Typography} from '@mui/material';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const Customers = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Clients | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Box component="main">
          <Box
            sx={{
              my: 2,
            }}>
            <Typography variant="h5">Liste des clients</Typography>
          </Box>
          {repairer && <CustomersList repairer={repairer} />}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Customers;
