import React from 'react';
import {Box, Typography, useMediaQuery} from '@mui/material';
import EmployeesList from '@components/dashboard/employees/EmployeesList';
import {User} from '@interfaces/User';
import Button from '@mui/material/Button';
import AddIcon from '@mui/icons-material/Add';
import Link from 'next/link';
import theme from 'styles/theme';
import {Repairer} from '@interfaces/Repairer';

interface DashboardHomeEmployeesProps {
  currentBoss: User;
  repairer: Repairer;
}

export const DashboardHomeEmployees = ({
  currentBoss,
  repairer,
}: DashboardHomeEmployeesProps): JSX.Element => {
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  return (
    <Box display="flex" flexDirection="column" gap={2}>
      <Typography variant="h5">
        Utilisateurs
        <Link
          legacyBehavior
          passHref
          href={`/sradmin/boutiques/${repairer.id}/employes/ajouter`}>
          <Button
            variant="contained"
            color="secondary"
            sx={{float: 'right'}}
            size="small"
            startIcon={<AddIcon />}>
            {isMobile ? 'Ajouter' : 'Ajouter un employé'}
          </Button>
        </Link>
      </Typography>
      <EmployeesList
        currentBoss={currentBoss}
        repairers={[repairer]}
        showRepairer={false}
      />
    </Box>
  );
};

export default DashboardHomeEmployees;
