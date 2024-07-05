import React, {useContext, useState} from 'react';
import Link from 'next/link';
import {AutodiagContext} from '@contexts/AutodiagContext';
import {
  Box,
  Stack,
  Button,
  Typography,
  CircularProgress,
  TextField,
} from '@mui/material';
import {useRouter} from 'next/router';
import {autoDiagnosticResource} from '@resources/autoDiagResource';

export const AutoDiagTunnelComment = (): JSX.Element => {
  const {
    appointment,
    autoDiagnostic,
    comment,
    setComment,
    setAutoDiagnostic,
    setTunnelStep,
  } = useContext(AutodiagContext);
  const router = useRouter();
  const [loadingComment, setLoadingComment] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleClickBack = (): void => {
    setTunnelStep('photo');
  };

  const handleClickNext = async () => {
    if (!appointment || !autoDiagnostic) {
      return;
    }
    setLoadingComment(true);
    if (comment) {
      const autodiag = await autoDiagnosticResource.put(autoDiagnostic['@id'], {
        comment: comment,
      });
      setAutoDiagnostic(autodiag);
    }

    router.push(`/rendez-vous/recapitulatif/${appointment.id}`);
    setLoadingComment(false);
  };

  return (
    <Stack
      spacing={4}
      display="flex"
      flexDirection="column"
      alignItems="center"
      width="100%">
      <Typography variant="h5" component="label">
        Ajouter un commentaire
      </Typography>
      {loadingComment && <CircularProgress />}
      {!loadingComment && (
        <TextField
          placeholder={'Commentaire'}
          minRows={3}
          onChange={(e: {target: {value: string}}) =>
            setComment(e.target.value)
          }
        />
      )}
      <Box width="100%" display="flex" justifyContent="space-between" mt={4}>
        <Button variant="outlined" onClick={handleClickBack}>
          Retour
        </Button>
        <Button variant="contained" onClick={handleClickNext}>
          Suivant
        </Button>
      </Box>
    </Stack>
  );
};

export default AutoDiagTunnelComment;
