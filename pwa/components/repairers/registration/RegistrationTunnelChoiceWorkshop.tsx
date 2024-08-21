import {
  Box,
  Button,
  FormControl,
  FormControlLabel,
  Radio,
  RadioGroup,
  Typography,
} from '@mui/material';
import React, {useContext} from 'react';
import {RepairerRegistrationContext} from '@contexts/RepairerRegistrationContext';
import StorefrontIcon from '@mui/icons-material/Storefront';
import Avatar from '@mui/material/Avatar';

export const RegistrationTunnelChoiceWorkshop = (): JSX.Element => {
  const {
    choiceValue,
    setChoiceValue,
    setChosen,
    setMultipleWorkShop,
    setTunnelStep,
  } = useContext(RepairerRegistrationContext);

  const handleRadioChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setChoiceValue(event.target.value);
    handleSetChoice(event.target.value);
  };
  const handleSetChoice = (choice: string) => {
    setChosen(true);
    choice === 'Oui' ? setMultipleWorkShop(true) : setMultipleWorkShop(false);
  };

  const handleGoBack = () => {
    setTunnelStep('user_info');
  };

  return (
    <>
      <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
        <Box display="flex" flexDirection="column" gap={2} sx={{mx: 'auto'}}>
          <Avatar sx={{bgcolor: 'primary.main', mx: 'auto'}}>
            <StorefrontIcon sx={{color: 'white', fontSize: '1.7rem'}} />
          </Avatar>
          <Typography
            variant="h2"
            textAlign="center"
            color="primary.main"
            pb={2}>
            Mon enseigne
          </Typography>
        </Box>
        <Typography variant="h5" component="label">
          Votre enseigne possède-t-elle plusieurs antennes?
        </Typography>
        <>
          <FormControl>
            <RadioGroup
              aria-labelledby="demo-error-radios"
              name="quiz"
              value={choiceValue}
              onChange={handleRadioChange}>
              <FormControlLabel
                value="Non"
                control={<Radio />}
                label="Non"
                sx={{
                  border: '1px solid grey',
                  borderRadius: '20px',
                  marginTop: '10px',
                  marginBottom: '10px',
                  width: '60%',
                  marginX: 'auto',
                }}
              />
              <FormControlLabel
                value="Oui"
                control={<Radio />}
                label="Oui"
                sx={{
                  border: '1px solid grey',
                  borderRadius: '20px',
                  marginBottom: '10px',
                  marginTop: '10px',
                  width: '60%',
                  marginX: 'auto',
                }}
              />
            </RadioGroup>
          </FormControl>
        </>
      </Box>
      <Box
        width={{xs: '100%', md: '80%'}}
        mt={3}
        display="flex"
        mx="auto"
        justifyContent="space-between">
        <Button variant="outlined" onClick={handleGoBack}>
          Retour
        </Button>
        <Button
          onClick={() => setTunnelStep('workshop')}
          variant="contained"
          disabled={!choiceValue}>
          Suivant
        </Button>
      </Box>
    </>
  );
};

export default RegistrationTunnelChoiceWorkshop;
