import {Box, Button, Grid, TextField, Typography} from '@mui/material';
import PasswordInput from '@components/form/input/PasswordInput';
import React, {ChangeEvent, useContext, useState} from 'react';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import {validateEmail} from '@utils/emailValidator';
import {UserFormContext} from '@contexts/UserFormContext';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import {useRouter} from 'next/router';

export const RegistrationTunnelUserInfo = (): JSX.Element => {
  const router = useRouter();
  const {
    firstName,
    lastName,
    email,
    setStepOneCompleted,
    setTunnelStep,
    setFirstName,
    setLastName,
    setEmail,
  } = useContext(RepairerRegistrationContext);
  const {password} = useContext(UserFormContext);
  const [emailError, setEmailError] = useState<boolean>(false);
  const [emailHelperText, setEmailHelperText] = useState<string>('');

  const handleChangeFirstName = (
    event: ChangeEvent<HTMLInputElement>
  ): void => {
    setFirstName(event.target.value);
  };

  const handleChangeLastName = (event: ChangeEvent<HTMLInputElement>): void => {
    setLastName(event.target.value);
  };

  const handleChangeEmail = (event: ChangeEvent<HTMLInputElement>): void => {
    setEmail(event.target.value);
    if (!validateEmail(event.target.value)) {
      setEmailError(true);
      setEmailHelperText('Veuillez entrer une adresse email valide.');
    } else {
      setEmailError(false);
      setEmailHelperText('');
    }
  };

  const handleNextStep = () => {
    setTunnelStep('choice');
    setStepOneCompleted(true);
    router.push('/reparateur/inscription/choix-antenne');
  };

  return (
    <>
      <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
        <AccountCircleIcon
          sx={{color: 'primary.main', fontSize: '3rem', mx: 'auto'}}
        />
        <Typography variant="h2" textAlign="center" color="primary.main" pb={2}>
          Mon identité
        </Typography>
      </Box>
      <Grid container item xs={12} spacing={2} direction="row">
        <Grid item xs={12} sm={6} md={12} lg={6}>
          <TextField
            fullWidth
            required
            id="firstName"
            label="Prénom"
            name="firstName"
            autoComplete="firstName"
            autoFocus
            value={firstName}
            inputProps={{maxLength: 50}}
            onChange={handleChangeFirstName}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={12} lg={6}>
          <TextField
            fullWidth
            required
            id="lastName"
            label="Nom"
            name="lastName"
            autoComplete="lastName"
            value={lastName}
            inputProps={{maxLength: 50}}
            onChange={handleChangeLastName}
          />
        </Grid>
      </Grid>
      <Grid item xs={12}>
        <TextField
          fullWidth
          required
          error={emailError}
          helperText={emailHelperText}
          type={'email'}
          id="email"
          label="Email"
          name="email"
          autoComplete="email"
          value={email}
          inputProps={{maxLength: 180}}
          onChange={handleChangeEmail}
        />
      </Grid>
      <Grid item xs={12}>
        <PasswordInput />
      </Grid>
      <Box width="100%" display="flex" justifyContent="center" mt={4}>
        <Button
          variant="contained"
          onClick={() => handleNextStep()}
          disabled={!firstName || !lastName || !email || !password}>
          Etape suivante
        </Button>
      </Box>
    </>
  );
};

export default RegistrationTunnelUserInfo;
