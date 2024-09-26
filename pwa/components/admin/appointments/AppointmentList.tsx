import {Appointment} from '@interfaces/Appointment';
import {Collection} from '@interfaces/Resource';
import {AdapterDayjs} from '@mui/x-date-pickers/AdapterDayjs';
import {DatePicker} from '@mui/x-date-pickers/DatePicker';
import {frFR} from '@mui/x-date-pickers/locales';
import {LocalizationProvider} from '@mui/x-date-pickers/LocalizationProvider';
import {appointmentResource} from '@resources/appointmentResource';
import dayjs from 'dayjs';
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
  Typography,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {formatDate, padNumber} from '@helpers/dateHelper';
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
  const [startSlotTimeFilter, setStartSlotTimeFilter] = useState<
    string | undefined
  >(undefined);
  const [endSlotTimeFilter, setEndSlotTimeFilter] = useState<
    string | undefined
  >(undefined);

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
        'order[slotTime]': 'DESC',
        ...(!!searchTerm && {search: searchTerm, page: 1}),
        ...(!!startSlotTimeFilter && {
          'slotTime[after]': startSlotTimeFilter,
          page: 1,
        }),
        ...(!!endSlotTimeFilter && {
          'slotTime[before]': endSlotTimeFilter,
          page: 1,
        }),
      };

      return await appointmentResource.getAll(true, params);
    }, [currentPage, endSlotTimeFilter, searchTerm, startSlotTimeFilter]);

  useEffect(() => {
    if (searchTerm.length === 0 || searchTerm.length >= 3) {
      fetchAppointments().then(handleStates);
    }
  }, [searchTerm, currentPage, fetchAppointments]);

  const handleSearchTermChange = (e: ChangeEvent<HTMLInputElement>): void => {
    setSearchTerm(e.target.value);
  };

  const handleSearchTermKeyPress = async (
    e: React.KeyboardEvent<HTMLInputElement>
  ) => {
    if (e.key === 'Enter') {
      await fetchAppointments().then(handleStates);
    }
  };

  const handleSearchTermCLickOnSearchIcon = async () =>
    await fetchAppointments().then(handleStates);

  const handleDatePickerFilter = (
    newValue: dayjs.Dayjs | null,
    startSlotTime: boolean = true
  ) => {
    if (newValue && newValue.year() && newValue.month() && newValue.date()) {
      const month = padNumber(newValue.month() + 1);
      const day = padNumber(newValue.date());
      const newDate = `${newValue.year()}-${month}-${day}`;

      if (startSlotTime) {
        if (endSlotTimeFilter && dayjs(startSlotTimeFilter) > dayjs(newDate)) {
          return;
        }

        setStartSlotTimeFilter(newDate);

        return;
      }

      setEndSlotTimeFilter(newDate);
    }

    if (
      !newValue ||
      isNaN(newValue.year()) ||
      isNaN(newValue.month()) ||
      isNaN(newValue.date())
    ) {
      if (startSlotTime) {
        setStartSlotTimeFilter(undefined);

        return;
      }

      setEndSlotTimeFilter(undefined);
    }
  };

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
      {/* Filters */}
      <Box sx={{display: 'flex'}}>
        <Box>
          <TextField
            label="Chercher..."
            value={searchTerm}
            onChange={handleSearchTermChange}
            onKeyDown={handleSearchTermKeyPress}
            inputProps={{maxLength: 180}}
            InputProps={{
              endAdornment: (
                <InputAdornment
                  position="end"
                  onClick={handleSearchTermCLickOnSearchIcon}
                  sx={{cursor: 'pointer'}}>
                  <SearchIcon />
                </InputAdornment>
              ),
            }}
          />
        </Box>
        <Box sx={{marginLeft: 'auto'}}>
          <LocalizationProvider
            localeText={
              frFR.components.MuiLocalizationProvider.defaultProps.localeText
            }
            adapterLocale="fr"
            dateAdapter={AdapterDayjs}>
            <Box
              sx={{
                display: 'flex',
              }}>
              <Box
                sx={{
                  display: 'flex',
                  flexDirection: 'row-reverse',
                  marginRight: '3.5rem',
                  gap: 1,
                }}>
                <DatePicker
                  minDate={dayjs(startSlotTimeFilter)}
                  slotProps={{
                    textField: {
                      size: 'small',
                    },
                  }}
                  label="Date de fin"
                  format="DD-MM-YYYY"
                  onChange={(newValue) =>
                    handleDatePickerFilter(newValue, false)
                  }
                />
                <Box component="span" sx={{alignSelf: 'center'}}>
                  -
                </Box>
                <DatePicker
                  {...(!!endSlotTimeFilter && {
                    maxDate: dayjs(endSlotTimeFilter),
                  })}
                  slotProps={{textField: {size: 'small'}}}
                  label="Date de début"
                  sx={{width: '40%'}}
                  format="DD-MM-YYYY"
                  onChange={(newValue) => handleDatePickerFilter(newValue)}
                />
              </Box>
              <Button onClick={downloadCsv} variant="contained">
                Télécharger au format CSV
              </Button>
            </Box>
          </LocalizationProvider>
        </Box>
      </Box>

      {/* Table */}
      <TableContainer elevation={2} component={Paper} sx={{marginTop: '2rem'}}>
        <Table aria-label="appointments">
          <TableHead
            sx={{
              '& th': {
                fontWeight: 'bold',
                color: 'primary.main',
              },
            }}>
            <TableRow>
              <TableCell align="left">Utilisateur</TableCell>
              <TableCell align="left" width="12%">
                Inscription
              </TableCell>
              <TableCell align="left" width="12%">
                Création du rdv
              </TableCell>
              <TableCell align="left" width="12%">
                Rdv
              </TableCell>
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
                  {appointment.customer?.lastName}{' '}
                  {appointment.customer?.firstName}
                  <br />
                  <Typography sx={{color: 'grey'}}>
                    {appointment.customer?.email}
                  </Typography>
                </TableCell>
                <TableCell align="left">
                  {formatDate(appointment.customer?.createdAt ?? undefined)}
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
