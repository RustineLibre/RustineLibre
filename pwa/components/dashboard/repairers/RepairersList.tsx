import {User} from '@interfaces/User';
import {Repairer} from '@interfaces/Repairer';
import {
  Box,
  CircularProgress,
  IconButton,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@mui/material';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import EmployeesListActions from '@components/dashboard/employees/EmployeesListActions';
import React, {useState} from 'react';
import Link from 'next/link';
import EditIcon from '@mui/icons-material/Edit';
import DeleteForeverIcon from '@mui/icons-material/DeleteForeverSharp';
import ConfirmationModal from '@components/common/ConfirmationModal';
import {repairerEmployeesResource} from '@resources/repairerEmployeesResource';
import {repairerResource} from '@resources/repairerResource';

interface RepairersListProps {
  repairers: Repairer[];
}
const RepairersList = ({repairers}: RepairersListProps) => {
  const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
  const [removePending, setRemovePending] = useState<boolean>(false);

  const handleDeleteConfirm = async (repairer: Repairer) => {
    setRemovePending(true);
    try {
      await repairerResource.delete(repairer['@id']);
      setDeleteDialogOpen(false);
      // await fetchEmployees();
    } catch (e) {}
    setRemovePending(false);
  };

  return (
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
            <TableCell align="right">Actions</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {repairers.map((repairer) => (
            <TableRow
              key={repairer.id}
              sx={{'&:last-child td, &:last-child th': {border: 0}}}>
              <TableCell component="th" scope="row">
                {repairer.name}
              </TableCell>
              <TableCell align="right">
                {removePending ? (
                  <CircularProgress />
                ) : (
                  <>
                    <Link
                      href={`/sradmin/mes-boutiques/edit/${repairer.id}`}
                      legacyBehavior
                      passHref>
                      <IconButton color="secondary">
                        <EditIcon color="secondary" />
                      </IconButton>
                    </Link>
                    <IconButton
                      onClick={() => setDeleteDialogOpen(true)}
                      color="secondary">
                      <DeleteForeverIcon />
                    </IconButton>
                  </>
                )}
                <ConfirmationModal
                  open={deleteDialogOpen}
                  onClose={() => setDeleteDialogOpen(false)}
                  onConfirm={() => handleDeleteConfirm(repairer)}>
                  {`Êtes-vous sûr de vouloir supprimer la boutique "${repairer.name}" ?`}
                </ConfirmationModal>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default RepairersList;