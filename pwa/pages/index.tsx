import React, {useState} from 'react';
import Head from 'next/head';
import WebsiteLayout from '@components/layout/WebsiteLayout';
import {useAccount} from '@contexts/AuthContext';
import {Box, Button, Container, Typography} from '@mui/material';
import SearchARepairer from '@components/homepage/SearchARepairer';
import CreateMaintenanceBooklet from '@components/homepage/CreateMaintenanceBooklet';
import JoinTheCollective from '@components/homepage/JoinTheCollective';
import FavoriteRepairers from '@components/homepage/FavoriteRepairers';
import {isBoss, isEmployee} from '@helpers/rolesHelpers';
import Grid from '@mui/material/Unstable_Grid2';
import Arg1 from '@public/img/arg1.webp';
import Arg2 from '@public/img/arg2.webp';
import Arg3 from '@public/img/arg3.webp';
import Image from 'next/image';
import Link from 'next/link';
import Faq from '@components/homepage/Faq';
import {GetStaticProps} from 'next';
import {websiteMediaResource} from '@resources/WebsiteMediaResource';

type homepageProps = {
  homepagePictureFetched: string;
};

const Home = ({homepagePictureFetched = ''}) => {
  const {user} = useAccount({redirectIfMailNotConfirm: '/'});
  const [homepagePicturePath, setHomepagePicturePath] = useState<string>(
    homepagePictureFetched
  );

  const args = [
    {
      title: 'Réparez 100% local',
      text: 'Prenez rendez-vous sur la métropole lilloise et dans les Hauts-de-France',
      img: <Image src={Arg1} width={100} height={100} alt="" />,
    },
    {
      title: 'Vos supers réparateurs',
      text: "Un collectif d'experts pour un travail sur-mesure et de qualité",
      img: <Image src={Arg2} width={100} height={100} alt="" />,
    },
    {
      title: 'Solidaire toi-même !',
      text: "Réparer son vélo, c'est bon pour la planête et pour l'économie locale !",
      img: <Image src={Arg3} width={100} height={100} alt="" />,
    },
  ];

  return (
    <>
      <Head>
        <title> Réparation vélos Lille métropole | Rustine Libre</title>
      </Head>
      <WebsiteLayout>
        <SearchARepairer homepagePicturePath={homepagePicturePath} />
        <Container>
          <Box
            gap={{xs: 6, md: 8}}
            width="100%"
            display="flex"
            flexDirection="column">
            <Grid container spacing={4} width="100%" sx={{mx: 'auto'}}>
              {args.map((arg) => (
                <Grid xs={12} md={4} key={arg.title}>
                  <Box
                    sx={{
                      display: 'flex',
                      flexDirection: {xs: 'column', lg: 'row'},
                      alignItems: 'center',
                      justifyContent: 'center',
                      textAlign: {xs: 'center', lg: 'left'},
                      gap: 2,
                    }}>
                    <Box width="100px" height="100px">
                      {arg.img}
                    </Box>
                    <Box>
                      <Typography gutterBottom variant="h5">
                        {arg.title}
                      </Typography>
                      <Typography>{arg.text}</Typography>
                    </Box>
                  </Box>
                </Grid>
              ))}
            </Grid>
            <Link
              rel="canonical"
              href="/reparateur/chercher-un-reparateur"
              legacyBehavior
              passHref>
              <Button
                size="large"
                variant="contained"
                sx={{mx: 'auto'}}
                color="secondary">
                Je prends rendez-vous
              </Button>
            </Link>
            {user && !isBoss(user) && !isEmployee(user) && (
              <FavoriteRepairers user={user} />
            )}
            <CreateMaintenanceBooklet />
          </Box>
        </Container>
        <Box my={6}>
          <Faq />
        </Box>
        <Container>
          <JoinTheCollective />
        </Container>
      </WebsiteLayout>
    </>
  );
};

export const getStaticProps: GetStaticProps = async () => {
  const homepagePicture = await websiteMediaResource.getById(
    'homepage_main_picture',
    false
  );

  const homepagePictureFetched =
    homepagePicture && homepagePicture.media
      ? homepagePicture.media.contentUrl
      : '/img/rustine-libre-reparateur.webp';

  return {
    props: {
      homepagePictureFetched,
    },
    revalidate: 10,
  };
};

export default Home;
