import Head from 'next/head';
import React from 'react';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {useAccount} from '@contexts/AuthContext';
import EmployeesList from '@components/dashboard/employees/EmployeesList';
import Link from 'next/link';
import Button from '@mui/material/Button';
import AddIcon from '@mui/icons-material/Add';
import {isBoss} from '@helpers/rolesHelpers';
import {Typography} from '@mui/material';
import RepairersList from '@components/dashboard/repairers/RepairersList';

const Repairers = () => {
  const {user} = useAccount({redirectIfNotFound: '/'});

  return (
    <>
      <Head>
        <title>Mes boutiques | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Box component="main">
          <Box
            sx={{
              display: 'flex',
              flexDirection: 'row',
              justifyContent: 'space-between',
              alignItems: 'center',
              my: 2,
            }}>
            <Typography variant="h5">Liste des boutiques</Typography>
            <Link href="/sradmin/boutiques/ajouter" legacyBehavior passHref>
              <Button variant="contained" size="small" startIcon={<AddIcon />}>
                Ajouter une boutique
              </Button>
            </Link>
          </Box>
          {user && isBoss(user) && <RepairersList repairers={user.repairers} />}
        </Box>

        <Box component="main">
          <Box
            sx={{
              display: 'flex',
              flexDirection: 'row',
              justifyContent: 'space-between',
              alighItems: 'center',
              my: 2,
            }}>
            <Typography variant="h5">Liste des employés</Typography>
            <Link href="/sradmin/boutiques/employes/ajouter" legacyBehavior passHref>
              <Button variant="contained" size="small" startIcon={<AddIcon />}>
                Ajouter un employé
              </Button>
            </Link>
          </Box>
          {user && isBoss(user) && (
            <EmployeesList
              currentBoss={user}
              repairers={user.repairers}
              showRepairer={true}
            />
          )}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Repairers;
