import React from 'react';
import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import Box from '@mui/material/Box';
import EmployeeForm from '@components/dashboard/employees/EmployeeForm';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';

const AddEmployee: NextPageWithLayout = () => {
  return (
    <>
      <Head>
        <title>Ajouter un employé | Rustine Libre</title>
      </Head>
      <DashboardLayout />
      <Box
        component="main"
        sx={{marginLeft: '20%', marginRight: '5%', marginTop: '100px'}}>
        <EmployeeForm repairerEmployee={null} repairer={null} />
      </Box>
    </>
  );
};

export default AddEmployee;
