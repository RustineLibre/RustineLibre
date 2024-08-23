import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import Box from '@mui/material/Box';
import React, {useContext} from 'react';
import RepairerForm from '@components/dashboard/repairers/RepairerForm';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const AddRepairer = () => {
  const {repairer} = useContext(DashboardRepairerContext);
  return (
    <>
      <Head>
        <title>Modifier une boutique | Rustine Libre</title>
      </Head>
      <DashboardLayout />
      <Box
        component="main"
        sx={{marginLeft: '20%', marginRight: '5%', marginTop: '100px'}}>
        <RepairerForm repairer={repairer} />
      </Box>
    </>
  );
};

export default AddRepairer;
