import {AbstractResource} from '@resources/AbstractResource';
import {WebsiteMedia} from '@interfaces/WebsiteMedia';

class WebsiteMediaResource extends AbstractResource<WebsiteMedia> {
  protected endpoint = '/website_media';
}

export const websiteMediaResource = new WebsiteMediaResource();
