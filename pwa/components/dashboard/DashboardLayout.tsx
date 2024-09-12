import AppBar from '@components/dashboard/appbar/AppBar';
import {Sidebar, SidebarHeader} from '@components/dashboard/sidebar/Sidebar';
import SidebarListItemRepairers from '@components/sidebar/SidebarListItemRepairers';
import {SR_ADMIN_REPAIRER_PAGE} from '@constants/pages';
import * as React from 'react';
import {useRouter} from 'next/router';
import {useTheme} from '@mui/material/styles';
import {
  Box,
  Toolbar,
  List,
  Divider,
  ListItem,
  Typography,
  Button,
} from '@mui/material';
import SidebarListItem from '@components/sidebar/SidebarListItem';
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
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

interface DashboardLayoutProps {
  children?: React.ReactNode;
}

const DashboardLayout = ({children}: DashboardLayoutProps) => {
  const router = useRouter();
  const {repairer} = useContext(DashboardRepairerContext);
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const {logout} = useAuth();
  const {user} = useAccount({
    redirectIfNotFound: `/login?next=${encodeURIComponent(router.asPath)}`,
  });
  const [discussions, setDiscussions] = useState<Discussion[]>([]);
  const [unreadMessages, setUnreadMessages] = useState<number>(0);

  const isRepairerPage = router.pathname.includes(SR_ADMIN_REPAIRER_PAGE);
  const isBossOrEmployee = user && (isBoss(user) || isEmployee(user));

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
            <Sidebar variant={'permanent'} open={!isMobile} anchor="left">
              <SidebarHeader>
                <Link
                  href="/"
                  style={{height: '35px', display: 'block', margin: '0 auto'}}>
                  <Logo inline color="primary" />
                </Link>
              </SidebarHeader>

              <Divider />

              <List>
                <SidebarListItem
                  text="Tableau de bord"
                  open={true}
                  icon={<HomeIcon />}
                  path="/sradmin/boutiques"
                />
                {user && isBoss(user) && (
                  <>
                    <SidebarListItemRepairers />
                    {!isRepairerPage && (
                      <SidebarListItem
                        text="Employés"
                        open={true}
                        icon={<EngineeringIcon />}
                        path={`/sradmin/boutiques/employes`}
                      />
                    )}
                  </>
                )}
                {(repairer ||
                  (user &&
                    user.repairerEmployee &&
                    user.repairerEmployee.repairer)) && (
                  <>
                    <SidebarListItem
                      text="Agenda"
                      open={true}
                      icon={<CalendarMonthIcon />}
                      path={`/sradmin/boutiques/${repairer?.id ?? user?.repairerEmployee?.repairer.id}/agenda`}
                    />
                    {repairer && isRepairerItinerant(repairer) && (
                      <SidebarListItem
                        text="Tournée"
                        open={true}
                        icon={<RouteIcon />}
                        path={`/sradmin/boutiques/${repairer?.id ?? user?.repairerEmployee?.repairer.id}/tour`}
                      />
                    )}
                    <Badge badgeContent={unreadMessages} color="primary">
                      <SidebarListItem
                        text="Messages"
                        open={true}
                        icon={<ForumIcon />}
                        path={`/sradmin/boutiques/${repairer?.id ?? user?.repairerEmployee?.repairer.id}/messagerie`}
                      />
                    </Badge>
                    <SidebarListItem
                      text="Clients"
                      open={true}
                      icon={<FolderSharedIcon />}
                      path={`/sradmin/boutiques/${repairer?.id ?? user?.repairerEmployee?.repairer.id}/clients`}
                    />
                    {user && isBoss(user) && (
                      <SidebarListItem
                        text="Paramètres Agenda"
                        open={true}
                        icon={<HandymanIcon />}
                        path={`/sradmin/boutiques/${repairer?.id}/agenda/parametres`}
                      />
                    )}
                    {user && isBoss(user) && (
                      <SidebarListItem
                        text="Employés"
                        open={true}
                        icon={<EngineeringIcon />}
                        path={`/sradmin/boutiques/${repairer?.id}/employes`}
                      />
                    )}
                    {user && isBoss(user) && (
                      <SidebarListItem
                        text="Informations"
                        open={true}
                        icon={<InfoIcon />}
                        path={`/sradmin/boutiques/${repairer?.id}/informations`}
                      />
                    )}
                  </>
                )}
              </List>

              <Divider />

              <List>
                <SidebarListItem
                  text="Mon compte"
                  open={true}
                  icon={<AccountCircleIcon />}
                  path="/sradmin/mon-compte"
                />
                <SidebarListItem
                  text="Retourner sur le site"
                  open={true}
                  icon={<ArrowBackIcon />}
                  path="/"
                />
              </List>
            </Sidebar>
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
