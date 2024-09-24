import {Appointment} from '@interfaces/Appointment';
import {Collection} from '@interfaces/Resource';
import {appointmentResource} from '@resources/appointmentResource';
import React, {
  ChangeEvent,
  MouseEventHandler,
  useCallback,
  useEffect,
  useState,
} from 'react';
import {repairerResource} from '@resources/repairerResource';
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
  Typography,
  IconButton,
  InputAdornment,
  Switch,
  Button,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import EditIcon from '@mui/icons-material/Edit';
import CircleIcon from '@mui/icons-material/Circle';
import DeleteForeverIcon from '@mui/icons-material/DeleteForever';
import {formatDate} from '@helpers/dateHelper';
import {Repairer} from '@interfaces/Repairer';
import {downloadFile} from '@utils/downloadFileLink';

export const AppointmentList = (): React.JSX.Element => {
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [loadingList, setLoadingList] = useState<boolean>(false);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [totalPages, setTotalPages] = useState<number>(0);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
  const [removePending, setRemovePending] = useState<boolean>(false);
  const [selectedRepairerToDelete, setSelectedRepairerToDelete] =
    useState<Repairer | null>(null);

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
        ...('' !== searchTerm && {search: searchTerm, page: 1}),
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

  const handleKeyPress = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      fetchAppointments().then(handleStates);
    }
  };

  const handleCLickOnSearch = () => fetchAppointments().then(handleStates);

  const downloadCsv = async () =>
    await appointmentResource
      .exportAppointmentCollectionCsv()
      .then((response: Response) =>
        downloadFile(response, 'appointment_collection')
      );

  const handleDeleteClick = (repairer: Repairer) => {
    setDeleteDialogOpen(true);
    setSelectedRepairerToDelete(repairer);
  };

  const handleDeleteConfirm = async () => {
    if (!selectedRepairerToDelete) {
      return;
    }
    setRemovePending(true);
    setDeleteDialogOpen(false);
    try {
      await repairerResource.delete(selectedRepairerToDelete['@id']);
      setRemovePending(false);
      setSelectedRepairerToDelete(null);
      // await fetchRepairers();
    } catch (e: any) {
      setRemovePending(false);
    }
    setRemovePending(false);
  };

  const handlePageChange = (event: ChangeEvent<unknown>, page: number) => {
    setCurrentPage(page);
  };

  const handleSwitchChange = async (event: ChangeEvent, repairer: Repairer) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    await repairerResource.put(repairer['@id'], {
      enabled: isChecked,
    });
    // const updatedRepairerList = repairers.map((r: Repairer) => {
    //   if (r.id === repairer.id) {
    //     return {...r, enabled: isChecked};
    //   }
    //   return r;
    // });
    // setRepairers(updatedRepairerList);
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
        <Table aria-label="employees">
          <TableHead
            sx={{
              '& th': {
                fontWeight: 'bold',
                color: 'primary.main',
              },
            }}>
            <TableRow>
              <TableCell align="left">Nom</TableCell>
              <TableCell align="center">Statut</TableCell>
              <TableCell align="center">Activé</TableCell>
              <TableCell align="center">Dernière connexion</TableCell>
              <TableCell align="right">Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {/*{loadingList && <CircularProgress sx={{ml: 5, mt: 5}} />}*/}
            {/*{repairers.map((repairer) => (*/}
            {/*  <TableRow*/}
            {/*    key={repairer.id}*/}
            {/*    sx={{*/}
            {/*      '&:last-child td, &:last-child th': {border: 0},*/}
            {/*    }}>*/}
            {/*    <TableCell align="left" component="th" scope="row">*/}
            {/*      {repairer.name}*/}
            {/*      <br />*/}
            {/*      {repairer.owner && (*/}
            {/*        <Typography sx={{color: 'grey'}}>*/}
            {/*          {repairer.owner.email}*/}
            {/*        </Typography>*/}
            {/*      )}*/}
            {/*    </TableCell>*/}
            {/*    <TableCell align="center">*/}
            {/*      {repairer.enabled && (*/}
            {/*        <span*/}
            {/*          style={{*/}
            {/*            color: '#027f00',*/}
            {/*            backgroundColor: '#cdffcd',*/}
            {/*            padding: '10px',*/}
            {/*            borderRadius: '10px',*/}
            {/*          }}>*/}
            {/*          <CircleIcon sx={{fontSize: '0.8em'}} /> Actif*/}
            {/*        </span>*/}
            {/*      )}*/}
            {/*      {!repairer.enabled && (*/}
            {/*        <span*/}
            {/*          style={{*/}
            {/*            color: '#a36f1a',*/}
            {/*            backgroundColor: '#ffeccb',*/}
            {/*            padding: '10px',*/}
            {/*            borderRadius: '10px',*/}
            {/*          }}>*/}
            {/*          <CircleIcon sx={{fontSize: '0.8em'}} /> En attente*/}
            {/*        </span>*/}
            {/*      )}*/}
            {/*    </TableCell>*/}
            {/*    <TableCell align="center">*/}
            {/*      <Switch*/}
            {/*        checked={repairer.enabled}*/}
            {/*        onChange={(event) => handleSwitchChange(event, repairer)}*/}
            {/*        inputProps={{'aria-label': 'controlled'}}*/}
            {/*      />*/}
            {/*    </TableCell>*/}
            {/*    <TableCell align="center">*/}
            {/*      {repairer.owner.lastConnect &&*/}
            {/*        formatDate(repairer.owner.lastConnect)}*/}
            {/*    </TableCell>*/}
            {/*    <TableCell align="right">*/}
            {/*      <Link*/}
            {/*        href={`/admin/reparateurs/edit/${repairer.id}`}*/}
            {/*        legacyBehavior*/}
            {/*        passHref>*/}
            {/*        <IconButton color="secondary">*/}
            {/*          <EditIcon color="secondary" />*/}
            {/*        </IconButton>*/}
            {/*      </Link>*/}
            {/*      <IconButton*/}
            {/*        color="secondary"*/}
            {/*        onClick={() => handleDeleteClick(repairer)}>*/}
            {/*        <DeleteForeverIcon />*/}
            {/*      </IconButton>*/}
            {/*    </TableCell>*/}
            {/*  </TableRow>*/}
            {/*))}*/}
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
      {selectedRepairerToDelete && (
        <ConfirmationModal
          open={deleteDialogOpen}
          onClose={() => setDeleteDialogOpen(false)}
          onConfirm={handleDeleteConfirm}
          loading={
            removePending
          }>{`Êtes-vous sûr de vouloir supprimer le réparateur "${selectedRepairerToDelete.name}" ?`}</ConfirmationModal>
      )}
    </Box>
  );
};

export default AppointmentList;
