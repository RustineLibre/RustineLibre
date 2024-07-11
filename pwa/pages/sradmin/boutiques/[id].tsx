import React, {useEffect, useState} from 'react';
import Head from 'next/head';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {useAccount} from '@contexts/AuthContext';
import DashboardHomeContent from '@components/dashboard/home/DashboardHomeContent';
import {Repairer} from '@interfaces/Repairer';
import {isBoss, isEmployee} from '@helpers/rolesHelpers';
import {useRouter} from 'next/router';
import Error404 from '../../404';

const Dashboard = () => {
  const router = useRouter();
  const {id} = router.query;
  const {user} = useAccount({});
  const [repairer, setRepairer] = useState<Repairer | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    const repairerForBoss = user?.repairers.find(
      (repairer) => repairer.id == id
    );

    if (user && isBoss(user) && repairerForBoss) {
      setRepairer(repairerForBoss);
      setIsLoading(false);
      return;
    }

    if (
      user &&
      isEmployee(user) &&
      user.repairerEmployee &&
      user.repairerEmployee.repairer.id === id
    ) {
      setRepairer(user.repairerEmployee.repairer);
      setIsLoading(false);
    }
  }, [user]); // eslint-disable-line react-hooks/exhaustive-deps

  if (!isLoading && !repairer) {
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
