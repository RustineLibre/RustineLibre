import React, {useCallback, useEffect, useState} from 'react';
import {Box, Typography} from '@mui/material';
import Grid from '@mui/material/Grid';
import DashboardNextAppointments from '@components/dashboard/home/DashboardNextAppointments';
import DashboardWaitingAppointments from '@components/dashboard/home/DashboardWaitingAppointments';
import {appointmentResource} from '@resources/appointmentResource';
import {Appointment} from '@interfaces/Appointment';
import {RequestParams} from '@interfaces/Resource';
import {dateObjectAsString} from '@helpers/dateHelper';
import DashboardHomeEmployees from '@components/dashboard/home/DashboardHomeEmployees';
import {User} from '@interfaces/User';
import {Repairer} from '@interfaces/Repairer';
import {isBoss} from '@helpers/rolesHelpers';

interface DashboardHomeContentProps {
  repairer: Repairer;
  currentUser: User;
}

export const DashboardHomeContent = ({
  repairer,
  currentUser,
}: DashboardHomeContentProps): JSX.Element => {
  const [loadingListNext, setLoadingListNext] = useState<boolean>(false);
  const [loadingListWait, setLoadingListWait] = useState<boolean>(false);
  const [appointmentsNext, setAppointmentsNext] = useState<Appointment[]>([]);
  const [appointmentsWaiting, setAppointmentsWaiting] = useState<Appointment[]>(
    []
  );

  const fetchNextAppointments = async () => {
    setLoadingListNext(true);
    const nextAppointments = await fetchAppointments({
      itemsPerPage: '6',
      repairer: repairer['@id'],
      'order[slotTime]': 'ASC',
      'slotTime[after]': dateObjectAsString(new Date()),
      status: 'validated',
    });

    setAppointmentsNext(nextAppointments);
    setLoadingListNext(false);
  };

  const fetchWaitingAppointments = async () => {
    setLoadingListWait(true);
    const waitAppointments = await fetchAppointments({
      itemsPerPage: '6',
      repairer: repairer['@id'],
      'order[slotTime]': 'ASC',
      status: 'pending_repairer',
      'slotTime[after]': dateObjectAsString(new Date()),
    });

    setAppointmentsWaiting(waitAppointments);
    setLoadingListWait(false);
  };

  const fetchAppointments = async (
    params: RequestParams
  ): Promise<Appointment[]> => {
    const response = await appointmentResource.getAllByRepairer(
      repairer,
      params
    );
    return response['hydra:member'];
  };

  console.log(repairer);

  return (
    <Box>
      {!repairer.enabled && (
        <Box display={'flex'} justifyContent={'center'}>
          <Typography
            textAlign={'center'}
            fontWeight={'bold'}
            width={'50%'}
            marginTop={5}>
            Votre demande a bien été reçue. Elle est en cours de traitement par
            l&apos;équipe Rustine Libre. Dès validation, vous serez tenu
            informé.
          </Typography>
        </Box>
      )}
      {repairer.enabled && (
        <Grid container spacing={3}>
          <Grid item xs={12} md={6}>
            <DashboardNextAppointments
              repairer={repairer}
              appointmentsNext={appointmentsNext}
              fetchNextAppointments={fetchNextAppointments}
              loadingListNext={loadingListNext}
            />
          </Grid>
          <Grid item xs={12} md={6}>
            <DashboardWaitingAppointments
              repairer={repairer}
              appointmentsWaiting={appointmentsWaiting}
              fetchNextAppointments={fetchNextAppointments}
              fetchWaitingAppointments={fetchWaitingAppointments}
              loadingListWait={loadingListWait}
            />
          </Grid>
          {isBoss(currentUser) && (
            <Grid item xs={12} mt={2}>
              <DashboardHomeEmployees
                currentBoss={currentUser}
                repairer={repairer}
              />
            </Grid>
          )}
        </Grid>
      )}
    </Box>
  );
};

export default DashboardHomeContent;
