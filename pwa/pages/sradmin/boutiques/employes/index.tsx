import Head from 'next/head';
import {useRouter} from 'next/router';
import React, {useEffect} from 'react';
import Box from '@mui/material/Box';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {useAccount} from '@contexts/AuthContext';
import EmployeesList from '@components/dashboard/employees/EmployeesList';
import Link from 'next/link';
import Button from '@mui/material/Button';
import AddIcon from '@mui/icons-material/Add';
import {isBoss, isEmployee} from '@helpers/rolesHelpers';
import {Typography} from '@mui/material';

const Employees = () => {
  const {user} = useAccount({redirectIfNotFound: '/'});
  const router = useRouter();

  useEffect(() => {
    if (
      user &&
      isEmployee(user) &&
      user.repairerEmployee &&
      user.repairerEmployee.repairer
    ) {
      router.push(`/sradmin/boutiques/${user.repairerEmployee.repairer.id}`);

      return;
    }

    if (user && !isBoss(user)) {
      router.push('/sradmin/boutiques');
    }
  }, [router, user]);

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
            {user && isBoss(user) && (
              <Link
                href={`/sradmin/boutiques/employes/ajouter`}
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

export default Employees;
