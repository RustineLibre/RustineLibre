import * as React from 'react';
import {useRouter} from 'next/router';
import {styled, Theme, CSSObject, useTheme, alpha} from '@mui/material/styles';
import {
  Box,
  Toolbar,
  List,
  Divider,
  ListItem,
  Typography,
  Button,
} from '@mui/material';
import MuiDrawer from '@mui/material/Drawer';
import MuiAppBar, {AppBarProps as MuiAppBarProps} from '@mui/material/AppBar';
import DashboardSidebarListItem from '@components/dashboard/DashboardSidebarListItem';
import HomeIcon from '@mui/icons-material/Home';
import ForumIcon from '@mui/icons-material/Forum';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import FolderSharedIcon from '@mui/icons-material/FolderShared';
import InfoIcon from '@mui/icons-material/Info';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import EngineeringIcon from '@mui/icons-material/Engineering';
import {useAccount, useAuth} from '@contexts/AuthContext';
import useMediaQuery from '@mui/material/useMediaQuery';
import HandymanIcon from '@mui/icons-material/Handyman';
import Link from 'next/link';
import {isBoss, isEmployee, isRepairerItinerant} from '@helpers/rolesHelpers';
import RouteIcon from '@mui/icons-material/Route';
import Logo from '@components/common/Logo';
import {useContext, useEffect, useState} from 'react';
import {Discussion} from '@interfaces/Discussion';
import {ENTRYPOINT} from '@config/entrypoint';
import {discussionResource} from '@resources/discussionResource';
import Badge from '@mui/material/Badge';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import GoogleCalendarSync from '@components/calendar/GoogleCalendarSync';
import {Repairer} from '@interfaces/Repairer';
import MenuItem from '@mui/material/MenuItem';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';
import Menu, {MenuProps} from '@mui/material/Menu';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const drawerWidth = 240;

const openedMixin = (theme: Theme): CSSObject => ({
  width: drawerWidth,
  transition: theme.transitions.create('width', {
    easing: theme.transitions.easing.sharp,
    duration: theme.transitions.duration.enteringScreen,
  }),
  overflowX: 'hidden',
});

const closedMixin = (theme: Theme): CSSObject => ({
  transition: theme.transitions.create('width', {
    easing: theme.transitions.easing.sharp,
    duration: theme.transitions.duration.leavingScreen,
  }),
  overflowX: 'hidden',
  width: `calc(${theme.spacing(7)} + 1px)`,
  [theme.breakpoints.up('sm')]: {
    width: `calc(${theme.spacing(8)} + 1px)`,
  },
});

const DrawerHeader = styled('div')(({theme}) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'flex-start',
  padding: theme.spacing(0, 1),
  // necessary for content to be below app bar
  ...theme.mixins.toolbar,
}));

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
    marginLeft: drawerWidth,
    width: `calc(100% - ${drawerWidth}px)`,
    transition: theme.transitions.create(['width', 'margin'], {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.enteringScreen,
    }),
  }),
}));

const Drawer = styled(MuiDrawer, {
  shouldForwardProp: (prop) => prop !== 'open',
})(({theme, open}) => ({
  width: drawerWidth,
  flexShrink: 0,
  whiteSpace: 'nowrap',
  boxSizing: 'border-box',
  ...(open && {
    ...openedMixin(theme),
    '& .MuiDrawer-paper': openedMixin(theme),
  }),
  ...(!open && {
    ...closedMixin(theme),
    '& .MuiDrawer-paper': closedMixin(theme),
  }),
}));

interface DashboardLayoutProps {
  children?: React.ReactNode;
}

