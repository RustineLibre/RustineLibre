import Link from 'next/link';
import React from "react";
import Box from '@mui/material/Box';
import BottomNavigation from '@mui/material/BottomNavigation';
import BottomNavigationAction from '@mui/material/BottomNavigationAction';
import HomeIcon from '@mui/icons-material/Home';
import ListIcon from '@mui/icons-material/List';
import Paper from '@mui/material/Paper';
import DirectionsBikeIcon from '@mui/icons-material/DirectionsBike';
import ChatBubbleIcon from '@mui/icons-material/ChatBubble';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';

interface FooterProps {
    logged: boolean;
    role?: 'user';
}

export const Footer = ({
                           logged,
                           role,
                       }: FooterProps): JSX.Element => {

    const [value, setValue] = React.useState(0);

    return (
        <Box sx={{ width: 500 }}>
            <Paper sx={{ position: 'fixed', bottom: 0, left: 0, right: 0 }} elevation={3}>
                <BottomNavigation
                    showLabels
                    value={value}
                    onChange={(event, newValue) => {
                        setValue(newValue);
                    }}
                >
                    <Link href="/" passHref>
                        <BottomNavigationAction label="Accueil" icon={<HomeIcon />} />
                    </Link>
                    <BottomNavigationAction label="Rendez-vous" icon={<ListIcon />} />
                    <BottomNavigationAction label="Mes vélos" icon={<DirectionsBikeIcon />} />
                    <BottomNavigationAction label="Messages" icon={<ChatBubbleIcon />} />
                    <BottomNavigationAction label={logged ? 'Profil' : 'Connexion'} icon={<AccountCircleIcon />} />
                </BottomNavigation>
            </Paper>
        </Box>
    );
};
