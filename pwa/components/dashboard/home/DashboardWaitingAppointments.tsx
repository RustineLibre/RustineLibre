import React, {useEffect, useState} from 'react';
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
  Typography,
  IconButton,
  Link,
  useMediaQuery,
} from '@mui/material';
import {Appointment} from '@interfaces/Appointment';
import {appointmentResource} from '@resources/appointmentResource';
import {
  getDateFromDateAsString,
  getTimeFromDateAsString,
} from '@helpers/dateHelper';
import SearchIcon from '@mui/icons-material/Search';
import ModalShowAppointment from '@components/dashboard/agenda/ModalShowAppointment';
import CheckCircleIcon from '@mui/icons-material/CheckCircleOutline';
import {Repairer} from '@interfaces/Repairer';
import {getAutodiagBikeName} from '@helpers/appointmentStatus';
import theme from 'styles/theme';
import DirectionsBikeIcon from '@mui/icons-material/DirectionsBike';
import HomeIcon from '@mui/icons-material/Home';
import {isRepairerItinerant} from '@helpers/rolesHelpers';

interface DashboardWaitingAppointmentsProps {
  repairer: Repairer;
  appointmentsWaiting: Appointment[];
  fetchWaitingAppointments: () => Promise<void>;
  fetchNextAppointments: () => Promise<void>;
  loadingListWait: boolean;
}

export const DashboardWaitingAppointments = ({
  repairer,
  appointmentsWaiting,
  fetchWaitingAppointments,
  fetchNextAppointments,
  loadingListWait,
}: DashboardWaitingAppointmentsProps): JSX.Element => {
  const [appointmentSelected, setAppointmentSelected] =
    useState<Appointment | null>(null);
  const [openModal, setOpenModal] = useState<boolean>(false);
  const [updateAppointmentId, setUpdateAppointmentId] = useState<string | null>(
    null
  );

  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  const handleCloseModal = (refresh = true): void => {
    setOpenModal(false);
    if (refresh) {
      refreshLists();
    }
  };

  const refreshLists = async (): Promise<void> => {
    await fetchNextAppointments();
    await fetchWaitingAppointments();
  };

  useEffect(() => {
    fetchWaitingAppointments();
  }, [repairer]); // eslint-disable-line react-hooks/exhaustive-deps

  const getAppointmentName = (appointment: Appointment): string => {
    if (appointment.autoDiagnostic) {
      return appointment.autoDiagnostic.prestation;
    }

    return `${
      appointment.customer
        ? `${appointment.customer.firstName} ${appointment.customer.lastName}`
        : (appointment.customerName ?? 'Nom inconnu')
    }`;
  };

  const handleShowAppointment = (appointment: Appointment) => {
    setOpenModal(true);
    setAppointmentSelected(appointment);
  };

  const handleValidAppointment = async (appointment: Appointment) => {
    setUpdateAppointmentId(appointment.id);
    await appointmentResource.updateAppointmentStatus(appointment.id, {
      transition: 'validated_by_repairer',
    });

    await refreshLists();
    setUpdateAppointmentId(null);
  };

  return (
    <Box>
      <Typography variant="h5">Rendez-vous en attente</Typography>
      {loadingListWait && <CircularProgress sx={{ml: 10, mt: 10}} />}
      {!loadingListWait &&
        (appointmentsWaiting.length > 0 ? (
          <TableContainer elevation={4} component={Paper} sx={{mt: 3}}>
            <Table aria-label="rdv">
              <TableHead
                sx={{
                  '& th': {
                    fontWeight: 'bold',
                    color: 'primary.main',
                  },
                }}>
                <TableRow>
                  {repairer.repairerTypes.length > 1 &&
                    isRepairerItinerant(repairer) && (
                      <TableCell align="left">Lieu</TableCell>
                    )}
                  <TableCell align="left">Nom</TableCell>
                  <TableCell align="right">Date</TableCell>
                  <TableCell align="center">Actions</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {appointmentsWaiting.slice(0, 6).map((appointment) => (
                  <TableRow
                    key={appointment.id}
                    sx={{'&:last-child td, &:last-child th': {border: 0}}}>
                    {repairer.repairerTypes.length > 1 &&
                      isRepairerItinerant(repairer) && (
                        <TableCell component="th" scope="row">
                          {appointment.address ? (
                            <DirectionsBikeIcon color="secondary" />
                          ) : (
                            <HomeIcon color="secondary" />
                          )}
                        </TableCell>
                      )}
                    <TableCell component="th" scope="row">
                      {appointment.customer
                        ? `${appointment.customer.firstName} ${appointment.customer.lastName}`
                        : (appointment.customerName ?? 'Nom inconnu')}
                      <br />
                      <Typography
                        variant="body1"
                        sx={{fontSize: '0.9em', color: 'grey'}}>
                        {getAutodiagBikeName(appointment)}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      {getDateFromDateAsString(appointment.slotTime)}
                      <br />
                      <Typography
                        variant="body1"
                        sx={{fontSize: '0.9em', color: 'green'}}>
                        {getTimeFromDateAsString(appointment.slotTime)}
                      </Typography>
                    </TableCell>
                    <TableCell align="center">
                      <IconButton
                        color="secondary"
                        disabled={!!updateAppointmentId}
                        onClick={() => handleValidAppointment(appointment)}>
                        {updateAppointmentId === appointment.id ? (
                          <CircularProgress size={18} color="secondary" />
                        ) : (
                          <CheckCircleIcon />
                        )}
                      </IconButton>

                      <IconButton
                        disabled={!!updateAppointmentId}
                        color="secondary"
                        onClick={() => handleShowAppointment(appointment)}>
                        <SearchIcon />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))}
                <TableRow
                  key="see_more_waiting_appointment"
                  sx={{'&:last-child td, &:last-child th': {border: 0}}}>
                  <TableCell
                    component="th"
                    scope="row"
                    colSpan={3}
                    sx={{textAlign: 'center'}}>
                    <Link href={`/sradmin/boutiques/${repairer.id}/agenda`}>
                      Voir tout
                    </Link>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </TableContainer>
        ) : (
          <Typography
            variant="body2"
            sx={{py: 3}}
            color="text.secondary"
            width={isMobile ? '70%' : '100%'}>
            Vous n&apos;avez aucun rendez-vous en attente
          </Typography>
        ))}

      {appointmentSelected && (
        <ModalShowAppointment
          appointment={appointmentSelected}
          openModal={openModal}
          handleCloseModal={handleCloseModal}
        />
      )}
    </Box>
  );
};

export default DashboardWaitingAppointments;
