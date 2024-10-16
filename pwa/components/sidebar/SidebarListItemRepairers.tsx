import {useAccount} from '@contexts/AuthContext';
import {Repairer} from '@interfaces/Repairer';
import {ExpandLess, ExpandMore} from '@mui/icons-material';
import StorefrontIcon from '@mui/icons-material/Storefront';
import Collapse from '@mui/material/Collapse';
import React, {useState} from 'react';
import {useRouter} from 'next/router';
import {
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Typography,
  useMediaQuery,
  List,
} from '@mui/material';
import theme from 'styles/theme';
import Link from 'next/link';

const SidebarListItemRepairers = (): React.JSX.Element => {
  const router = useRouter();
  const {user} = useAccount({
    redirectIfNotFound: `/login?next=${encodeURIComponent(router.asPath)}`,
  });
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const isRepairerSelected = (repairer: Repairer) =>
    router.query.repairer_id
      ? +router.query.repairer_id === +repairer.id
      : false;
  const [toggleRepairers, setToggleRepairers] = useState<boolean>(true);

  return (
    <>
      {!isMobile && (
        <>
          <ListItemButton
            sx={{
              minHeight: 48,
              px: 2.5,
            }}
            onClick={() => setToggleRepairers(!toggleRepairers)}>
            <ListItemIcon
              sx={{
                minWidth: 0,
                mr: 2,
                justifyContent: 'center',
                color: 'primary.main',
              }}>
              <StorefrontIcon />
            </ListItemIcon>
            <ListItemText
              disableTypography
              primary={<Typography color="grey.600">Boutiques</Typography>}
            />
            {toggleRepairers ? <ExpandMore /> : <ExpandLess />}
          </ListItemButton>
          <Collapse in={toggleRepairers} timeout="auto" unmountOnExit>
            <List
              component="div"
              disablePadding
              sx={{
                marginBottom: 1,
              }}>
              {user?.repairers.map((repairer) => (
                <Link
                  key={repairer.id}
                  href={`/sradmin/boutiques/${repairer.id}`}
                  passHref
                  legacyBehavior>
                  <ListItemButton
                    component="a"
                    sx={{
                      paddingTop: 0.5,
                      paddingBottom: 0.5,
                    }}>
                    <ListItemText
                      disableTypography={true}
                      primary={repairer.name}
                      sx={{
                        '::before': {
                          content: '"-"',
                          display: 'inline-flex',
                          marginLeft: 3,
                          marginRight: 2,
                        },
                        color: isRepairerSelected(repairer)
                          ? 'primary.main'
                          : 'grey.600',
                        whiteSpace: 'normal',
                      }}
                    />
                  </ListItemButton>
                </Link>
              ))}
            </List>
          </Collapse>
        </>
      )}
      {isMobile && (
        <>
          <ListItemButton
            href={'/sradmin/boutiques'}
            sx={{
              minHeight: 48,
              px: 2.5,
            }}>
            <ListItemIcon
              sx={{
                minWidth: 0,
                mr: 2,
                justifyContent: 'center',
                color:
                  '/sradmin/boutiques' === router.asPath
                    ? 'primary.main'
                    : 'grey.600',
              }}>
              <StorefrontIcon />
            </ListItemIcon>
          </ListItemButton>
        </>
      )}
    </>
  );
};

export default SidebarListItemRepairers;
