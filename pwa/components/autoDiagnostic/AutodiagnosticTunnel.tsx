import React, {useContext, useEffect, useState} from 'react';
import Box from '@mui/material/Box';
import {Container, Paper, Stack} from '@mui/material';
import useMediaQuery from '@mui/material/useMediaQuery';
import {AutodiagContext} from '@contexts/AutodiagContext';
import {appointmentResource} from '@resources/appointmentResource';
import {useRouter} from 'next/router';
import AutoDiagTunnelPrestation from '@components/autoDiagnostic/AutodiagTunnelPrestation';
import AutoDiagTunnelPhoto from '@components/autoDiagnostic/AutoDiagTunnelPhoto';
import AutoDiagTunnelChoice from '@components/autoDiagnostic/AutoDiagTunnelChoice';
import AutoDiagTunnelBikeSelection from '@components/autoDiagnostic/AutoDiagTunnelBikeSelection';
import {useTheme} from '@mui/material/styles';
import AutoDiagTunnelComment from '@components/autoDiagnostic/AutoDiagTunnelComment';

interface AutoDiagnosticTunnelProps {
  appointmentId: string;
}

export const AutoDiagnosticTunnel = ({
  appointmentId,
}: AutoDiagnosticTunnelProps): JSX.Element => {
  const router = useRouter();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [loading, setLoading] = useState<boolean>(false);
  const {
    tunnelStep,
    appointment,
    setTunnelStep,
    setPrestation,
    setAppointment,
    setAutoDiagnostic,
    setPhoto,
  } = useContext(AutodiagContext);

  async function fetchAppointment() {
    if (appointmentId) {
      setLoading(true);
      const appointmentFetched =
        await appointmentResource.getById(appointmentId);
      setAppointment(appointmentFetched);

      if (!appointmentFetched) {
        return router.push('/');
      }

      // If autodiag still exist, go to 2nd step
      if (appointmentFetched.autoDiagnostic) {
        setLoading(false);
        setAutoDiagnostic(appointmentFetched.autoDiagnostic);
        setPrestation(appointmentFetched.autoDiagnostic.prestation);
        setTunnelStep('photo');

        if (appointmentFetched.autoDiagnostic.photo) {
          setPhoto(appointmentFetched.autoDiagnostic.photo);
        }
      } else {
        // If no autodiag, check bike selection
        if (!appointmentFetched.bike && !appointmentFetched.bikeType) {
          setTunnelStep('bike_selection');
        } else {
          setTunnelStep('choice');
        }
        setLoading(false);
      }
    }
  }

  useEffect(() => {
    fetchAppointment();
  }, [appointmentId]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <Box>
      {!loading && appointment && (
        <Box textAlign="center">
          <Paper
            elevation={isMobile ? 0 : 1}
            sx={{p: 3, borderRadius: 6, maxWidth: '600px', mx: 'auto'}}>
            <Stack
              spacing={5}
              sx={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
              }}>
              {tunnelStep === 'bike_selection' && (
                <AutoDiagTunnelBikeSelection />
              )}
              {tunnelStep === 'choice' && <AutoDiagTunnelChoice />}
              {tunnelStep === 'prestation' && <AutoDiagTunnelPrestation />}
              {tunnelStep === 'photo' && <AutoDiagTunnelPhoto />}
              {tunnelStep === 'comment' && <AutoDiagTunnelComment />}
            </Stack>
          </Paper>
        </Box>
      )}
    </Box>
  );
};

export default AutoDiagnosticTunnel;