const StyledMenu = styled((props: MenuProps) => (
  <Menu
    elevation={0}
    anchorOrigin={{
      vertical: 'bottom',
      horizontal: 'right',
    }}
    transformOrigin={{
      vertical: 'top',
      horizontal: 'right',
    }}
    {...props}
  />
))(({theme}) => ({
  '& .MuiPaper-root': {
    borderRadius: 6,
    marginTop: theme.spacing(1),
    minWidth: 180,
    color:
      theme.palette.mode === 'light'
        ? 'rgb(55, 65, 81)'
        : theme.palette.grey[300],
    boxShadow:
      'rgb(255, 255, 255) 0px 0px 0px 0px, rgba(0, 0, 0, 0.05) 0px 0px 0px 1px, rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px',
    '& .MuiMenu-list': {
      padding: '4px 0',
    },
    '& .MuiMenuItem-root': {
      '& .MuiSvgIcon-root': {
        fontSize: 18,
        color: theme.palette.text.secondary,
        marginRight: theme.spacing(1.5),
      },
      '&:active': {
        backgroundColor: alpha(
          theme.palette.primary.main,
          theme.palette.action.selectedOpacity
        ),
      },
    },
  },
}));

const DashboardLayout = ({children}: DashboardLayoutProps) => {
  const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
  const open = !!anchorEl;
  const openMenu = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const closeMenu = () => {
    setAnchorEl(null);
  };
  const handleClose = (repairer: Repairer | null = null) => {
    setAnchorEl(null);
    if (!repairer) {
      router.push('/sradmin/boutiques');
      return;
    }

    router.push(`/sradmin/boutiques/${repairer.id}`);
  };

  const router = useRouter();
  const {repairer} = useContext(DashboardRepairerContext);

  const {user} = useAccount({
    redirectIfNotFound: `/login?next=${encodeURIComponent(router.asPath)}`,
  });
  const isBossOrEmployee = user && (isBoss(user) || isEmployee(user));
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const {logout} = useAuth();
  const [discussions, setDiscussions] = useState<Discussion[]>([]);
  const [unreadMessages, setUnreadMessages] = useState<number>(0);

  const subscribeMercureDiscussions = async (): Promise<void> => {
    const hubUrl = `${ENTRYPOINT}/.well-known/mercure`;
    const hub = new URL(hubUrl);
    discussions.map((discussion) => {
      hub.searchParams.append('topic', `${ENTRYPOINT}${discussion['@id']}`);
    });

    const eventSource = new EventSource(hub);
    eventSource.onmessage = (event) => {
      countUnread();
    };
  };

  const countUnread = async (): Promise<void> => {
    if (!user || !repairer) {
      return;
    }

    const countUnread = await discussionResource.countUnreadForRepairer(
      repairer,
      {}
    );
    setUnreadMessages(countUnread.count);
  };

  const fetchDiscussions = async () => {
    if (!repairer) {
      return;
    }

    const response = await discussionResource.getAll(true, {
      repairer: repairer.id,
      itemsPerPage: 50,
      'order[lastMessage]': 'DESC',
    });
    setDiscussions(response['hydra:member']);
  };

  useEffect(() => {
    if (user) {
      if (!user.emailConfirmed) {
        router.push('/login');
      } else {
        countUnread();
        fetchDiscussions();
      }
    }
  }, [user]); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    if (discussions.length > 0) {
      subscribeMercureDiscussions();
    }
  }, [discussions]); // eslint-disable-line react-hooks/exhaustive-deps

  if (user && !isBoss(user) && !isEmployee(user)) {
    router.push('/');
  }

  const clickLogOut = async () => {
    await logout();
    await router.push(`/login?next=${encodeURIComponent(router.asPath)}`);
  };

  return (
    <>
      {isBossOrEmployee && (
        <Box>
          <AppBar position="sticky" open={!isMobile}>
            <Toolbar>
              <List>
                <ListItem key="1" disablePadding>
                  <Typography variant="h4">
                    Bonjour {user?.firstName} !
                  </Typography>
                </ListItem>
              </List>

              <Box sx={{float: 'right'}}>
                {repairer &&
                  router.asPath ===
                    `/sradmin/boutiques/${repairer.id}/agenda` && (
                    <GoogleCalendarSync repairer={repairer} />
                  )}
              </Box>

              <Button
                onClick={clickLogOut}
                sx={{
                  ml: 'auto',
                  backgroundColor: 'white',
                  color: 'primary.main',
                  '&:hover': {
                    backgroundColor: 'rgba(255,255,255,0.8)',
                    color: 'primary.dark',
                  },
                }}
                variant="contained">
                Déconnexion
              </Button>
            </Toolbar>
          </AppBar>
          <Box sx={{display: 'flex'}}>
            <Drawer variant={'permanent'} open={!isMobile} anchor="left">
              <DrawerHeader>
                <Link
                  href="/"
                  style={{height: '35px', display: 'block', margin: '0 auto'}}>
                  <Logo inline color="primary" />
                </Link>
              </DrawerHeader>
              <Divider />

              <div
                style={{width: '100%', margin: '20px 0', textAlign: 'center'}}>
                <Button
                  onClick={openMenu}
                  id="repairer-selection-button"
                  aria-controls={open ? 'repairer-selection-menu' : undefined}
                  aria-haspopup="true"
                  aria-expanded={open ? 'true' : undefined}
                  variant="contained"
                  disableElevation
                  // onClick={openMenu}
                  endIcon={<KeyboardArrowDownIcon />}>
                  {repairer ? repairer.name : 'Toutes mes boutiques'}
                </Button>
                <StyledMenu
                  id="repairer-selection-menu"
                  MenuListProps={{
                    'aria-labelledby': 'repairer-selection-button',
                  }}
                  anchorEl={anchorEl}
                  open={open}
                  onClose={closeMenu}>
                  <MenuItem onClick={() => handleClose(null)} disableRipple>
                    Toutes mes boutiques
                  </MenuItem>
                  <Divider />
                  {user?.repairers.map((repairer) => (
                    <MenuItem
                      key={repairer.id}
                      onClick={() => handleClose(repairer)}
                      disableRipple>
                      {repairer.name}
                    </MenuItem>
                  ))}
                </StyledMenu>
              </div>

              {repairer && (
                <>
                  <List>
                    <DashboardSidebarListItem
                      text="Tableau de bord"
                      open={true}
                      icon={<HomeIcon />}
                      path={`/sradmin/boutiques/${repairer.id}`}
                    />
                    <DashboardSidebarListItem
                      text="Agenda"
                      open={true}
                      icon={<CalendarMonthIcon />}
                      path={`/sradmin/boutiques/${repairer.id}/agenda`}
                    />
                    {user && isRepairerItinerant(repairer) && (
                      <DashboardSidebarListItem
                        text="Tournée"
                        open={true}
                        icon={<RouteIcon />}
                        path={`/sradmin/boutiques/${repairer.id}/tour`}
                      />
                    )}
                    <Badge badgeContent={unreadMessages} color="primary">
                      <DashboardSidebarListItem
                        text="Messages"
                        open={true}
                        icon={<ForumIcon />}
                        path={`/sradmin/boutiques/${repairer.id}/messagerie`}
                      />
                    </Badge>
                    <DashboardSidebarListItem
                      text="Clients"
                      open={true}
                      icon={<FolderSharedIcon />}
                      path={`/sradmin/boutiques/${repairer.id}/clients`}
                    />
                    {user && isBoss(user) && (
                      <DashboardSidebarListItem
                        text="Paramètres Agenda"
                        open={true}
                        icon={<HandymanIcon />}
                        path={`/sradmin/boutiques/${repairer.id}/agenda/parametres`}
                      />
                    )}
                    {user && isBoss(user) && (
                      <DashboardSidebarListItem
                        text="Employés"
                        open={true}
                        icon={<EngineeringIcon />}
                        path={`/sradmin/boutiques/${repairer.id}/employes`}
                      />
                    )}
                    {user && isBoss(user) && (
                      <DashboardSidebarListItem
                        text="Informations"
                        open={true}
                        icon={<InfoIcon />}
                        path={`/sradmin/boutiques/${repairer.id}/informations`}
                      />
                    )}
                  </List>
                </>
              )}
              {repairer && <Divider />}
              <List>
                <DashboardSidebarListItem
                  text="Mon compte"
                  open={true}
                  icon={<AccountCircleIcon />}
                  path="/sradmin/mon-compte"
                />
                <DashboardSidebarListItem
                  text="Retourner sur le site"
                  open={true}
                  icon={<ArrowBackIcon />}
                  path="/"
                />
              </List>
            </Drawer>
            <Box sx={{flexGrow: 1, width: '80%'}} p={3}>
              {children}
            </Box>
          </Box>
        </Box>
      )}
    </>
  );
};

export default DashboardLayout;
