import React from 'react';
import {useRouter} from 'next/router';
import {
  ListItemButton,
  ListItemIcon,
  ListItemText,
  ListItem,
  Typography,
  useMediaQuery,
} from '@mui/material';
import theme from 'styles/theme';
import Link from 'next/link';

interface SidebarListItemProps {
  text: string;
  open: boolean;
  icon: any;
  path: string;
  prefetch?: boolean;
}

const SidebarListItem = ({
  text,
  open,
  icon,
  path,
  prefetch = true,
}: SidebarListItemProps): JSX.Element => {
  const router = useRouter();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  return (
    <>
      <Link
        href={path}
        legacyBehavior
        passHref
        prefetch={prefetch}
        style={{textDecoration: 'none'}}>
        <ListItem key={text} disablePadding sx={{display: 'block'}}>
          <ListItemButton
            sx={{
              minHeight: 48,
              justifyContent: open ? 'initial' : 'center',
              px: 2.5,
            }}>
            <ListItemIcon
              sx={{
                minWidth: 0,
                mr: open ? 2 : 'auto',
                justifyContent: 'center',
                color: 'primary.main',
              }}>
              {icon}
            </ListItemIcon>
            {!isMobile && (
              <ListItemText
                disableTypography
                primary={
                  <Typography
                    sx={{
                      color:
                        path === router.asPath ? 'primary.main' : 'grey.600',
                    }}>
                    {text}
                  </Typography>
                }
              />
            )}
          </ListItemButton>
        </ListItem>
      </Link>
    </>
  );
};

export default SidebarListItem;
