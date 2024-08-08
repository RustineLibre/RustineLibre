import Head from 'next/head';
import React, {useContext} from 'react';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {useAccount} from '@contexts/AuthContext';
import EmployeesList from '@components/dashboard/employees/EmployeesList';
import Link from 'next/link';
import Button from '@mui/material/Button';
import AddIcon from '@mui/icons-material/Add';
import {isBoss} from '@helpers/rolesHelpers';
import {Typography} from '@mui/material';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const Employees = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);
  const {user} = useAccount({});

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Employés | Rustine Libre</title>
      </Head>
      <DashboardLayout>
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
            {repairer && (
              <Link
                href={`/sradmin/boutiques/${repairer.id}/employes/ajouter`}
                legacyBehavior
                passHref>
                <Button
                  variant="contained"
                  size="small"
                  startIcon={<AddIcon />}>
                  Ajouter un employé
                </Button>
              </Link>
            )}
          </Box>
          {user && isBoss(user) && repairer && (
            <EmployeesList
              currentBoss={user}
              repairers={[repairer]}
              showRepairer={false}
            />
          )}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Employees;
