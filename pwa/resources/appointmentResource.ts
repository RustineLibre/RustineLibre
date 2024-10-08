import {getToken} from '@helpers/localHelper';
import {User} from '@interfaces/User';
import {AbstractResource} from '@resources/AbstractResource';
import {Appointment} from '@interfaces/Appointment';
import {
  Collection,
  RequestBody,
  RequestHeaders,
  RequestParams,
} from '@interfaces/Resource';
import {Repairer} from '@interfaces/Repairer';

class AppointmentResource extends AbstractResource<Appointment> {
  protected endpoint = '/appointments';

  async updateAppointmentStatus(
    id: string,
    body: RequestBody = {},
    headers?: RequestHeaders
  ): Promise<any> {
    const url = this.getUrl(`/appointment_transition/${id}`);

    const doFetch = async () => {
      return await fetch(url, {
        headers: {
          ...this.getDefaultHeaders(),
          ...headers,
        },
        method: 'PUT',
        body: JSON.stringify(body),
      });
    };

    return await this.getResult(doFetch);
  }

  async getAllByRepairer(
    repairer: Repairer,
    params?: RequestParams,
    headers?: RequestHeaders
  ): Promise<Collection<Appointment>> {
    const doFetch = async () => {
      return await fetch(
        this.getUrl(`/repairers/${repairer.id}${this.endpoint}`, params),
        {
          headers: {
            ...this.getDefaultHeaders(true),
            ...headers,
          },
        }
      );
    };

    return await this.getResult(doFetch, true);
  }

  async getAllByCustomer(
    customer: User,
    params?: RequestParams,
    headers?: RequestHeaders
  ): Promise<Collection<Appointment>> {
    const doFetch = async () => {
      return await fetch(
        this.getUrl(`/customers/${customer.id}${this.endpoint}`, params),
        {
          headers: {
            ...this.getDefaultHeaders(true),
            ...headers,
          },
        }
      );
    };

    return await this.getResult(doFetch, true);
  }

  async exportAppointmentCollectionCsv(): Promise<Response> {
    const currentToken = getToken();

    return await fetch('/export_appointments_csv', {
      headers: {
        'Content-Type': 'text/csv',
        Authorization: `Bearer ${currentToken}`,
      },
      method: 'GET',
    });
  }
}

export const appointmentResource = new AppointmentResource();
