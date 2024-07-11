import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import Box from '@mui/material/Box';
import EmployeeForm from '@components/dashboard/employees/EmployeeForm';
import React from 'react';
import RepairerForm from '@components/dashboard/repairers/RepairerForm';

const AddRepairer = () => {
  return (
    <>
      <Head>
        <title>Ajouter une boutique | Rustine Libre</title>
      </Head>
      <DashboardLayout />
      <Box
        component="main"
        sx={{marginLeft: '20%', marginRight: '5%', marginTop: '100px'}}>
        <RepairerForm repairer={null} />
      </Box>
    </>
  );
};

export default AddRepairer;
