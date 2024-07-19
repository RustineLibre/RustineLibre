import React, {useEffect, useState} from 'react';
import {repairerEmployeesResource} from '@resources/repairerEmployeesResource';
import {
  Paper,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  TableContainer,
  CircularProgress,
  Box,
} from '@mui/material';
import EmployeesListActions from '@components/dashboard/employees/EmployeesListActions';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import {RepairerEmployee} from '@interfaces/RepairerEmployee';
import {User} from '@interfaces/User';
import {isBoss} from '@helpers/rolesHelpers';
import {Repairer} from '@interfaces/Repairer';

interface EmployeesListProps {
  currentBoss: User;
  repairers: Repairer[];
  showRepairer: boolean;
}

export const EmployeesList = ({
  currentBoss,
  repairers,
  showRepairer = false,
}: EmployeesListProps): JSX.Element => {
  const [loadingList, setLoadingList] = useState<boolean>(false);
  const [employees, setEmployees] = useState<RepairerEmployee[]>([]);

  const fetchEmployees = async () => {
    if (currentBoss && isBoss(currentBoss)) {
      setLoadingList(true);
      let employees = [];
      for (const repairer of repairers) {
        const response = await repairerEmployeesResource.getAll(true, {
          repairer: repairer['@id'],
        });
        employees.push(...response['hydra:member']);
      }
      setEmployees(employees);
      setLoadingList(false);
    }
  };

  useEffect(() => {
    fetchEmployees();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <>
      {loadingList && (
        <Box display="flex" justifyContent="center" my={2}>
          <CircularProgress />
        </Box>
      )}
      <TableContainer elevation={4} component={Paper}>
        <Table aria-label="employees">
          <TableHead
            sx={{
              '& th': {
                fontWeight: 'bold',
                color: 'primary.main',
              },
            }}>
            <TableRow>
              <TableCell>Nom</TableCell>
              <TableCell align="left">Rôle</TableCell>
              {showRepairer && <TableCell align="left">Boutique</TableCell>}
              <TableCell align="center">Status</TableCell>
              <TableCell align="right">Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {currentBoss && (
              <TableRow
                key={currentBoss.firstName + ' ' + currentBoss.lastName}
                sx={{'&:last-child td, &:last-child th': {border: 0}}}>
                <TableCell component="th" scope="row">
                  {currentBoss.firstName + ' ' + currentBoss.lastName}
                </TableCell>
                <TableCell align="left">Admin</TableCell>
                {showRepairer && (
                  <TableCell align="left">
                    {currentBoss.repairers
                      .map((repairer) => repairer.name)
                      .join(', ')}
                  </TableCell>
                )}
                <TableCell align="center">
                  <CheckCircleIcon color="success" />
                </TableCell>
                <TableCell align="right"></TableCell>
              </TableRow>
            )}
            {employees.map((repairerEmployee) => (
              <TableRow
                key={repairerEmployee.id}
                sx={{'&:last-child td, &:last-child th': {border: 0}}}>
                <TableCell component="th" scope="row">
                  {repairerEmployee.employee.firstName}{' '}
                  {repairerEmployee.employee.lastName}
                </TableCell>
                <TableCell align="left">Réparateur</TableCell>
                {showRepairer && (
                  <TableCell align="left">
                    {repairerEmployee.repairer.name}
                  </TableCell>
                )}
                <TableCell align="center">
                  {repairerEmployee.enabled ? (
                    <CheckCircleIcon color="success" />
                  ) : (
                    <CancelIcon color="error" />
                  )}
                </TableCell>
                <TableCell align="right">
                  <EmployeesListActions
                    employee={repairerEmployee}
                    fetchEmployees={fetchEmployees}
                  />
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
    </>
  );
};

export default EmployeesList;
