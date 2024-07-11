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
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';

interface DashboardSidebarListItemProps {
  text: string;
  open: boolean;
  icon: any;
  path: string;
  subItems: SubItem[];
}

interface SubItem {
  text: string;
  path: string;
}

const DashboardSidebarListItem = ({
  text,
  open,
  icon,
  path,
  subItems = [],
}: DashboardSidebarListItemProps): JSX.Element => {
  const router = useRouter();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  return (
    <>
      <Link href={path} style={{textDecoration: 'none'}}>
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
                        path === router.pathname ? 'primary.main' : 'grey.600',
                    }}>
                    {text}
                  </Typography>
                }
              />
            )}
            {!isMobile && subItems.length > 0 && (
              <ListItemIcon
                sx={{
                  minWidth: 0,
                  ml: open ? 2 : 'auto',
                  justifyContent: 'center',
                  color: 'primary.main',
                }}>
                <KeyboardArrowDownIcon />
              </ListItemIcon>
            )}
          </ListItemButton>
        </ListItem>
      </Link>
      {!isMobile && (
        <>
          {subItems.map((subItem) => (
            <Link
              key={subItem.text}
              href={subItem.path}
              style={{textDecoration: 'none'}}>
              <ListItem key={text} disablePadding sx={{display: 'block'}}>
                <ListItemButton
                  sx={{
                    minHeight: 48,
                    justifyContent: open ? 'initial' : 'center',
                    px: 2.5,
                  }}>
                  <ListItemText
                    disableTypography
                    primary={
                      <Typography
                        sx={{
                          color:
                            subItem.path === router.pathname
                              ? 'primary.main'
                              : 'grey.600',
                          fontSize: '0.9rem',
                          paddingLeft: 3,
                        }}>
                        {subItem.text}
                      </Typography>
                    }
                  />
                </ListItemButton>
              </ListItem>
            </Link>
          ))}
        </>
      )}
    </>
  );
};

export default DashboardSidebarListItem;
