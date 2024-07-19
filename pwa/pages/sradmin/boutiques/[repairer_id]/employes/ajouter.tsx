import {NextPageWithLayout} from 'pages/_app';
import React, {useContext} from 'react';
import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import Box from '@mui/material/Box';
import EmployeeForm from '@components/dashboard/employees/EmployeeForm';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const AddEmployee: NextPageWithLayout = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Ajouter un employé | Rustine Libre</title>
      </Head>
      <DashboardLayout />
      <Box
        component="main"
        sx={{marginLeft: '20%', marginRight: '5%', marginTop: '100px'}}>
        {repairer && (
          <EmployeeForm repairerEmployee={null} repairer={repairer} />
        )}
      </Box>
    </>
  );
};

export default AddEmployee;
