import {Repairer} from '@interfaces/Repairer';
import {Box, Typography} from '@mui/material';
import GoogleCalendarSync from '@components/calendar/GoogleCalendarSync';

interface GoogleAgendaLoginProps {
  repairer: Repairer;
}
const GoogleAgendaLogin = ({repairer}: GoogleAgendaLoginProps) => {
  return (
    <>
      <Box sx={{display: 'flex', flexDirection: 'column', gap: '20px'}}>
        <Typography variant="h5">
          Affichez vos rendez-vous dans Google Agenda
        </Typography>
        <Typography sx={{maxWidth: '650px'}}>
          Vous êtes habitués à gérer toute l&apos;activité de votre structure
          sur Google Agenda ?<br />
          Vous pouvez désormais afficher vos rendez-vous Rustine Libre sur
          Google Agenda en cliquant sur le bouton &quot;Se connecter avec
          Google&quot;
        </Typography>
        <Typography variant={'h6'}>
          Statut : {repairer.isConnectedToGoogle ? 'Connecté' : 'Non connecté'}
        </Typography>
        <GoogleCalendarSync repairer={repairer} />
      </Box>
    </>
  );
};

export default GoogleAgendaLogin;
