import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import React from 'react';
import Head from 'next/head';
import {Container, Typography, Box, List, ListItem} from '@mui/material';
import WebsiteLayout from '@components/layout/WebsiteLayout';

const OurCharter: NextPageWithLayout = () => {
  return (
    <>
      <Head>
        <title>Charte de Rustine Libre | Rustine Libre</title>
      </Head>
      <WebsiteLayout>
        <Container sx={{width: {xs: '100%', md: '70%'}}}>
          <Box
            display="flex"
            flexDirection="column"
            gap={2}
            sx={{mx: 'auto', mt: 8}}>
            <Typography
              variant="h1"
              textAlign="center"
              color="primary.main"
              pb={2}>
              Charte de Rustine Libre
            </Typography>

            <Box>
              <Typography component="h2" fontWeight={600} color="primary">
                Préambule
              </Typography>
              <Typography component="p">
                En 2022, des réparateurs du Nord Pas-de-Calais se sont réunis,
                sous l’impulsion d’un projet européen soutenu par l’Apes
                (Acteurs pour une économie solidaire), pour développer un outil
                numérique respectueux de l’humain, dans une logique de Commun.
              </Typography>
              <Typography component="p">
                Le collectif est constitué d’associations d’aide à la réparation
                , de réparateur·ice·s exerçants seul·e·s, et d’ateliers de réparation
                regroupant plusieurs réparateur·ice·s. Nos fonctionnements sont
                différents mais nos objectifs sont les mêmes : mutualisation,
                transition écologique et sociétale, coopération... Nous mettons
                notre expérience et nos compétences au service des cyclistes.
              </Typography>
              <Typography component="p">
                Nous proposons des solutions de réparation, du conseil, de la
                formation, qui rendront les usager·e·s du vélo de plus en plus
                autonomes
              </Typography>
              <Typography component="p">
                Cette présente charte expose nos engagements vis-à-vis des
                utilisateur·ice·s de l’application et permettront à d’autres de se
                reconnaître et de nous rejoindre !
              </Typography>
            </Box>
            <Box>
              <Typography component="h3" fontWeight={600} color="primary">
                1. Engagements des ateliers de réparation
              </Typography>
              <List
                sx={{
                  listStyleType: 'disc',
                  pl: 4,
                  py: 0,
                }}>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  garantir la sécurité du cycliste
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  respecter les tarifs affichés et être transparent sur les
                  constituants de ces tarifs
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  ne pratiquer aucune discrimination sur la clientèle
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  suivre une obligation de conseils et de résultats
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  respecter les lois et obligations professionnelles en vigueur
                </ListItem>
              </List>
            </Box>
            <Box>
              <Typography component="h3" fontWeight={600} color="primary">
                1 bis. Engagements des ateliers d'aide à la réparation
              </Typography>
              <List
                sx={{
                  listStyleType: 'disc',
                  pl: 4,
                  py: 0,
                }}>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  accueillir sans discrimination des cyclistes voulant apprendre
                  à réparer eux-mêmes leurs vélos
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  les ateliers associatifs s’engagent sur du conseil, de l'aide
                  et des moyens pour réparer les vélos des adhérents, dans une
                  démarche d’accompagnement et de transmission de savoir
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  les réparateur.rice.s bénévoles et/ ou salariés font de leur
                  mieux pour répondre aux demandes, remettre en état de
                  fonctionnement les vélos, sans obligation de résultat, ni
                  engager leur responsabilité sur des dommages éventuels suite à
                  leur accompagnement
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  de l'information relatives à la sécurité (attitudes,
                  équipements, réparations) seront préconisées, sans pourtant
                  engager la responsabilité de l'atelier suite à la
                  participation à un atelier d'aide à la réparation
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  proposer une charte et/ ou un règlement intérieur accessibles
                  et transparents pour expliquer leur fonctionnement propre, qui
                  doit être consulté et accepté
                </ListItem>
              </List>
            </Box>
            <Box>
              <Typography component="h3" fontWeight={600} color="primary">
                2. Engagements sur les pratiques des différent·e·s acteur·ice·s
                de la réparation
              </Typography>
              <List
                sx={{
                  listStyleType: 'disc',
                  pl: 4,
                  py: 0,
                }}>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  faire partie d’une structure qui se reconnaît et s’inscrit
                  dans les pratiques de l’économie sociale et solidaire
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  être dans une volonté de coopération plutôt que de compétition
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  favoriser un cadre de travail respectueux de l’humain et de
                  son épanouissement ainsi que de la législation en vigueur
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  mettre en valeur et développer les compétences de réparation
                  des réparateurs et des cyclistes
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  participer à la transition écologique
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  vouloir s’inscrire dans le développement de son territoire
                </ListItem>
              </List>
            </Box>
            <Box>
              <Typography component="h3" fontWeight={600} color="primary">
                3. Engagement dans le collectif de réparateur·ice·s
              </Typography>
              <List
                sx={{
                  listStyleType: 'disc',
                  pl: 4,
                  py: 0,
                }}>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  participer à la vie démocratique du collectif
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  respecter les décisions prises par le collectif
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  participer au bon fonctionnement de l’application
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  s’inscrire dans une démarche de mutualisation
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  privilégier les rapports humains directs aux échanges
                  numériques
                </ListItem>
              </List>
            </Box>
            <Box>
              <Typography component="h3" fontWeight={600} color="primary">
                4. Engagement vers un monde plus cyclable
              </Typography>
              <List
                sx={{
                  listStyleType: 'disc',
                  pl: 4,
                  py: 0,
                }}>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  participer à une logique d’accès au vélo pour tou.te.s
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  promouvoir le vélo, ses bienfaits, sa pratique sous toutes ses
                  formes
                </ListItem>
                <ListItem
                  sx={{
                    display: 'list-item',
                  }}>
                  être associés à moyen terme à l’élaboration des politiques
                  publiques en matière de mobilité et de formation
                  professionnelle
                </ListItem>
              </List>
            </Box>
          </Box>
        </Container>
      </WebsiteLayout>
    </>
  );
};

export default OurCharter;
