import {AbstractResource} from '@resources/AbstractResource';
import {Discussion} from '@interfaces/Discussion';
import {RequestHeaders} from '@interfaces/Resource';
import {Repairer} from "@interfaces/Repairer";
import {User} from "@interfaces/User";

class DiscussionResource extends AbstractResource<Discussion> {
  protected endpoint = '/discussions';

  async countUnreadForRepairer(
    repairer: Repairer,
    headers?: RequestHeaders
  ): Promise<any> {
    const url = this.getUrl(`/repairers/${repairer.id}/messages_unread`);

    const doFetch = async () => {
      return await fetch(url, {
        headers: {
          ...this.getDefaultHeaders(),
          ...headers,
        },
        method: 'GET',
      });
    };

    return await this.getResult(doFetch);
  }

  async countUnreadForUser(
    headers?: RequestHeaders
  ): Promise<any> {
    const url = this.getUrl(`/customers/messages_unread`);

    const doFetch = async () => {
      return await fetch(url, {
        headers: {
          ...this.getDefaultHeaders(true),
          ...headers,
        },
        method: 'GET',
      });
    };

    return await this.getResult(doFetch);
  }

  async countUnreadDiscussion(
    discussionId: string,
    headers?: RequestHeaders
  ): Promise<any> {
    const url = this.getUrl(`/messages_unread/${discussionId}`);

    const doFetch = async () => {
      return await fetch(url, {
        headers: {
          ...this.getDefaultHeaders(),
          ...headers,
        },
        method: 'GET',
      });
    };

    return await this.getResult(doFetch);
  }

  async discussionRead(
    discussionId: string,
    headers?: RequestHeaders
  ): Promise<any> {
    const url = this.getUrl(`/discussions/${discussionId}/set_read`);

    const doFetch = async () => {
      return await fetch(url, {
        headers: {
          ...this.getDefaultHeaders(),
          ...headers,
        },
        method: 'GET',
      });
    };

    return await this.getResult(doFetch);
  }
}

export const discussionResource = new DiscussionResource();
