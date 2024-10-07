import React, {useContext} from 'react';
import Head from 'next/head';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {useAccount} from '@contexts/AuthContext';
import DashboardHomeContent from '@components/dashboard/home/DashboardHomeContent';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';
import {Typography} from '@mui/material';

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
          {user && repairer && repairer.enabled && (
            <DashboardHomeContent repairer={repairer} currentUser={user} />
          )}
          {repairer && !repairer.enabled && (
            <Box
              display={'flex'}
              justifyContent={'center'}
              flexDirection={'column'}
              alignItems={'center'}>
              <Typography
                variant={'h3'}
                textAlign={'center'}
                fontWeight={'bold'}
                width={'50%'}
                marginTop={5}
                color={'primary.main'}>
                Cette boutique est en attente de validation.
              </Typography>
              <Typography textAlign={'center'} width={'50%'} marginTop={3}>
                Votre demande a bien été reçue. Elle est en cours de traitement
                par l&apos;équipe Rustine&nbsp;Libre. Dès validation, vous serez
                tenu informé.
              </Typography>
            </Box>
          )}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Dashboard;
