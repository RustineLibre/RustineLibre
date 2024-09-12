import {SR_ADMIN_SIDEBAR_WIDTH} from '@constants/ui';
import MuiAppBar from '@mui/material/AppBar';
import {AppBarProps as MuiAppBarProps} from '@mui/material/AppBar/AppBar';
import {styled} from '@mui/material/styles';

interface AppBarProps extends MuiAppBarProps {
  open?: boolean;
}

const AppBar = styled(MuiAppBar, {
  shouldForwardProp: (prop) => prop !== 'open',
})<AppBarProps>(({theme, open}) => ({
  zIndex: theme.zIndex.drawer + 1,
  transition: theme.transitions.create(['width', 'margin'], {
    easing: theme.transitions.easing.sharp,
    duration: theme.transitions.duration.leavingScreen,
  }),
  ...(open && {
    marginLeft: SR_ADMIN_SIDEBAR_WIDTH,
    width: `calc(100% - ${SR_ADMIN_SIDEBAR_WIDTH}px)`,
    transition: theme.transitions.create(['width', 'margin'], {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.enteringScreen,
    }),
  }),
}));

export default AppBar;
