import {ENTRYPOINT} from '@config/entrypoint';
import React, {useEffect, useState} from 'react';
import Link from 'next/link';
import Image from 'next/image';
import {discussionResource} from '@resources/discussionResource';
import {Avatar, Box, CardMedia, Typography, Badge} from '@mui/material';
import {formatDate} from '@helpers/dateHelper';
import {Discussion} from '@interfaces/Discussion';
import {Repairer} from '@interfaces/Repairer';

type DiscussionListItemProps = {
  discussionGiven: Discussion;
  repairer: Repairer | null;
  current?: boolean;
};

const DiscussionListItem = ({
  discussionGiven,
  repairer,
  current,
}: DiscussionListItemProps): JSX.Element => {
  const [unreadCounter, setUnreadCounter] = useState<number>(0);
  const [discussion, setDiscussion] = useState<Discussion>(discussionGiven);

  const subscribeMercureDiscussion = async (): Promise<void> => {
    const hubUrl = `${ENTRYPOINT}/.well-known/mercure`;
    const hub = new URL(hubUrl);
    hub.searchParams.append('topic', `${ENTRYPOINT}${discussion['@id']}`);
    const eventSource = new EventSource(hub);
    eventSource.onmessage = (event) => {
      countUnreadMessagesFromDiscussion();
      refreshDiscussion();
    };
  };

  const countUnreadMessagesFromDiscussion = async (): Promise<void> => {
    const countUnread = await discussionResource.countUnreadDiscussion(
      discussion.id.toString(),
      {}
    );
    setUnreadCounter(countUnread.count);
  };

  const refreshDiscussion = async (): Promise<void> => {
    const refreshDiscussion = await discussionResource.get(
      discussion['@id'],
      true
    );
    if (refreshDiscussion) {
      setDiscussion(refreshDiscussion);
    }
  };

  useEffect(() => {
    countUnreadMessagesFromDiscussion();
    subscribeMercureDiscussion();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <>
      <Link
        legacyBehavior
        passHref
        key={discussion.id}
        href={
          !repairer
            ? `/messagerie/${discussion.id}`
            : `/sradmin/boutiques/${repairer.id}/messagerie/${discussion.id}`
        }>
        <Box
          sx={{
            cursor: current ? 'default' : 'pointer',
            width: '100%',
            borderRadius: 5,
            mb: 2,
            transition: 'all ease 0.3s',
            bgcolor: current ? 'lightprimary.dark' : 'grey.100',
            '&:hover': {
              filter: current ? 'none' : 'brightness(0.90)',
            },
          }}>
          <Badge badgeContent={unreadCounter} color="primary">
            <Box px={2} py={2} display="flex" gap={2} alignItems="center">
              {!repairer && (
                <>
                  {discussion.repairer.thumbnail ? (
                    <CardMedia
                      component="div"
                      sx={{
                        width: 48,
                        height: 48,
                        position: 'relative',
                      }}>
                      <Image
                        fill
                        alt=""
                        src={discussion.repairer.thumbnail.contentUrl}
                        style={{borderRadius: '50%', objectFit: 'cover'}}
                      />
                    </CardMedia>
                  ) : (
                    <Avatar
                      sx={{
                        width: '48px',
                        height: '48px',
                        bgcolor: current ? 'primary.main' : 'grey.300',
                      }}
                    />
                  )}
                </>
              )}
              <Box>
                <Typography
                  variant="body2"
                  fontWeight={800}
                  gutterBottom
                  color={current ? 'primary.main' : 'text.secondary'}>
                  {!repairer
                    ? discussion.repairer.name
                    : `${discussion.customer.firstName} ${discussion.customer.lastName}`}
                </Typography>
                <Typography
                  color="grey.500"
                  variant="caption"
                  fontStyle="italic"
                  component="div"
                  lineHeight="1.2">
                  {discussion.lastMessage
                    ? `Dernier message : ${formatDate(
                        discussion.lastMessage,
                        true
                      )}`
                    : 'Pas encore de message'}
                </Typography>
              </Box>
            </Box>
          </Badge>
        </Box>
      </Link>
    </>
  );
};

export default DiscussionListItem;
