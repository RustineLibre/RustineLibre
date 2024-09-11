import {
  Box,
  Button,
  FormControl,
  FormControlLabel,
  Radio,
  RadioGroup,
  Typography,
  useMediaQuery,
} from '@mui/material';
import React, {useState} from 'react';
import theme from '../../styles/theme';

interface AppointmentPlaceChoiceProps {
  confirmAppointmentPlace: (choice: string) => void;
  choice: string;
  setChoice: (choice: string) => void;
}
const AppointmentPlaceChoice = ({
  confirmAppointmentPlace,
  choice,
  setChoice,
}: AppointmentPlaceChoiceProps) => {
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  const handleRadioChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    let radioChoice = event.target.value;
    setChoice(radioChoice);
  };

  return (
    <Box width="100%">
      <Typography pb={2} textAlign="center">
        Indiquez au réparateur le lieu du rendez-vous
      </Typography>
      <FormControl fullWidth={true}>
        <RadioGroup
          aria-labelledby="demo-error-radios"
          name="appointment choice"
          value={choice}
          onChange={handleRadioChange}>
          <FormControlLabel
            value={'workshop'}
            control={<Radio />}
            label="À l'atelier"
            sx={{
              border: '1px solid grey',
              borderRadius: '20px',
              marginTop: '10px',
              marginBottom: '10px',
              width: isMobile ? '70%' : '40%',
              marginX: 'auto',
            }}
          />
          <FormControlLabel
            value={'home'}
            control={<Radio />}
            label="À une autre adresse"
            sx={{
              border: '1px solid grey',
              borderRadius: '20px',
              marginBottom: '10px',
              marginTop: '10px',
              width: isMobile ? '70%' : '40%',
              marginX: 'auto',
            }}
          />
        </RadioGroup>
      </FormControl>

      <Box display="flex" justifyContent="center" pt={4}>
        <Button
          variant="contained"
          size="large"
          onClick={() => confirmAppointmentPlace(choice)}>
          Suivant
        </Button>
      </Box>
    </Box>
  );
};

export default AppointmentPlaceChoice;
