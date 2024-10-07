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
  useMediaQuery,
} from '@mui/material';
import React, {useState} from 'react';
import Link from 'next/link';
import EditIcon from '@mui/icons-material/Edit';
import DeleteForeverIcon from '@mui/icons-material/DeleteForeverSharp';
import ConfirmationModal from '@components/common/ConfirmationModal';
import {repairerResource} from '@resources/repairerResource';
import RemoveRedEyeIcon from '@mui/icons-material/RemoveRedEye';
import theme from '../../../styles/theme';

interface RepairersListProps {
  repairers: Repairer[];
}
const RepairersList = ({repairers}: RepairersListProps) => {
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
  const [removePending, setRemovePending] = useState<boolean>(false);

  const handleDeleteConfirm = async (repairer: Repairer) => {
    setRemovePending(true);
    try {
      await repairerResource.delete(repairer['@id']);
      setDeleteDialogOpen(false);
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
                    <Box display={isMobile ? 'flex' : 'block'}>
                      <Link
                        href={`/sradmin/boutiques/${repairer.id}`}
                        legacyBehavior
                        passHref>
                        <IconButton
                          color="secondary"
                          size={isMobile ? 'small' : 'medium'}>
                          <RemoveRedEyeIcon />
                        </IconButton>
                      </Link>
                      <Link
                        href={
                          repairer.enabled
                            ? `/sradmin/boutiques/${repairer.id}/edit`
                            : `/sradmin/boutiques/${repairer.id}`
                        }
                        legacyBehavior
                        passHref>
                        <IconButton color="secondary">
                          <EditIcon color="secondary" />
                        </IconButton>
                      </Link>
                      <IconButton
                        onClick={() => setDeleteDialogOpen(true)}
                        color="secondary"
                        size={isMobile ? 'small' : 'medium'}>
                        <DeleteForeverIcon />
                      </IconButton>
                    </Box>
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
