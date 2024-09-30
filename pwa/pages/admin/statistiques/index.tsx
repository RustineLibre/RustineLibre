import AppointmentList from '@components/admin/appointments/AppointmentList';
import Head from 'next/head';
import React from 'react';
import Box from '@mui/material/Box';
import AdminLayout from '@components/admin/AdminLayout';

const Statistics = () => {
  return (
    <>
      <Head>
        <title>Statistiques | Rustine Libre</title>
      </Head>
      <AdminLayout>
        <Box component="main">
          <AppointmentList />
        </Box>
      </AdminLayout>
    </>
  );
};

export default Statistics;
