import {Appointment} from '@interfaces/Appointment';
import {Collection} from '@interfaces/Resource';
import {appointmentResource} from '@resources/appointmentResource';
import React, {ChangeEvent, useCallback, useEffect, useState} from 'react';
import ConfirmationModal from '@components/common/ConfirmationModal';
import {
  Box,
  Pagination,
  Stack,
  Paper,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  TableContainer,
  CircularProgress,
  TextField,
  IconButton,
  InputAdornment,
  Button,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {formatDate} from '@helpers/dateHelper';
import {downloadFile} from '@utils/downloadFileLink';

export const AppointmentList = (): React.JSX.Element => {
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [loadingList, setLoadingList] = useState<boolean>(false);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [totalPages, setTotalPages] = useState<number>(0);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
  const [removePending, setRemovePending] = useState<boolean>(false);
  const [selectedAppointmentToDelete, setSelectedAppointmentToDelete] =
    useState<Appointment | undefined>(undefined);

  const handleStates = (response: Collection<Appointment>) => {
    setAppointments(response['hydra:member']);
    setTotalPages(Math.ceil(response['hydra:totalItems'] / 30));
    setLoadingList(false);
  };

  const fetchAppointments: () => Promise<Collection<Appointment>> =
    useCallback(async () => {
      setLoadingList(true);

      const params = {
        page: `${currentPage ?? 1}`,
        itemsPerPage: 30,
        'order[id]': 'DESC',
        ...(!!searchTerm && {search: searchTerm, page: 1}),
      };

      return await appointmentResource.getAll(true, params);
    }, [currentPage, searchTerm]);

  useEffect(() => {
    if (searchTerm.length === 0 || searchTerm.length >= 3) {
      fetchAppointments().then(handleStates);
    }
  }, [searchTerm, currentPage, fetchAppointments]);

  const handleSearchTermChange = (e: ChangeEvent<HTMLInputElement>): void => {
    setSearchTerm(e.target.value);
  };

  const handleKeyPress = async (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      await fetchAppointments().then(handleStates);
    }
  };

  const handleCLickOnSearch = async () =>
    await fetchAppointments().then(handleStates);

  const downloadCsv = async () =>
    await appointmentResource
      .exportAppointmentCollectionCsv()
      .then((response: Response) =>
        downloadFile(response, 'appointment_collection')
      );

  const handlePageChange = (event: ChangeEvent<unknown>, page: number) =>
    setCurrentPage(page);

  const handleDeleteClick = (appointment: Appointment) => {
    setDeleteDialogOpen(true);
    setSelectedAppointmentToDelete(appointment);
  };

  const handleDeleteConfirm = async () => {
    if (!selectedAppointmentToDelete) {
      return;
    }

    setRemovePending(true);
    setDeleteDialogOpen(false);

    try {
      await appointmentResource.delete(selectedAppointmentToDelete['@id']);
      setRemovePending(false);
      setSelectedAppointmentToDelete(undefined);
      await fetchAppointments().then(handleStates);
    } catch (e: any) {
      setRemovePending(false);
    }
    setRemovePending(false);
  };

  return (
    <Box>
      <TextField
        label="Chercher..."
        value={searchTerm}
        onChange={handleSearchTermChange}
        onKeyDown={handleKeyPress}
        inputProps={{maxLength: 180}}
        InputProps={{
          endAdornment: (
            <InputAdornment
              position="end"
              onClick={handleCLickOnSearch}
              sx={{cursor: 'pointer'}}>
              <SearchIcon />
            </InputAdornment>
          ),
        }}
      />
      <Button onClick={downloadCsv} variant="contained" sx={{float: 'right'}}>
        Télécharger au format CSV
      </Button>
      <TableContainer elevation={4} component={Paper} sx={{marginTop: '10px'}}>
        <Table aria-label="appointments">
          <TableHead
            sx={{
              '& th': {
                fontWeight: 'bold',
                color: 'primary.main',
              },
            }}>
            <TableRow>
              <TableCell align="left">Nom</TableCell>
              <TableCell align="left">Prénom</TableCell>
              <TableCell align="left">Email</TableCell>
              <TableCell align="left">Date de création du rdv</TableCell>
              <TableCell align="left">Date du rdv</TableCell>
              <TableCell align="left">Prestation</TableCell>
              <TableCell align="left">Enseigne</TableCell>
              <TableCell align="right">Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {loadingList && <CircularProgress sx={{ml: 5, mt: 5}} />}
            {appointments.map((appointment: Appointment) => (
              <TableRow
                key={appointment.id}
                sx={{
                  '&:last-child td, &:last-child th': {border: 0},
                }}>
                <TableCell align="left" component="th" scope="row">
                  {appointment.customer?.lastName}
                </TableCell>
                <TableCell align="left">
                  {appointment.customer?.firstName}
                </TableCell>
                <TableCell align="left">
                  {appointment.customer?.email}
                </TableCell>
                <TableCell align="left">
                  {formatDate(appointment.createdAt)}
                </TableCell>
                <TableCell align="left">
                  {formatDate(appointment.slotTime)}
                </TableCell>
                <TableCell align="left">
                  {appointment.autoDiagnostic?.prestation}
                </TableCell>
                <TableCell align="left">{appointment.repairer.name}</TableCell>
                <TableCell align="right">
                  <IconButton
                    color="secondary"
                    onClick={() => handleDeleteClick(appointment)}>
                    <DeleteForeverIcon />
                  </IconButton>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
      {totalPages > 1 && (
        <Stack spacing={2} sx={{marginTop: '20px'}}>
          <Pagination
            count={totalPages}
            page={currentPage}
            onChange={handlePageChange}
            color="primary"
            showFirstButton
            showLastButton
            size="large"
          />
        </Stack>
      )}
      {selectedAppointmentToDelete && (
        <ConfirmationModal
          open={deleteDialogOpen}
          onClose={() => setDeleteDialogOpen(false)}
          onConfirm={handleDeleteConfirm}
          loading={
            removePending
          }>{`Êtes-vous sûr de vouloir supprimer le rendez vous du "${formatDate(selectedAppointmentToDelete.slotTime)}" ?`}</ConfirmationModal>
      )}
    </Box>
  );
};

export default AppointmentList;
